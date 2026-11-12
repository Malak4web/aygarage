<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression tests for the issues found in the final review.
 */
class FinalReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_wrong_admin_password_shows_error_on_login_field(): void
    {
        Livewire::test(Login::class)
            ->fillForm(['login' => '01000000001', 'password' => 'definitely-wrong'])
            ->call('authenticate')
            ->assertHasFormErrors(['login']);

        $this->assertGuest();
    }

    public function test_admin_can_log_in_with_plus20_phone(): void
    {
        $admin = $this->admin();
        $admin->forceFill(['password' => 'Admin#Test1234'])->save();

        Livewire::test(Login::class)
            ->fillForm(['login' => '+20 10 0000 0001', 'password' => 'Admin#Test1234'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_user_form_rejects_same_phone_in_other_format(): void
    {
        $this->actingAs($this->admin());

        // Karim is seeded as 01012345678.
        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'عميل مكرر',
                'phone' => '+20 10 1234 5678',
                'password' => 'Secret#12345',
            ])
            ->call('create')
            ->assertHasFormErrors(['phone']);

        $this->assertSame(1, User::where('phone', '01012345678')->count());
    }

    public function test_admin_user_form_rejects_duplicate_email(): void
    {
        $this->actingAs($this->admin());
        $taken = User::whereNotNull('email')->firstOrFail()->email;

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'عميل جديد',
                'phone' => '01112223334',
                'email' => strtoupper($taken),
                'password' => 'Secret#12345',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    }

    public function test_reactivating_order_without_stock_is_blocked_with_message(): void
    {
        $this->actingAs($this->admin());

        $order = Order::has('items')->firstOrFail();
        $item = $order->items->first();

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'cancelled'])
            ->call('save')
            ->assertHasNoFormErrors();

        // Someone else buys everything in the meantime.
        Product::whereKey($item->product_id)->update(['stock' => 0]);

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'preparing'])
            ->call('save')
            ->assertHasFormErrors(['status']);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(0, Product::find($item->product_id)->stock);
    }

    public function test_cancelling_twice_returns_stock_once(): void
    {
        $this->actingAs($this->admin());

        $order = Order::has('items')->firstOrFail();
        $item = $order->items->first();
        $before = Product::find($item->product_id)->stock;

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'cancelled'])
            ->call('save');

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'cancelled'])
            ->call('save');

        $this->assertSame($before + $item->qty, Product::find($item->product_id)->stock);
    }

    public function test_order_totals_and_payment_method_cannot_be_changed_from_admin(): void
    {
        $this->actingAs($this->admin());
        $order = Order::firstOrFail();

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['total' => 1, 'payment_method' => 'card', 'status' => 'preparing'])
            ->call('save');

        $fresh = $order->fresh();
        $this->assertSame($order->total, $fresh->total);
        $this->assertSame($order->payment_method, $fresh->payment_method);
        $this->assertSame('preparing', $fresh->status);
    }

    public function test_uploaded_product_image_is_served_by_the_api(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->actingAs($this->admin());

        $product = Product::where('slug', 'brembo-front-brake-pads')->firstOrFail();
        $upload = \Illuminate\Http\UploadedFile::fake()->image('brembo.jpg', 600, 600);

        Livewire::test(\App\Filament\Resources\Products\Pages\EditProduct::class, ['record' => $product->id])
            ->set('data.images', ['new1' => ['path' => []]])
            ->set('data.images.new1.path', $upload)
            ->call('save')
            ->assertHasNoFormErrors();

        $image = $product->images()->first();
        $this->assertNotNull($image);
        $this->assertStringStartsWith('products/', $image->path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($image->path);

        $json = $this->getJson('/api/products/brembo-front-brake-pads')->assertOk()->json();
        $data = $json['data'] ?? $json;
        $this->assertStringEndsWith('/storage/'.$image->path, $data['image']);
        $this->assertSame($data['image'], $data['images'][0]);
    }

    public function test_upload_rejects_non_images(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->actingAs($this->admin());

        $product = Product::where('slug', 'brembo-front-brake-pads')->firstOrFail();
        $upload = \Illuminate\Http\UploadedFile::fake()->create('virus.php', 10, 'application/x-php');

        Livewire::test(\App\Filament\Resources\Products\Pages\EditProduct::class, ['record' => $product->id])
            ->set('data.images', ['new1' => ['path' => []]])
            ->set('data.images.new1.path', $upload)
            ->call('save')
            ->assertHasFormErrors();

        $this->assertSame(0, $product->images()->count());
    }

    public function test_dashboard_widgets_render_with_real_counts(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(\App\Filament\Widgets\StatsOverview::class)
            ->assertOk()
            ->assertSee('حجوزات صيانة اليوم')
            ->assertSee('الطلبات الجديدة');

        // Yuasa battery is seeded with stock 0, so it must show as low stock.
        Livewire::test(\App\Filament\Widgets\LowStockProductsTable::class)
            ->assertOk()
            ->assertSee('Yuasa');

        Livewire::test(\App\Filament\Widgets\TodayBookingsTable::class)->assertOk();
    }

    public function test_search_singular_word_finds_whole_category(): void
    {
        $oils = Product::whereHas('category', fn ($q) => $q->where('slug', 'oils'))
            ->where('is_active', true)
            ->count();

        $json = $this->getJson('/api/products?perPage=48&q='.urlencode('زيت'))->assertOk()->json();
        $slugs = collect($json['data'])->pluck('slug');

        $this->assertGreaterThanOrEqual($oils, $slugs->count());
        $this->assertContains('castrol-power1-racing-10w40', $slugs->all());
    }

    public function test_malformed_booking_settings_never_return_500(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $token = $customer->createToken('test')->plainTextToken;
        $date = now('Africa/Cairo')->addDays(2);
        if ($date->isFriday()) {
            $date->addDay();
        }

        Setting::set('booking_slots', 'not-json-at-all');

        $this->getJson('/api/booking/slots?date='.$date->toDateString())
            ->assertStatus(200);

        $this->withToken($token)->postJson('/api/bookings', [
            'service_id' => 's1',
            'date' => $date->toDateString(),
            'time' => '13:00',
            'custom_model' => 'ZT350-T',
        ])->assertStatus(422);

        Setting::set('booking_slots', [['label' => 'no time key'], 'string-row']);

        $this->withToken($token)->postJson('/api/bookings', [
            'service_id' => 's1',
            'date' => $date->toDateString(),
            'time' => '13:00',
            'custom_model' => 'ZT350-T',
        ])->assertStatus(422);
    }

    public function test_malformed_governorates_setting_never_returns_500(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $token = $customer->createToken('test')->plainTextToken;

        Setting::set('governorates', [['name' => 'القاهرة', 'areas' => 'مدينة نصر']]);

        $this->withToken($token)->postJson('/api/orders', [
            'name' => 'كريم محمود',
            'phone' => '01012345678',
            'deliveryMethod' => 'home',
            'paymentMethod' => 'cod',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع 9',
            'items' => [['productId' => 'ngk-iridium-cr8eix', 'qty' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors(['area']);
    }

    public function test_payment_methods_setting_cannot_enable_card(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();
        $token = $customer->createToken('test')->plainTextToken;

        Setting::set('payment_methods', [['id' => 'card', 'title' => 'كارت', 'desc' => '']]);

        $this->withToken($token)->postJson('/api/orders', [
            'name' => 'كريم محمود',
            'phone' => '01012345678',
            'deliveryMethod' => 'home',
            'paymentMethod' => 'card',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع 9',
            'items' => [['productId' => 'ngk-iridium-cr8eix', 'qty' => 1]],
        ])->assertStatus(422)->assertJsonValidationErrors(['payment_method']);
    }
}
