<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\AdminDashboardStatistics;
use App\Services\LiveOrderFeedService;
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
        $ownerOrder->status = Order::STATUS_PROCESSING;
        $ownerOrder->payment_status = 'paid';
        $ownerOrder->save();
        $ownerOrder->statusHistory()->create([
            'old_status' => Order::STATUS_PENDING,
            'new_status' => Order::STATUS_PROCESSING,
            'changed_by' => $owner->id,
            'note' => 'Production started.',
        ]);
        $ownerOrder->payments()->create([
            'gateway' => 'manual',
            'transaction_id' => 'PAY-REFERENCE-123',
            'amount' => '117.00',
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $longFilename = str_repeat('Technical_Project_Documentation_', 5).'.pdf';
        $ownerOrder->files()->create([
            'type' => 'pdf',
            'original_name' => $longFilename,
            'file_size' => 1024,
            'mime_type' => 'application/pdf',
            'status' => 'missing',
        ]);

        $this->actingAs($owner)
            ->get(route('user.order-history'))
            ->assertOk()
            ->assertSee($ownerOrder->order_number)
            ->assertSee('Payment')
            ->assertSee('Processing')
            ->assertSee('<table class="table order-history-table align-middle mb-0">', false)
            ->assertSee('order-history-mobile-card', false)
            ->assertSee(route('user.track-help', ['order_id' => $ownerOrder->order_number]), false)
            ->assertDontSee($otherOrder->order_number);

        $this->get(route('user.orders.show', $ownerOrder->id))
            ->assertOk()
            ->assertSee('Order #'.$ownerOrder->order_number)
            ->assertSee('Processing')
            ->assertSee('Paid')
            ->assertSee('Order Summary')
            ->assertSee('₹117.00')
            ->assertSee('card.pdf')
            ->assertSee($longFilename)
            ->assertSee('Unavailable')
            ->assertSee('Production started.')
            ->assertSee('Status History')
            ->assertSee(route('user.track-help', ['order_id' => $ownerOrder->order_number]), false)
            ->assertSee('PAY-REFERENCE-123');

        $this->get(route('user.orders.show', $otherOrder->id))->assertNotFound();
        $this->get(route('user.orders.files.download', [$otherOrder->id, $file->id]))->assertNotFound();
        $this->get(route('user.orders.files.download', [$ownerOrder->id, $file->id]))->assertOk();
    }

    public function test_order_details_shows_truthful_empty_states_without_fabricating_history(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user);
        $order->statusHistory()->delete();

        $this->actingAs($user)
            ->get(route('user.orders.show', $order->id))
            ->assertOk()
            ->assertSee('No documents attached to this order.')
            ->assertSee('Payment pending')
            ->assertSee('No payment has been recorded for this order yet.')
            ->assertSee('No status history has been recorded for this order.')
            ->assertDontSee('<article class="order-activity-item', false);
    }

    public function test_order_history_filters_dates_by_owner_and_preserves_pagination_parameters(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $today = now()->startOfDay();
        $inRangeOrder = null;
        $outOfRangeOrder = null;

        for ($index = 0; $index < 21; $index++) {
            $order = $this->makeOrder($owner);
            DB::table('orders')->where('id', $order->id)->update([
                'created_at' => $index === 0 ? $today->copy()->subDay() : $today,
            ]);
            if ($index === 1) {
                $inRangeOrder = $order;
            }
            if ($index === 0) {
                $outOfRangeOrder = $order;
            }
        }

        $otherOrder = $this->makeOrder($otherUser);
        DB::table('orders')->where('id', $otherOrder->id)->update(['created_at' => $today]);
        $filterDate = $today->toDateString();

        $this->actingAs($owner)
            ->get(route('user.order-history', ['from' => $filterDate, 'to' => $filterDate]))
            ->assertOk()
            ->assertSee($inRangeOrder->order_number)
            ->assertDontSee($outOfRangeOrder->order_number)
            ->assertDontSee($otherOrder->order_number)
            ->assertViewHas('orders', function ($orders) use ($filterDate): bool {
                parse_str(parse_url($orders->url(2), PHP_URL_QUERY) ?? '', $query);

                return $orders->total() === 20
                    && $orders->perPage() === 20
                    && $query['from'] === $filterDate
                    && $query['to'] === $filterDate;
            });
    }

    public function test_order_history_displays_payment_and_fulfillment_status_separately(): void
    {
        $user = User::factory()->create();
        $paidOrder = $this->makeOrder($user, Order::STATUS_PAYMENT_PENDING);
        $paidOrder->payment_status = 'paid';
        $paidOrder->save();

        $this->actingAs($user)
            ->get(route('user.order-history'))
            ->assertOk()
            ->assertSee('Paid')
            ->assertSee('Awaiting Payment')
            ->assertSee('Payment')
            ->assertSee('Status');
    }

    public function test_order_history_shows_compact_empty_states(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.order-history'))
            ->assertOk()
            ->assertSee('No orders found')
            ->assertSee('Your PVC card print orders will appear here.')
            ->assertDontSee('<table class="table order-history-table', false);

        $future = now()->addDay()->toDateString();
        $this->get(route('user.order-history', ['from' => $future, 'to' => $future]))
            ->assertOk()
            ->assertSee('No orders found for the selected date range.')
            ->assertSee('Clear filters');
    }

    public function test_invalid_order_history_filter_shows_one_toast(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('user.order-history'))
            ->get(route('user.order-history', [
                'from' => '2026-10-02',
                'to' => '2026-10-01',
            ]))
            ->assertRedirect(route('user.order-history'))
            ->assertSessionHasErrors('to');

        $response = $this->get(route('user.order-history'));
        $response->assertOk()->assertSee('toast-container');
        $this->assertSame(1, substr_count($response->getContent(), 'role="alert"'));

        $this->from(route('user.order-history'))
            ->get(route('user.order-history', ['from' => ['unexpected']]))
            ->assertRedirect(route('user.order-history'))
            ->assertSessionHasErrors('from');

        $response = $this->get(route('user.order-history'));
        $response->assertOk()->assertSee('toast-container');
        $this->assertSame(1, substr_count($response->getContent(), 'role="alert"'));
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
               ->assertSee('Status History');

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

    public function test_live_order_feed_is_global_and_public_safe(): void
    {
        $viewer = User::factory()->create();
        $customerA = User::factory()->create([
            'name' => 'Alice Customer',
            'email' => 'alice@example.com',
            'district' => 'South 24 Parganas',
        ]);
        $customerB = User::factory()->create([
            'name' => 'Bob Customer',
            'email' => 'bob@example.com',
            'district' => 'Purba Medinipur',
        ]);

        $ownOrder = $this->makeOrder($viewer, Order::STATUS_PENDING, 3);
        $publicOrderA = $this->makeOrder($customerA, Order::STATUS_PENDING, 13);
        $publicOrderB = $this->makeOrder($customerB, Order::STATUS_PROCESSING, 11);
        $cancelledOrder = $this->makeOrder($customerB, Order::STATUS_CANCELLED, 7);
        $photoOrder = $this->makeOrder($customerA, Order::STATUS_PENDING, 5);
        $photoOrder->update(['service_type' => 'photo-print']);

        $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('liveOrderFeed', fn ($feed): bool => $feed->contains(fn ($order): bool => $order->id === $ownOrder->id)
                && $feed->contains(fn ($order): bool => $order->id === $publicOrderA->id)
                && $feed->contains(fn ($order): bool => $order->id === $publicOrderB->id)
                && ! $feed->contains(fn ($order): bool => $order->id === $cancelledOrder->id)
                && ! $feed->contains(fn ($order): bool => $order->id === $photoOrder->id));

        $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('alice@example.com')
            ->assertDontSee('bob@example.com')
            ->assertSee('Alice Customer ordered 13 cards')
            ->assertSee('Bob Customer ordered 11 cards')
            ->assertSee('South 24 Parganas')
            ->assertSee('Purba Medinipur');
    }

    public function test_live_order_feed_uses_the_current_status_for_public_orders(): void
    {
        $viewer = User::factory()->create();
        $customer = User::factory()->create(['name' => 'Status Customer']);
        $order = $this->makeOrder($customer, Order::STATUS_PENDING, 1);

        $this->assertTrue(app(LiveOrderFeedService::class)->latest()->contains(fn ($item): bool => $item->id === $order->id && $item->status === Order::STATUS_PENDING));

        $order->update(['status' => Order::STATUS_PROCESSING]);

        $this->assertTrue(app(LiveOrderFeedService::class)->latest()->contains(fn ($item): bool => $item->id === $order->id && $item->status === Order::STATUS_PROCESSING));

        $order->update(['status' => Order::STATUS_DELIVERED]);

        $this->assertTrue(app(LiveOrderFeedService::class)->latest()->contains(fn ($item): bool => $item->id === $order->id && $item->status === Order::STATUS_DELIVERED));

        $this->actingAs($viewer)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Status Customer ordered 1 card');
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