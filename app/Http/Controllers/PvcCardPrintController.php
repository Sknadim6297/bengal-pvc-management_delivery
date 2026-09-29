<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitPvcCardPrintRequest;
use App\Models\Order;
use App\Services\AdminDashboardStatistics;
use App\Services\OrderNumberGenerator;
use App\Services\PricingSettings;
use App\Services\PrintPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PvcCardPrintController extends Controller
{
    public function show(): View
    {
        $submissionKey = (string) Str::uuid();
        session()->put('pvc_print_submission_key', $submissionKey);

        return view('user-panel.pvc-card-print', [
            'pricing' => PricingSettings::remember(),
            'submissionKey' => $submissionKey,
        ]);
    }

    public function submit(SubmitPvcCardPrintRequest $request, OrderNumberGenerator $orderNumbers): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $submissionKey = $validated['submission_key'] ?? (string) Str::uuid();
        $existingOrder = Order::query()
            ->where('user_id', $user->id)
            ->where('submission_key', $submissionKey)
            ->first();

        if ($existingOrder) {
            return redirect()->route('user.orders.show', $existingOrder->id);
        }

        $files = $validated['pdf_files'] ?? [];
        $driveLinks = $validated['drive_links'] ?? [];
        $quantity = count($files) + count($driveLinks);
        $coverSelected = (bool) ($validated['include_card_cover'] ?? false);
        $pricing = app(PrintPricingService::class)->calculate(
            'pvc-card',
            $validated['print_quality'],
            $quantity,
            $coverSelected,
        );
        $directory = 'orders/'.Str::uuid();
        $pdfPaths = [];

        try {
            $order = DB::transaction(function () use ($files, $driveLinks, $directory, &$pdfPaths, $submissionKey, $user, $pricing, $coverSelected, $orderNumbers): Order {
                $order = $user->orders()->create([
                    'order_number' => $orderNumbers->temporary(),
                    'service_type' => 'pvc-card',
                    'status' => Order::STATUS_PENDING,
                    'payment_status' => 'pending',
                    'quantity' => $pricing['quantity'],
                    'subtotal' => $pricing['subtotal'],
                    'discount_amount' => $pricing['discount_amount'],
                    'cover_amount' => $pricing['cover_total'],
                    'shipping_amount' => $pricing['shipping_amount'],
                    'coupon_discount' => $pricing['coupon_discount'],
                    'total_amount' => $pricing['total_amount'],
                    'submission_key' => $submissionKey,
                ]);

                $order->order_number = $orderNumbers->forOrder($order);
                $order->save();

                $order->items()->create([
                    'quality_slug' => $pricing['quality_slug'],
                    'quality_name' => $pricing['quality_name'],
                    'quantity' => $pricing['quantity'],
                    'base_unit_price' => $pricing['base_unit_price'],
                    'unit_price' => $pricing['unit_price'],
                    'discount_percent' => $pricing['discount_percent'],
                    'discount_amount' => $pricing['discount_amount'],
                    'subtotal' => $pricing['cards_subtotal'],
                    'cover_selected' => $coverSelected,
                    'cover_unit_price' => $pricing['cover_unit_price'],
                    'cover_total' => $pricing['cover_total'],
                ]);

                foreach ($files as $file) {
                    $path = $file->store($directory, 'local');
                    if (! $path) {
                        throw new \RuntimeException('The uploaded PDF could not be stored.');
                    }
                    $pdfPaths[] = $path;
                    $originalName = basename(str_replace('\\', '/', $file->getClientOriginalName()));

                    $order->files()->create([
                        'type' => 'pdf',
                        'original_name' => $originalName,
                        'storage_path' => $path,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType() ?: 'application/pdf',
                        'status' => 'received',
                    ]);
                }

                foreach ($driveLinks as $driveLink) {
                    $order->files()->create([
                        'type' => 'drive',
                        'drive_url' => $driveLink,
                        'status' => 'received',
                    ]);
                }

                $order->statusHistory()->create([
                    'old_status' => null,
                    'new_status' => Order::STATUS_PENDING,
                    'changed_by' => $user->id,
                    'note' => 'Order placed by customer.',
                ]);

                return $order;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($pdfPaths);

            if ($exception instanceof QueryException) {
                $existingOrder = Order::query()
                    ->where('user_id', $user->id)
                    ->where('submission_key', $submissionKey)
                    ->first();

                if ($existingOrder) {
                    return redirect()->route('user.orders.show', $existingOrder->id);
                }
            }

            throw $exception;
        }

        AdminDashboardStatistics::forget();

        return redirect()->route('user.orders.show', $order->id)->with('statusMessage', 'Order created successfully.');
    }
}