<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitPhotoPrintRequest;
use App\Models\Order;
use App\Models\PrintQuality;
use App\Models\PrintService;
use App\Services\AdminDashboardStatistics;
use App\Services\OrderNumberGenerator;
use App\Services\PrintPricingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PhotoPrintController extends Controller
{
    public function show(): View
    {
        $service = PrintService::query()
            ->where('slug', 'photo-print')
            ->where('enabled', true)
            ->with(['shippingSetting', 'qualities' => fn ($query) => $query
                ->where('enabled', true)
                ->orderBy('display_order')
                ->orderBy('id')
                ->with(['pricingTiers' => fn ($tiers) => $tiers->where('enabled', true)->orderBy('min_quantity')])])
            ->firstOrFail();

        $options = $service->qualities
            ->map(function (PrintQuality $quality): ?array {
                $tier = $quality->pricingTiers->first(fn ($pricingTier) => $pricingTier->min_quantity <= 1
                    && ($pricingTier->max_quantity === null || $pricingTier->max_quantity >= 1));

                if (! $tier) {
                    return null;
                }

                return [
                    'id' => $quality->id,
                    'slug' => $quality->slug,
                    'name' => $quality->name,
                    'description' => $quality->description,
                    'unit_price' => (string) $tier->unit_price,
                    'display_order' => $quality->display_order,
                ];
            })
            ->filter()
            ->values();

        $submissionKey = (string) Str::uuid();
        session()->put('photo_print_submission_key', $submissionKey);

        return view('user-panel.photo-print', [
            'photoOptions' => $options,
            'shippingFee' => (string) ($service->shippingSetting?->flat_rate ?? '0.00'),
            'districts' => SubmitPhotoPrintRequest::DISTRICTS,
            'submissionKey' => $submissionKey,
        ]);
    }

    public function submit(SubmitPhotoPrintRequest $request, PrintPricingService $pricingService, OrderNumberGenerator $orderNumbers): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $submissionKey = $validated['submission_key'] ?? (string) Str::uuid();
        $existingOrder = Order::query()->where('user_id', $user->id)->where('submission_key', $submissionKey)->first();

        if ($existingOrder) {
            return redirect()->route('user.orders.show', $existingOrder->id);
        }

        $service = PrintService::query()->where('slug', 'photo-print')->where('enabled', true)->firstOrFail();
        $option = PrintQuality::query()
            ->where('service_id', $service->id)
            ->where('enabled', true)
            ->findOrFail($validated['photo_option_id']);
        $files = $validated['photo_files'] ?? [];
        $driveLinks = $validated['drive_links'] ?? [];
        $quantity = count($files) + count($driveLinks);
        $pricing = $pricingService->calculate('photo-print', $option->slug, $quantity);
        $directory = 'orders/'.Str::uuid();
        $storedPaths = [];
        $deliveryAddress = array_merge($validated['delivery_address'] ?? [], [
            'district' => $validated['delivery_district'],
        ]);

        try {
            $order = DB::transaction(function () use (
                $user,
                $files,
                $driveLinks,
                $directory,
                &$storedPaths,
                $submissionKey,
                $pricing,
                $option,
                $quantity,
                $deliveryAddress,
                $orderNumbers,
            ): Order {
                $order = $user->orders()->create([
                    'order_number' => $orderNumbers->temporary(),
                    'service_type' => 'photo-print',
                    'status' => Order::STATUS_PAYMENT_PENDING,
                    'payment_status' => 'pending',
                    'quantity' => $pricing['quantity'],
                    'subtotal' => $pricing['subtotal'],
                    'discount_amount' => $pricing['discount_amount'],
                    'cover_amount' => '0.00',
                    'shipping_amount' => $pricing['shipping_amount'],
                    'coupon_discount' => '0.00',
                    'total_amount' => $pricing['total_amount'],
                    'delivery_address' => $deliveryAddress,
                    'submission_key' => $submissionKey,
                ]);

                $order->order_number = $orderNumbers->forOrder($order);
                $order->save();
                $order->items()->create([
                    'quality_slug' => $option->slug,
                    'quality_name' => $option->name,
                    'option_slug' => $option->slug,
                    'quantity' => $quantity,
                    'base_unit_price' => $pricing['unit_price'],
                    'unit_price' => $pricing['unit_price'],
                    'discount_percent' => '0.00',
                    'discount_amount' => '0.00',
                    'subtotal' => $pricing['cards_subtotal'],
                    'cover_selected' => false,
                    'cover_unit_price' => '0.00',
                    'cover_total' => '0.00',
                ]);

                foreach ($files as $file) {
                    $path = $file->store($directory, 'local');
                    if (! $path) {
                        throw new \RuntimeException('An uploaded photo could not be stored.');
                    }
                    $storedPaths[] = $path;
                    $order->files()->create([
                        'type' => 'photo',
                        'original_name' => basename(str_replace('\\', '/', $file->getClientOriginalName())),
                        'storage_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'status' => 'received',
                    ]);
                }

                foreach ($driveLinks as $driveLink) {
                    $order->files()->create(['type' => 'drive', 'drive_url' => $driveLink, 'status' => 'received']);
                }

                $order->payments()->create([
                    'amount' => $pricing['total_amount'],
                    'status' => 'pending',
                    'metadata' => ['service_type' => 'photo-print', 'payment_gateway_configured' => false],
                ]);
                $order->statusHistory()->create([
                    'old_status' => null,
                    'new_status' => Order::STATUS_PAYMENT_PENDING,
                    'changed_by' => $user->id,
                    'note' => 'Photo Print order created. Payment is pending; no payment gateway is configured.',
                ]);

                return $order;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            if ($exception instanceof QueryException) {
                $existingOrder = Order::query()->where('user_id', $user->id)->where('submission_key', $submissionKey)->first();
                if ($existingOrder) {
                    return redirect()->route('user.orders.show', $existingOrder->id);
                }
            }

            throw $exception;
        }

        AdminDashboardStatistics::forget();

        return redirect()->route('user.orders.show', $order->id)->with('statusMessage', 'Photo order created. Payment is pending confirmation.');
    }
}