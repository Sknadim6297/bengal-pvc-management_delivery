<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderFile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserOrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $orders = Order::query()
            ->select([
                'id', 'user_id', 'order_number', 'service_type', 'status', 'payment_status',
                'quantity', 'total_amount', 'created_at',
            ])
            ->with(['items:id,order_id,quality_slug,quality_name,quantity,unit_price'])
            ->where('user_id', $request->user()->id)
            ->when(! empty($filters['from']), fn ($query) => $query->where('created_at', '>=', Carbon::parse($filters['from'])->startOfDay()))
            ->when(! empty($filters['to']), fn ($query) => $query->where('created_at', '<', Carbon::parse($filters['to'])->addDay()->startOfDay()))
            ->latest('created_at')
            ->latest('id');

        $orders = $orders->paginate(20)->withQueryString();

        return view('user-panel.order-history', ['orders' => $orders, 'filters' => $filters]);
    }

    public function show(Request $request, int $orderId): View
    {
        $order = Order::query()
            ->with(['items', 'files', 'statusHistory.changedBy', 'payments'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($orderId);

        return view('user-panel.orders.show', ['order' => $order]);
    }

    public function downloadFile(Request $request, int $orderId, int $fileId): StreamedResponse
    {
        $file = OrderFile::query()
            ->where('order_id', $orderId)
            ->whereIn('type', ['pdf', 'photo'])
            ->whereNotNull('storage_path')
            ->whereHas('order', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($fileId);

        abort_unless(Storage::disk('local')->exists($file->storage_path), 404);

        return response()->streamDownload(function () use ($file): void {
            $stream = Storage::disk('local')->readStream($file->storage_path);
            abort_unless(is_resource($stream), 404);
            fpassthru($stream);
            fclose($stream);
        }, basename((string) $file->original_name), ['Content-Type' => $file->mime_type ?: 'application/pdf']);
    }
}