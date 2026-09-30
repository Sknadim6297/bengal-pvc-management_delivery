<?php

namespace Tests\Feature;

use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class SupportTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_page_requires_an_authenticated_user(): void
    {
        $this->get(route('user.track-help'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('user.track-help'))
            ->assertOk()
            ->assertSee('Track Your Order')
            ->assertSee('Need Help?')
            ->assertSee('name="order_id"', false);
    }

    public function test_empty_and_malformed_order_ids_return_one_toast_without_server_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.track-help').'?order_id=')
            ->assertOk()
            ->assertSee('Please enter your Order ID.')
            ->assertDontSee('Order not found.');

        $this->get(route('user.track-help', ['order_id' => ['malformed']]))
            ->assertOk()
            ->assertSee('Please enter a valid Order ID.');

        $this->get(route('user.track-help', ['order_id' => str_repeat('A', 33)]))
            ->assertOk()
            ->assertSee('Please enter a valid Order ID.')
            ->assertDontSee(str_repeat('A', 33));
    }

    public function test_new_order_progress_marks_order_placed_current_and_later_real_stages_future(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, Order::STATUS_PENDING);

        $this->actingAs($user)
            ->get(route('user.track-help', ['order_id' => $order->order_number]))
            ->assertOk()
            ->assertSee('Current Status')
            ->assertSee('Order Progress')
            ->assertSee('Order Placed')
            ->assertSee('Confirmed')
            ->assertSee('Delivered')
            ->assertSee('tracking-timeline-step is-current', false)
            ->assertSee('tracking-timeline-step is-future', false);
    }

    public function test_payment_pending_initial_state_starts_without_an_order_placed_stage(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, Order::STATUS_PAYMENT_PENDING);

        $this->actingAs($user)
            ->get(route('user.track-help', ['order_id' => $order->order_number]))
            ->assertOk()
            ->assertSee('Awaiting Payment')
            ->assertSee('Confirmed')
            ->assertDontSee('Order placed');
    }

    public function test_user_can_see_only_their_order_status_history(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, Order::STATUS_PROCESSING);

        $this->actingAs($user)
            ->get(route('user.track-help', ['order_id' => $order->order_number]))
            ->assertOk()
            ->assertSee('Order Progress')
            ->assertSee($order->order_number)
            ->assertSee('Processing')
            ->assertSee('Current Status')
            ->assertDontSee('Latest Order Status')
            ->assertDontSee('Current status')
            ->assertDontSee('Private staff-only note')
            ->assertDontSee($user->email);
    }

    public function test_unknown_and_other_users_orders_have_the_same_private_not_found_response(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'private-customer@example.com']);
        $otherOrder = $this->makeOrder($otherUser, Order::STATUS_DELIVERED);

        $this->actingAs($user)
            ->get(route('user.track-help', ['order_id' => $otherOrder->order_number]))
            ->assertOk()
            ->assertSee('Order not found.')
            ->assertDontSee($otherOrder->order_number)
            ->assertDontSee('Delivered')
            ->assertDontSee('private-customer@example.com')
            ->assertDontSee('belongs to another user');

        $this->get(route('user.track-help', ['order_id' => 'PVC-20260930-999999']))
            ->assertOk()
            ->assertSee('Order not found.');
    }

    public function test_terminal_statuses_are_shown_without_a_delivery_progression(): void
    {
        $user = User::factory()->create();

        foreach ([Order::STATUS_CANCELLED, Order::STATUS_FAILED] as $status) {
            $order = $this->makeOrder($user, $status);

            $this->actingAs($user)
                ->get(route('user.track-help', ['order_id' => $order->order_number]))
                ->assertOk()
                ->assertSee(ucfirst($status));
        }
    }

    public function test_admin_status_changes_are_visible_on_the_next_tracking_lookup(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $order = $this->makeOrder($user, Order::STATUS_PENDING);

        $this->actingAs($admin)
            ->patch(route('admin.pvc-orders.status', $order->id), ['status' => Order::STATUS_CONFIRMED])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('user.track-help', ['order_id' => $order->order_number]))
            ->assertOk()
            ->assertSee('Order Placed')
            ->assertSee('Confirmed')
            ->assertSee('Current Status')
            ->assertSee('Order Progress')
            ->assertSee('Processing')
            ->assertSee('Printing')
            ->assertSee('Packed')
            ->assertSee('Shipped')
            ->assertSee('Out for Delivery')
            ->assertSee('Delivered');
    }

    public function test_support_links_use_the_configured_contact_and_encode_order_context(): void
    {
        $user = User::factory()->create();
        $order = $this->makeOrder($user, Order::STATUS_PENDING);
        $settings = GeneralSetting::query()->findOrFail(1);
        $settings->whatsapp_contact_url = 'https://wa.me/919876543210';
        $settings->helpline_number = '+91 9876543210';
        $settings->save();

        $this->actingAs($user)
            ->get(route('user.track-help', ['order_id' => $order->order_number]))
            ->assertOk()
            ->assertSee('https://wa.me/919876543210?text=Hello%2C%20I%20need%20help%20with%20Order%20'.$order->order_number.'.', false)
            ->assertSee('href="tel:+919876543210"', false)
            ->assertSee('Need Help?');
    }

    public function test_missing_or_unsupported_whatsapp_contact_does_not_render_an_unsafe_action(): void
    {
        Config::set('services.whatsapp_contact_url', null);
        $settings = GeneralSetting::query()->findOrFail(1);
        $settings->whatsapp_contact_url = 'https://example.com/contact';
        $settings->save();

        $this->actingAs(User::factory()->create())
            ->get(route('user.track-help'))
            ->assertOk()
            ->assertDontSee('example.com')
            ->assertDontSee('Chat on WhatsApp')
            ->assertSee('Call Support')
            ->assertSee('href="tel:+918900162634"', false);

        $settings->whatsapp_contact_url = '';
        $settings->save();
        Config::set('services.whatsapp_contact_url', null);

        $this->get(route('user.track-help'))
            ->assertOk()
            ->assertDontSee('Chat on WhatsApp')
            ->assertSee('Call Support');
    }

    private function makeOrder(User $user, string $status): Order
    {
        $order = $user->orders()->create([
            'order_number' => 'TMP-'.Str::random(20),
            'service_type' => 'pvc-card',
            'status' => $status,
            'payment_status' => 'pending',
            'quantity' => 1,
            'subtotal' => '77.00',
            'discount_amount' => '0.00',
            'cover_amount' => '0.00',
            'shipping_amount' => '40.00',
            'coupon_discount' => '0.00',
            'total_amount' => '117.00',
            'submission_key' => (string) Str::uuid(),
        ]);
        $order->order_number = 'PVC-'.$order->created_at->format('Ymd').'-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
        $order->save();
        $order->statusHistory()->create([
            'old_status' => null,
            'new_status' => $status,
            'changed_by' => $user->id,
            'note' => 'Private staff-only note',
        ]);

        return $order;
    }
}