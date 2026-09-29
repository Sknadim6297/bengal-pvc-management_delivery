<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PrintQuality;
use App\Models\PrintService;
use App\Models\User;
use App\Services\PricingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PhotoPrintOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_photo_page_shows_active_database_options_and_dynamic_rates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.photo-print'))
            ->assertOk()
            ->assertSee('4x6 Inch (Standard)')
            ->assertSee('₹120.00 / photo')
            ->assertSee('A4 Size (8x12)')
            ->assertSee('₹240.00 / photo')
            ->assertSee('Shipping: ₹50.00')
            ->assertSee('name="photo_option_id"', false)
            ->assertSee('name="photo_files[]"', false)
            ->assertSee('name="delivery_district"', false)
            ->assertSee('PAY &amp; ORDER PHOTOS', false);
    }

    public function test_mixed_jpg_png_and_drive_order_uses_server_quantity_price_and_pending_payment(): void
    {
        $user = User::factory()->create();
        $optionId = $this->photoOptionId('photo-4x6');

        $this->actingAs($user)
            ->post(route('user.photo-print.submit'), [
                'photo_option_id' => $optionId,
                'photo_files' => [
                    UploadedFile::fake()->image('one.jpg', 20, 20),
                    UploadedFile::fake()->image('two.png', 20, 20),
                ],
                'drive_links' => ['https://drive.google.com/file/d/photo-three/view'],
                'delivery_district' => 'Howrah',
                'delivery_address' => ['village_area' => 'Test Area', 'pincode' => '711101'],
                'quantity' => 999,
                'unit_price' => 1,
                'total_amount' => 1,
                'user_id' => 999999,
                'status' => 'delivered',
                'payment_status' => 'paid',
            ])
            ->assertRedirect(route('user.orders.show', 1));

        $order = Order::query()->with(['items', 'files', 'payments', 'statusHistory'])->firstOrFail();
        $this->assertMatchesRegularExpression('/^PHOTO-\d{8}-\d{6}$/', $order->order_number);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('photo-print', $order->service_type);
        $this->assertSame(3, $order->quantity);
        $this->assertSame('payment_pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('Howrah', $order->delivery_address['district']);
        $this->assertSame('Test Area', $order->delivery_address['village_area']);
        $this->assertSame('120.00', $order->items->first()->unit_price);
        $this->assertSame('360.00', $order->subtotal);
        $this->assertSame('50.00', $order->shipping_amount);
        $this->assertSame('410.00', $order->total_amount);
        $this->assertSame(2, $order->files->where('type', 'photo')->count());
        $this->assertSame(1, $order->files->where('type', 'drive')->count());
        $this->assertTrue(Storage::disk('local')->exists($order->files->firstWhere('type', 'photo')->storage_path));
        $this->assertSame('pending', $order->payments->first()->status);
        $this->assertSame('payment_pending', $order->statusHistory->first()->new_status);

        $this->actingAs($user)
            ->get(route('user.orders.show', $order->id))
            ->assertOk()
            ->assertSee('Photo size')
            ->assertSee('Rate per photo')
            ->assertSee('Howrah');
    }

    public function test_a4_three_photos_cost_720_plus_50_shipping(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('user.photo-print.submit'), [
                'photo_option_id' => $this->photoOptionId('photo-a4'),
                'drive_links' => [
                    'https://drive.google.com/file/d/a4-one/view',
                    'https://drive.google.com/file/d/a4-two/view',
                    'https://drive.google.com/file/d/a4-three/view',
                ],
                'delivery_district' => 'Kolkata',
            ])
            ->assertRedirect(route('user.orders.show', 1));

        $order = Order::query()->with('items')->firstOrFail();
        $this->assertSame(3, $order->quantity);
        $this->assertSame('240.00', $order->items->first()->unit_price);
        $this->assertSame('720.00', $order->subtotal);
        $this->assertSame('50.00', $order->shipping_amount);
        $this->assertSame('770.00', $order->total_amount);
    }

    public function test_five_4x6_photos_cost_600_plus_50_shipping(): void
    {
        $user = User::factory()->create();
        $links = [];
        for ($index = 1; $index <= 5; $index++) {
            $links[] = "https://drive.google.com/file/d/photo-{$index}/view";
        }

        $this->actingAs($user)
            ->post(route('user.photo-print.submit'), [
                'photo_option_id' => $this->photoOptionId('photo-4x6'),
                'drive_links' => $links,
                'delivery_district' => 'Howrah',
            ])
            ->assertRedirect(route('user.orders.show', 1));

        $order = Order::query()->firstOrFail();
        $this->assertSame(5, $order->quantity);
        $this->assertSame('600.00', $order->subtotal);
        $this->assertSame('50.00', $order->shipping_amount);
        $this->assertSame('650.00', $order->total_amount);
    }

    public function test_photo_upload_rejects_empty_invalid_district_invalid_file_and_bad_drive_urls(): void
    {
        $user = User::factory()->create();
        $optionId = $this->photoOptionId('photo-4x6');
        $invalidCases = [
            ['delivery_district' => 'Kolkata'],
            ['photo_files' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')], 'delivery_district' => 'Kolkata'],
            ['drive_links' => ['http://drive.google.com/file/d/file/view'], 'delivery_district' => 'Kolkata'],
            ['drive_links' => ['https://example.com/photo.jpg'], 'delivery_district' => 'Kolkata'],
            ['drive_links' => ['https://drive.google.com/not-a-share-link'], 'delivery_district' => 'Kolkata'],
            ['drive_links' => ['https://drive.google.com/file/d/photo/view'], 'delivery_district' => 'Toronto'],
        ];

        foreach ($invalidCases as $case) {
            $this->actingAs($user)
                ->from(route('user.photo-print'))
                ->post(route('user.photo-print.submit'), $case + ['photo_option_id' => $optionId])
                ->assertRedirect(route('user.photo-print'))
                ->assertSessionHasErrors();
        }

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_photo_uploads_over_80mb_and_duplicate_file_content_are_rejected(): void
    {
        $user = User::factory()->create();
        $optionId = $this->photoOptionId('photo-4x6');
        $district = ['photo_option_id' => $optionId, 'delivery_district' => 'Kolkata'];

        $this->actingAs($user)
            ->from(route('user.photo-print'))
            ->post(route('user.photo-print.submit'), $district + [
                'photo_files' => [
                    UploadedFile::fake()->image('large-one.jpg', 20, 20)->size(40960),
                    UploadedFile::fake()->image('large-two.jpg', 20, 20)->size(40960),
                    UploadedFile::fake()->image('large-three.jpg', 20, 20)->size(1),
                ],
            ])
            ->assertRedirect(route('user.photo-print'))
            ->assertSessionHasErrors('photo_files');

        $duplicate = UploadedFile::fake()->image('duplicate.png', 8, 8);
        $this->actingAs($user)
            ->from(route('user.photo-print'))
            ->post(route('user.photo-print.submit'), $district + ['photo_files' => [$duplicate, $duplicate]])
            ->assertRedirect(route('user.photo-print'))
            ->assertSessionHasErrors('photo_files.1');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_admin_can_manage_photo_catalog_and_existing_orders_keep_price_snapshots(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create();
        $fourBySixId = $this->photoOptionId('photo-4x6');
        $a4Id = $this->photoOptionId('photo-a4');

        $this->actingAs($user)
            ->post(route('user.photo-print.submit'), [
                'photo_option_id' => $fourBySixId,
                'drive_links' => ['https://drive.google.com/file/d/before-price-change/view'],
                'delivery_district' => 'Hooghly',
            ])
            ->assertRedirect(route('user.orders.show', 1));
        $oldOrder = Order::query()->with('items')->firstOrFail();

        $photoOptions = $this->adminPhotoOptions();
        foreach ($photoOptions as &$option) {
            if ($option['id'] === $fourBySixId) {
                $option['name'] = '4x6 Inch (Updated)';
                $option['unit_price'] = '130.00';
            }
            if ($option['id'] === $a4Id) {
                $option['enabled'] = false;
            }
        }
        unset($option);
        $photoOptions['new_option'] = [
            'name' => '5x7 Inch',
            'slug' => 'photo-5x7',
            'description' => 'Test custom photo option.',
            'unit_price' => '150.00',
            'enabled' => true,
            'display_order' => 3,
        ];

        $this->actingAs($admin)
            ->from(route('admin.pricing.edit'))
            ->put(route('admin.pricing.update'), array_merge((array) PricingSettings::remember(), [
                'photo_shipping_fee' => '55.00',
                'photo_options' => $photoOptions,
            ]))
            ->assertRedirect(route('admin.pricing.edit'))
            ->assertSessionHasNoErrors();

        $this->assertSame('120.00', $oldOrder->items->first()->unit_price);
        $this->assertSame('170.00', $oldOrder->total_amount);
        $this->assertDatabaseHas('pricing_tiers', [
            'quality_id' => $fourBySixId,
            'min_quantity' => 1,
            'unit_price' => '130.00',
        ]);
        $this->assertDatabaseHas('shipping_settings', ['flat_rate' => '55.00']);
        $this->assertDatabaseHas('print_qualities', ['slug' => 'photo-5x7', 'name' => '5x7 Inch', 'enabled' => 1]);

        $this->actingAs($user)
            ->get(route('user.photo-print'))
            ->assertOk()
            ->assertSee('4x6 Inch (Updated)')
            ->assertSee('₹130.00 / photo')
            ->assertSee('5x7 Inch')
            ->assertDontSee('A4 Size (8x12)');

        $this->from(route('user.photo-print'))->post(route('user.photo-print.submit'), [
            'photo_option_id' => $fourBySixId,
            'drive_links' => ['https://drive.google.com/file/d/after-price-change/view'],
            'delivery_district' => 'Hooghly',
        ])->assertRedirect(route('user.orders.show', 2));

        $newOrder = Order::query()->where('id', '!=', $oldOrder->id)->with('items')->firstOrFail();
        $this->assertSame('130.00', $newOrder->items->first()->unit_price);
        $this->assertSame('185.00', $newOrder->total_amount);

        $this->post(route('user.photo-print.submit'), [
            'photo_option_id' => $a4Id,
            'drive_links' => ['https://drive.google.com/file/d/disabled-size/view'],
            'delivery_district' => 'Hooghly',
        ])->assertRedirect(route('user.photo-print'))->assertSessionHasErrors('photo_option_id');
    }

    public function test_photo_orders_are_searchable_in_admin_and_unpaid_order_cannot_be_confirmed(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['name' => 'Photo Search Customer']);
        $optionId = $this->photoOptionId('photo-4x6');

        $this->actingAs($user)
            ->post(route('user.photo-print.submit'), [
                'photo_option_id' => $optionId,
                'photo_files' => [UploadedFile::fake()->image('private-photo.jpg', 10, 10)],
                'delivery_district' => 'South 24 Parganas',
            ])
            ->assertRedirect(route('user.orders.show', 1));
        $order = Order::query()->with(['files', 'payments'])->firstOrFail();
        $photoFile = $order->files->firstWhere('type', 'photo');

        $this->actingAs($admin)
            ->get(route('admin.photo-orders.index', ['q' => 'Photo Search']))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('South 24 Parganas');

        $this->get(route('admin.photo-orders.show', $order->id))
            ->assertOk()
            ->assertSee('private-photo.jpg')
            ->assertSee('Payment pending')
            ->assertSee('South 24 Parganas');
        $this->get(route('admin.photo-orders.files.download', [$order->id, $photoFile->id]))->assertOk();

        $this->from(route('admin.photo-orders.show', $order->id))
            ->patch(route('admin.photo-orders.status', $order->id), ['status' => Order::STATUS_CONFIRMED])
            ->assertRedirect(route('admin.photo-orders.show', $order->id))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => Order::STATUS_PAYMENT_PENDING, 'payment_status' => 'pending']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.photo-orders.index'))
            ->assertForbidden();
    }

    public function test_photo_order_details_and_photo_download_are_owner_scoped(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $optionId = $this->photoOptionId('photo-4x6');

        $this->actingAs($owner)->post(route('user.photo-print.submit'), [
            'photo_option_id' => $optionId,
            'photo_files' => [UploadedFile::fake()->image('owner-photo.jpg', 12, 12)],
            'delivery_district' => 'Kolkata',
        ]);
        $ownerOrder = Order::query()->where('user_id', $owner->id)->with('files')->firstOrFail();
        $ownerFile = $ownerOrder->files->first();

        $this->actingAs($otherUser)
            ->get(route('user.orders.show', $ownerOrder->id))
            ->assertNotFound();
        $this->get(route('user.orders.files.download', [$ownerOrder->id, $ownerFile->id]))
            ->assertNotFound();
    }

    private function photoOptionId(string $slug): int
    {
        $serviceId = PrintService::query()->where('slug', 'photo-print')->value('id');
        return (int) PrintQuality::query()->where('service_id', $serviceId)->where('slug', $slug)->value('id');
    }

    private function adminPhotoOptions(): array
    {
        $serviceId = PrintService::query()->where('slug', 'photo-print')->value('id');

        return PrintQuality::query()
            ->where('service_id', $serviceId)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->map(function (PrintQuality $quality): array {
                return [
                    'id' => $quality->id,
                    'name' => $quality->name,
                    'slug' => $quality->slug,
                    'description' => $quality->description,
                    'unit_price' => (string) $quality->pricingTiers()->where('min_quantity', 1)->value('unit_price'),
                    'enabled' => $quality->enabled,
                    'display_order' => $quality->display_order,
                ];
            })
            ->all();
    }
}