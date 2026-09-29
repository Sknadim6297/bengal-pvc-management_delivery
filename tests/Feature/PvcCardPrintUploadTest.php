<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PvcCardPrintUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_pdf_only_submission_creates_a_persistent_order_and_stores_the_file_privately(): void
    {
        $user = User::factory()->create();
        $pdf = UploadedFile::fake()->create('card.pdf', 120, 'application/pdf');

        $this->actingAs($user)
            ->from(route('user.pvc-card-print'))
            ->post(route('user.pvc-card-print.submit'), [
                'pdf_files' => [$pdf],
                'print_quality' => 'normal',
            ])
            ->assertRedirect(route('user.orders.show', 1));

        $order = Order::query()->with(['items', 'files', 'statusHistory'])->firstOrFail();
        $this->assertMatchesRegularExpression('/^PVC-\d{8}-\d{6}$/', $order->order_number);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(1, $order->quantity);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('117.00', $order->total_amount);
        $this->assertSame('normal', $order->items->first()->quality_slug);
        $this->assertCount(1, $order->files);
        $this->assertTrue(Storage::disk('local')->exists($order->files->first()->storage_path));
        $this->assertSame('card.pdf', $order->files->first()->original_name);
        $this->assertSame('pending', $order->statusHistory->first()->new_status);
    }

    public function test_drive_links_only_order_counts_and_stores_each_share_url(): void
    {
        $user = User::factory()->create();
        $links = [
            'https://drive.google.com/file/d/file-one/view?usp=sharing',
            'https://docs.google.com/document/d/document-two/edit',
        ];

        $this->actingAs($user)
            ->post(route('user.pvc-card-print.submit'), [
                'drive_links' => $links,
                'print_quality' => 'premium',
            ])
            ->assertRedirect(route('user.orders.show', 1));

        $order = Order::query()->with(['items', 'files'])->firstOrFail();
        $this->assertSame(2, $order->quantity);
        $this->assertSame('premium', $order->items->first()->quality_slug);
        $this->assertSame('220.00', $order->total_amount);
        $this->assertSame($links, $order->files->pluck('drive_url')->all());
    }

    public function test_order_quantity_combines_uploaded_pdfs_and_drive_links(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('user.pvc-card-print.submit'), [
                'pdf_files' => [
                    UploadedFile::fake()->create('first.pdf', 100, 'application/pdf'),
                    UploadedFile::fake()->create('second.pdf', 200, 'application/pdf'),
                ],
                'drive_links' => ['https://drive.google.com/file/d/file-one/view'],
                'print_quality' => 'normal',
                'include_card_cover' => '1',
                'quantity' => 900,
                'unit_price' => 1,
                'total_amount' => 1,
                'user_id' => 999999,
                'status' => 'delivered',
            ])
            ->assertRedirect(route('user.orders.show', 1));

        $order = Order::query()->with(['items', 'files'])->firstOrFail();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(3, $order->quantity);
        $this->assertSame('pending', $order->status);
        $this->assertSame('250.00', $order->total_amount);
        $this->assertCount(2, $order->files->where('type', 'pdf'));
        $this->assertCount(1, $order->files->where('type', 'drive'));
        $this->assertTrue($order->items->first()->cover_selected);
        $this->assertSame('45.00', $order->cover_amount);
    }

    public function test_invalid_files_drive_links_duplicates_and_empty_submissions_are_rejected(): void
    {
        $user = User::factory()->create();
        $invalidCases = [
            ['pdf_files' => [UploadedFile::fake()->create('notes.txt', 5, 'text/plain')]],
            ['drive_links' => ['https://example.com/file.pdf']],
            ['drive_links' => ['https://drive.google.com/not-a-share-link']],
            ['drive_links' => ['https://drive.google.com/open']],
            ['drive_links' => [
                'https://drive.google.com/file/d/file-one/view',
                'https://drive.google.com/file/d/file-one/view',
            ]],
            [],
        ];

        foreach ($invalidCases as $case) {
            $response = $this->actingAs($user)
                ->from(route('user.pvc-card-print'))
                ->post(route('user.pvc-card-print.submit'), $case + ['print_quality' => 'normal']);

            $response->assertRedirect(route('user.pvc-card-print'));
            $this->assertTrue($response->getSession()->has('errors'), json_encode($case));
        }

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_combined_pdf_size_over_80_mb_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('user.pvc-card-print'))
            ->post(route('user.pvc-card-print.submit'), [
                'pdf_files' => [
                    UploadedFile::fake()->create('first.pdf', 40960, 'application/pdf'),
                    UploadedFile::fake()->create('second.pdf', 40960, 'application/pdf'),
                    UploadedFile::fake()->create('third.pdf', 1, 'application/pdf'),
                ],
                'print_quality' => 'normal',
            ])
            ->assertRedirect(route('user.pvc-card-print'))
            ->assertSessionHasErrors('pdf_files');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_page_renders_multipart_form_and_global_drive_handler(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user.pvc-card-print'))
            ->assertOk()
            ->assertSee('id="pvcCardPrintForm"', false)
            ->assertSee('name="pdf_files[]"', false)
            ->assertSee('name="submission_key"', false)
            ->assertSee('window.addDriveLink = function addDriveLink()', false)
            ->assertSee('Total pcs = Files + Links:', false);
    }

    public function test_inactive_users_cannot_submit_print_files(): void
    {
        $user = User::factory()->create(['status' => User::STATUS_SUSPENDED]);

        $this->actingAs($user)
            ->post(route('user.pvc-card-print.submit'), [
                'drive_links' => ['https://drive.google.com/file/d/file-one/view'],
                'print_quality' => 'normal',
            ])
            ->assertRedirect('/login');
    }

    public function test_replaying_a_submission_key_does_not_create_another_order(): void
    {
        $user = User::factory()->create();
        $submissionKey = (string) \Illuminate\Support\Str::uuid();
        $payload = [
            'drive_links' => ['https://drive.google.com/file/d/file-one/view'],
            'print_quality' => 'normal',
            'submission_key' => $submissionKey,
        ];

        $this->actingAs($user)
            ->post(route('user.pvc-card-print.submit'), $payload)
            ->assertRedirect(route('user.orders.show', 1));

        $this->post(route('user.pvc-card-print.submit'), $payload)
            ->assertRedirect(route('user.orders.show', 1));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_status_history', 1);
    }
}