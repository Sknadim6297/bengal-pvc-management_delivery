<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\AdminDashboardStatistics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_user_order_history_and_details_are_scoped_to_the_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownerOrder = $this->makeOrder($owner, quantity: 2);
        $otherOrder = $this->makeOrder($otherUser, quantity: 4);
        $path = 'orders/private-card.pdf';
        Storage::disk('local')->put($path, 'private pdf bytes');
        $file = $ownerOrder->files()->create([
            'type' => 'pdf',
            'original_name' => 'card.pdf',
            'storage_path' => $path,
            'file_size' => 18,
            'mime_type' => 'application/pdf',
            'status' => 'received',
        ]);

        $this->actingAs($owner)
            ->get(route('user.order-history'))
            ->assertOk()
            ->assertSee($ownerOrder->order_number)
            ->assertDontSee($otherOrder->order_number);

        $this->get(route('user.orders.show', $ownerOrder->id))
            ->assertOk()
            ->assertSee('card.pdf')
            ->assertSee('Status timeline');

        $this->get(route('user.orders.show', $otherOrder->id))->assertNotFound();
        $this->get(route('user.orders.files.download', [$otherOrder->id, $file->id]))->assertNotFound();
        $this->get(route('user.orders.files.download', [$ownerOrder->id, $file->id]))->assertOk();
    }

    public function test_user_dashboard_sums_card_quantity_by_actual_status(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->makeOrder($user, Order::STATUS_PENDING, 5);
        $this->makeOrder($user, Order::STATUS_CONFIRMED, 3);
        $this->makeOrder($user, Order::STATUS_PROCESSING, 2);
        $this->makeOrder($user, Order::STATUS_DELIVERED, 7);
        $this->makeOrder($user, Order::STATUS_FAILED, 11);
        $this->makeOrder($otherUser, Order::STATUS_PROCESSING, 100);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('statistics', fn (object $statistics): bool => (int) $statistics->processing_cards === 5
                && (int) $statistics->delivered_cards === 7);
    }

    public function test_admin_can_search_open_orders_and_apply_only_valid_transitions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create([
            'name' => 'Order Search Customer',
            'email' => 'order-search@example.com',
            'whatsapp_number' => '919876543210',
        ]);
        $order = $this->makeOrder($customer);
        $order->files()->create([
            'type' => 'drive',
            'drive_url' => 'https://drive.google.com/file/d/private-file/view',
            'status' => 'received',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pvc-orders.index', ['q' => 'Order Search']))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('order-search@example.com');

        $this->get(route('admin.pvc-orders.show', $order->id))
            ->assertOk()
            ->assertSee('Order Search Customer')
            ->assertSee('private-file')
            ->assertSee('Pricing snapshot')
            ->assertSee('Status timeline');

        $this->from(route('admin.pvc-orders.show', $order->id))
            ->patch(route('admin.pvc-orders.status', $order->id), [
                'status' => Order::STATUS_CONFIRMED,
                'note' => 'Payment confirmed by staff.',
            ])
            ->assertRedirect(route('admin.pvc-orders.show', $order->id));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_CONFIRMED]);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'old_status' => Order::STATUS_PENDING,
            'new_status' => Order::STATUS_CONFIRMED,
            'changed_by' => $admin->id,
            'note' => 'Payment confirmed by staff.',
        ]);

        $this->from(route('admin.pvc-orders.show', $order->id))
            ->patch(route('admin.pvc-orders.status', $order->id), ['status' => Order::STATUS_DELIVERED])
            ->assertRedirect(route('admin.pvc-orders.show', $order->id))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_CONFIRMED]);
        $this->assertDatabaseCount('order_status_history', 2);
    }

    public function test_full_pvc_submission_admin_processing_and_user_tracking_flow(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)
            ->post(route('user.pvc-card-print.submit'), [
                'drive_links' => ['https://drive.google.com/file/d/full-flow-file/view'],
                'print_quality' => 'normal',
                'submission_key' => (string) Str::uuid(),
            ]);

        $order = Order::query()->where('user_id', $user->id)->firstOrFail();
        $response->assertRedirect(route('user.orders.show', $order->id));
        $this->actingAs($user)
            ->get(route('user.orders.show', $order->id))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Status timeline');

        foreach ([Order::STATUS_CONFIRMED, Order::STATUS_PROCESSING] as $status) {
            $this->actingAs($admin)
                ->from(route('admin.pvc-orders.show', $order->id))
                ->patch(route('admin.pvc-orders.status', $order->id), ['status' => $status])
                ->assertRedirect(route('admin.pvc-orders.show', $order->id));
        }

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'old_status' => Order::STATUS_CONFIRMED,
            'new_status' => Order::STATUS_PROCESSING,
            'changed_by' => $admin->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('statistics', fn (object $statistics): bool => (int) $statistics->processing_cards === 1);

        $this->get(route('user.order-history'))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->get(route('user.orders.show', $order->id))
            ->assertOk()
            ->assertSee('Processing');
    }

    public function test_admin_order_list_is_paginated_and_admin_routes_are_forbidden_to_users(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $now = now();
        $rows = [];

        for ($index = 0; $index < 55; $index++) {
            $rows[] = [
                'user_id' => $user->id,
                'order_number' => 'PVC-'.now()->format('Ymd').'-'.str_pad((string) ($index + 1000), 6, '0', STR_PAD_LEFT),
                'service_type' => 'pvc-card',
                'status' => Order::STATUS_PENDING,
                'payment_status' => 'pending',
                'quantity' => 1,
                'subtotal' => '77.00',
                'discount_amount' => '0.00',
                'cover_amount' => '0.00',
                'shipping_amount' => '40.00',
                'coupon_discount' => '0.00',
                'total_amount' => '117.00',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('orders')->insert($rows);

        $this->actingAs($admin)
            ->get(route('admin.pvc-orders.index'))
            ->assertOk()
            ->assertViewHas('orders', fn ($orders): bool => count($orders->items()) === 50 && $orders->hasMorePages());

        $this->actingAs($user)->get(route('admin.pvc-orders.index'))->assertForbidden();
        $this->get(route('admin.pvc-orders.show', 1000))->assertForbidden();
    }

    public function test_admin_dashboard_aggregates_real_order_status_and_paid_revenue(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $customer = User::factory()->create();
        $this->makeOrder($customer, Order::STATUS_PENDING, 1);
        $processing = $this->makeOrder($customer, Order::STATUS_PROCESSING, 2);
        DB::table('orders')->where('id', $processing->id)->update([
            'payment_status' => 'paid',
            'total_amount' => '234.00',
        ]);
        $this->makeOrder($customer, Order::STATUS_DELIVERED, 3);
        $this->makeOrder($customer, Order::STATUS_FAILED, 4);
        Cache::forget(AdminDashboardStatistics::CACHE_KEY);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('statistics', fn (object $statistics): bool => $statistics->total_orders === 4
                && $statistics->pending_orders === 1
                && $statistics->processing_orders === 1
                && $statistics->delivered_orders === 1
                && $statistics->failed_orders === 1
                && (float) $statistics->total_revenue === 234.0
                && $statistics->today_orders === 4
                && (float) $statistics->today_revenue === 234.0);
    }

    public function test_order_list_query_indexes_exist(): void
    {
        $indexNames = collect(Schema::getIndexes('orders'))->pluck('name')->all();

        $this->assertContains('orders_service_created_id_index', $indexNames);
        $this->assertContains('orders_user_created_index', $indexNames);
    }

    private function makeOrder(User $user, string $status = Order::STATUS_PENDING, int $quantity = 1): Order
    {
        $order = $user->orders()->create([
            'order_number' => 'TMP-'.Str::random(20),
            'service_type' => 'pvc-card',
            'status' => $status,
            'payment_status' => 'pending',
            'quantity' => $quantity,
            'subtotal' => '80.00',
            'discount_amount' => '3.00',
            'cover_amount' => '0.00',
            'shipping_amount' => '40.00',
            'coupon_discount' => '0.00',
            'total_amount' => '117.00',
            'submission_key' => (string) Str::uuid(),
        ]);
        $order->order_number = 'PVC-'.$order->created_at->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
        $order->save();
        $order->items()->create([
            'quality_slug' => 'normal',
            'quality_name' => 'Normal Quality',
            'quantity' => $quantity,
            'base_unit_price' => '80.00',
            'unit_price' => '77.00',
            'discount_percent' => '0.00',
            'discount_amount' => '3.00',
            'subtotal' => number_format(77 * $quantity, 2, '.', ''),
            'cover_selected' => false,
            'cover_unit_price' => '0.00',
            'cover_total' => '0.00',
        ]);
        $order->statusHistory()->create([
            'old_status' => null,
            'new_status' => $status,
            'changed_by' => $user->id,
            'note' => 'Order placed.',
        ]);

        return $order;
    }
}