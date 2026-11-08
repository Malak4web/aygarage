<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSettings;
use App\Filament\Resources\Bookings\Pages\CreateBooking;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceRecord;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBike;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/admin/login');
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = User::where('role', 'customer')->first();
        $this->assertNotNull($customer);

        $response = $this->actingAs($customer)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_panel_and_resources(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        // Check key resources
        $resources = [
            '/admin/products',
            '/admin/categories',
            '/admin/used-bikes',
            '/admin/orders',
            '/admin/bookings',
            '/admin/services',
            '/admin/coupons',
            '/admin/sell-requests',
            '/admin/users',
            '/admin/settings',
        ];

        foreach ($resources as $uri) {
            $res = $this->actingAs($admin)->get($uri);
            $res->assertStatus(200);
        }
    }

    public function test_admin_can_view_product_and_create(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin/products/create');
        $response->assertStatus(200);
    }

    public function test_livewire_can_change_pickup_order_status(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $order = Order::where('delivery_method', 'pickup')->first();
        if (!$order) {
            $order = Order::first();
            $order->update(['delivery_method' => 'pickup', 'payment_method' => 'pickup']);
        }

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'ready_for_pickup'])
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();
        $this->assertEquals('ready_for_pickup', $order->status);
    }

    public function test_livewire_cancelling_order_returns_stock(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $order = Order::where('status', '!=', 'cancelled')->has('items')->first();
        $this->assertNotNull($order);

        $item = $order->items->first();
        $product = Product::find($item->product_id);
        $initialStock = $product->stock;

        Livewire::test(EditOrder::class, ['record' => $order->id])
            ->fillForm(['status' => 'cancelled'])
            ->call('save')
            ->assertHasNoFormErrors();

        $product->refresh();
        $this->assertEquals($initialStock + $item->qty, $product->stock);

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_livewire_booking_action_completes_and_updates_bike(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $customer = User::where('role', 'customer')->first();
        $service = Service::first();

        $booking = Booking::create([
            'user_id' => $customer->id,
            'custom_model' => 'Zontes ZT350-X',
            'service_id' => $service->id,
            'date' => now()->toDateString(),
            'time' => '11:00',
            'status' => 'confirmed',
        ]);

        Livewire::test(ListBookings::class)
            ->callTableAction('completeAndRecord', $booking, data: [
                'bike_choice' => 'new',
                'new_bike_model_code' => 'ZT350-T',
                'new_bike_plate' => 'م ص ر 5555',
                'record_date' => now()->toDateString(),
                'odometer' => 12900,
                'work' => 'صيانة 10,000 كم',
                'technician' => 'أبو يوسف',
                'cost' => 650,
            ])
            ->assertHasNoTableActionErrors();

        $booking->refresh();
        $this->assertEquals('done', $booking->status);

        $this->assertDatabaseHas('service_records', [
            'booking_id' => $booking->id,
            'odometer' => 12900,
            'cost' => 650,
        ]);

        $createdBike = UserBike::where('user_id', $customer->id)->where('plate', 'م ص ر 5555')->first();
        $this->assertNotNull($createdBike);
        $this->assertEquals(12900, $createdBike->odometer);
        $this->assertEquals(15900, $createdBike->next_service_km);
        $this->assertEquals(now()->toDateString(), $createdBike->last_visit_at->format('Y-m-d'));
    }

    public function test_livewire_save_settings_no_500(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        Livewire::test(ManageSettings::class)
            ->fillForm([
                'store_name' => 'جراج أبو يوسف المطور',
                'phone' => '01099998888',
                'whatsapp' => '01099998888',
                'delivery_fee' => 85,
                'booking_days_ahead' => 30,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals(85, Setting::get('delivery_fee'));
        $this->assertSame('01099998888', Setting::get('phone'));
    }

    public function test_livewire_create_booking_from_admin(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $customer = User::where('role', 'customer')->first();
        $service = Service::first();

        Livewire::test(CreateBooking::class)
            ->fillForm([
                'user_id' => $customer->id,
                'service_id' => $service->id,
                'date' => now()->addDays(5)->toDateString(),
                'time' => '14:00',
                'status' => 'confirmed',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $createdBooking = Booking::where('user_id', $customer->id)
            ->where('time', '14:00')
            ->first();

        $this->assertNotNull($createdBooking);
        $this->assertStringStartsWith('BK-', $createdBooking->number);
        $this->assertEquals('confirmed', $createdBooking->status);
    }
}
