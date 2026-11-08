<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\UserBike;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $category = Category::first();

        $response = $this->actingAs($admin)->get('/admin/products/create');
        $response->assertStatus(200);
    }

    public function test_booking_complete_and_service_record_workflow(): void
    {
        $customer = User::where('role', 'customer')->first();
        $bike = $customer->bikes()->first();
        $service = Service::first();

        $booking = Booking::create([
            'number' => 'BK-9999',
            'user_id' => $customer->id,
            'user_bike_id' => $bike->id,
            'service_id' => $service->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'time' => '10:00',
            'odometer' => 15000,
            'price_snapshot' => 650,
            'status' => 'confirmed',
        ]);

        $this->assertEquals('confirmed', $booking->status);

        // Simulate complete action logic
        $recordDate = now()->format('Y-m-d');
        $newOdo = 15200;

        $booking->update(['status' => 'done']);
        $serviceRecord = ServiceRecord::create([
            'user_bike_id' => $bike->id,
            'booking_id' => $booking->id,
            'date' => $recordDate,
            'odometer' => $newOdo,
            'work' => $service->name,
            'technician' => 'أبو يوسف',
            'cost' => 650,
        ]);

        $bike->update([
            'odometer' => max($bike->odometer ?? 0, $newOdo),
            'last_visit_at' => $recordDate,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'done',
        ]);

        $this->assertDatabaseHas('service_records', [
            'booking_id' => $booking->id,
            'user_bike_id' => $bike->id,
            'odometer' => 15200,
        ]);

        $bike->refresh();
        $this->assertEquals(15200, $bike->odometer);
        $this->assertEquals($recordDate, $bike->last_visit_at?->format('Y-m-d'));
    }
}
