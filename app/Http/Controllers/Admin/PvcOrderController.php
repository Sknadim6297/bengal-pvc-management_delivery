<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminPvcOrderIndexRequest;
use App\Http\Requests\UpdatePvcOrderStatusRequest;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\PrintQuality;
use App\Models\PrintService;
use App\Services\AdminDashboardStatistics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PvcOrderController extends Controller
{
    public function index(AdminPvcOrderIndexRequest $request): View
    {
        return $this->renderOrderList($request, 'pvc-card', 'admin-panel.pvc-orders.index');
    }

    public function photoIndex(AdminPvcOrderIndexRequest $request): View
    {
        return $this->renderOrderList($request, 'photo-print', 'admin-panel.photo-orders.index')
            ->with('photoOptions', $this->activePhotoOptions());
    }

    private function renderOrderList(AdminPvcOrderIndexRequest $request, string $serviceType, string $viewName): View
    {
        $filters = $request->validated();
        $orders = Order::query()
            ->select([
                'id', 'user_id', 'order_number', 'service_type', 'status', 'payment_status',
                'quantity', 'total_amount', 'delivery_address', 'created_at',
            ])
            ->with([
                'user:id,name,email,whatsapp_number,district',
                'items:id,order_id,quality_slug,quality_name,quantity',
            ])
            ->where('service_type', $serviceType);

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $prefix = str_replace(['%', '_', '\\'], '', $search).'%';
            $orders->where(function ($query) use ($search, $prefix): void {
                $query->where('order_number', 'like', $prefix)
                    ->orWhereHas('user', function ($userQuery) use ($prefix): void {
                        $userQuery->where('name', 'like', $prefix)
                            ->orWhere('email', 'like', strtolower($prefix))
                            ->orWhere('whatsapp_number', 'like', $prefix);
                    });
            });
        }

        if (! empty($filters['status'])) {
            $orders->where('status', $filters['status']);
        }
        if (! empty($filters['payment_status'])) {
            $orders->where('payment_status', $filters['payment_status']);
        }
        if (! empty($filters['quality'])) {
            $orders->whereHas('items', fn ($query) => $query->where('quality_slug', $filters['quality']));
        }
        if (! empty($filters['district'])) {
            $orders->where('delivery_address->district', $filters['district']);
        }
        if (! empty($filters['from'])) {
            $orders->where('created_at', '>=', Carbon::parse($filters['from'])->startOfDay());
        }
        if (! empty($filters['to'])) {
            $orders->where('created_at', '<', Carbon::parse($filters['to'])->addDay()->startOfDay());
        }

        return view($viewName, [
            'orders' => $orders->latest('created_at')->latest('id')->paginate(50)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    private function activePhotoOptions(): array
    {
        $serviceId = PrintService::query()->where('slug', 'photo-print')->value('id');
        if (! $serviceId) {
            return [];
        }

        return PrintQuality::query()
            ->where('service_id', $serviceId)
            ->where('enabled', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->limit(50)
            ->get(['slug', 'name'])
            ->toArray();
    }

    public function show(int $orderId): View
    {
        return $this->renderOrderDetails($orderId, 'pvc-card', 'admin-panel.pvc-orders.show');
    }

    public function photoShow(int $orderId): View
    {
        return $this->renderOrderDetails($orderId, 'photo-print', 'admin-panel.photo-orders.show');
    }

    private function renderOrderDetails(int $orderId, string $serviceType, string $viewName): View
    {
        $order = Order::query()
            ->with([
                'user:id,name,email,whatsapp_number,district',
                'items',
                'files',
                'payments',
                'statusHistory.changedBy',
            ])
            ->where('service_type', $serviceType)
            ->findOrFail($orderId);

        $allowedStatuses = Order::allowedTransitions()[$order->status] ?? [];
        if ($serviceType === 'photo-print' && $order->payment_status !== 'paid') {
            $allowedStatuses = array_values(array_diff($allowedStatuses, [Order::STATUS_CONFIRMED]));
        }

        return view($viewName, [
            'order' => $order,
            'allowedStatuses' => $allowedStatuses,
        ]);
    }

    public function downloadFile(int $orderId, int $fileId): StreamedResponse
    {
        return $this->downloadOrderFile($orderId, $fileId, 'pvc-card', 'pdf');
    }

    public function downloadPhotoFile(int $orderId, int $fileId): StreamedResponse
    {
        return $this->downloadOrderFile($orderId, $fileId, 'photo-print', 'photo');
    }

    private function downloadOrderFile(int $orderId, int $fileId, string $serviceType, string $fileType): StreamedResponse
    {
        $file = OrderFile::query()
            ->where('order_id', $orderId)
            ->where('type', $fileType)
            ->whereNotNull('storage_path')
            ->whereHas('order', fn ($query) => $query->where('service_type', $serviceType))
            ->findOrFail($fileId);

        abort_unless(Storage::disk('local')->exists($file->storage_path), 404);

        return response()->streamDownload(function () use ($file): void {
            $stream = Storage::disk('local')->readStream($file->storage_path);
            abort_unless(is_resource($stream), 404);
            fpassthru($stream);
            fclose($stream);
        }, basename((string) $file->original_name), ['Content-Type' => $file->mime_type ?: 'application/pdf']);
    }

    public function updateStatus(UpdatePvcOrderStatusRequest $request, int $orderId): RedirectResponse
    {
        return $this->updateOrderStatus($request, $orderId, 'pvc-card');
    }

    public function updatePhotoStatus(UpdatePvcOrderStatusRequest $request, int $orderId): RedirectResponse
    {
        return $this->updateOrderStatus($request, $orderId, 'photo-print');
    }

    private function updateOrderStatus(UpdatePvcOrderStatusRequest $request, int $orderId, string $serviceType): RedirectResponse
    {
        $newStatus = $request->validated('status');

        DB::transaction(function () use ($request, $orderId, $newStatus, $serviceType): void {
            $order = Order::query()
                ->where('service_type', $serviceType)
                ->lockForUpdate()
                ->findOrFail($orderId);

            if (! $order->canTransitionTo($newStatus)
                || ($serviceType === 'photo-print' && $newStatus === Order::STATUS_CONFIRMED && $order->payment_status !== 'paid')) {
                throw ValidationException::withMessages(['status' => 'That order status transition is not allowed.']);
            }

            $oldStatus = $order->status;
            $order->status = $newStatus;
            $order->save();
            $order->statusHistory()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $request->user()->id,
                'note' => $request->validated('note'),
            ]);
        });

        AdminDashboardStatistics::forget();

        return back()->with('statusMessage', 'Order status updated.');
    }
}