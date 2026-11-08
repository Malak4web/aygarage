<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['key' => 'delivery_fee', 'value' => 60]);
        Setting::create(['key' => 'free_delivery_threshold', 'value' => 3000]);
        Setting::create(['key' => 'delivery_days_count', 'value' => 3]);

        $this->userA = User::factory()->create(['phone' => '01011111111']);
        $this->userB = User::factory()->create(['phone' => '01022222222']);

        $category = Category::create([
            'slug' => 'oils',
            'name' => 'زيوت وفلاتر',
            'icon' => 'oil',
            'sort' => 1,
        ]);

        $this->product = Product::create([
            'slug' => 'test-product',
            'name' => 'منتج اختباري',
            'brand' => 'TestBrand',
            'sku' => 'SKU-001',
            'category_id' => $category->id,
            'type' => 'oil',
            'price' => 500,
            'stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_order_totals_and_prices_taken_from_db(): void
    {
        Coupon::create([
            'code' => 'TEST10',
            'type' => 'percent',
            'value' => 10,
            'min_subtotal' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                // Client sends a fake unit_price of 100, but DB has 500
                ['productId' => $this->product->id, 'qty' => 2, 'unit_price' => 100],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع الثورة',
            'delivery_method' => 'home',
            'payment_method' => 'cod',
            'coupon_code' => 'TEST10',
        ]);

        $response->assertStatus(201);
        $order = $response->json('data');

        // Subtotal must be 500 * 2 = 1000, NOT 100 * 2 = 200
        $this->assertEquals(1000, $order['subtotal']);
        // Discount 10% = 100
        $this->assertEquals(100, $order['discount']);
        // Delivery fee for subtotal < 3000 is 60
        $this->assertEquals(60, $order['deliveryFee']);
        // Total = 1000 - 100 + 60 = 960
        $this->assertEquals(960, $order['totalNum']);
    }

    public function test_pickup_delivery_fee_is_zero(): void
    {
        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);

        $response->assertStatus(201);
        $order = $response->json('data');

        $this->assertEquals(0, $order['deliveryFee']);
        $this->assertEquals(500, $order['totalNum']);
    }

    public function test_free_delivery_threshold(): void
    {
        // 7 units * 500 = 3500 >= 3000 threshold
        $this->product->update(['stock' => 10]);

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 7],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع الثورة',
            'delivery_method' => 'home',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(201);
        $order = $response->json('data');

        $this->assertEquals(3500, $order['subtotal']);
        $this->assertEquals(0, $order['deliveryFee']);
        $this->assertEquals(3500, $order['totalNum']);
    }

    public function test_fixed_coupon_discount(): void
    {
        Coupon::create([
            'code' => 'FIXED50',
            'type' => 'fixed',
            'value' => 50,
            'min_subtotal' => 400,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
            'coupon_code' => 'FIXED50',
        ]);

        $response->assertStatus(201);
        $order = $response->json('data');

        $this->assertEquals(50, $order['discount']);
        $this->assertEquals(450, $order['totalNum']);
    }

    public function test_order_above_stock_is_rejected(): void
    {
        // Product stock is 5
        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 6],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
        // Product stock remains 5
        $this->assertEquals(5, $this->product->fresh()->stock);
    }

    public function test_stock_is_decremented_on_success(): void
    {
        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 2],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);

        $response->assertStatus(201);
        // Product stock decremented from 5 to 3
        $this->assertEquals(3, $this->product->fresh()->stock);
    }

    public function test_two_orders_for_the_last_unit_cannot_both_succeed(): void
    {
        $this->product->update(['stock' => 1]);

        // First order requests the last unit (1)
        $firstOrder = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل أ',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $firstOrder->assertStatus(201);
        $this->assertEquals(0, $this->product->fresh()->stock);

        // Second order for the same unit must fail
        $secondOrder = $this->actingAs($this->userB, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل ب',
            'phone' => '01022222222',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $secondOrder->assertStatus(422);
        $secondOrder->assertJsonValidationErrors(['items']);
    }

    public function test_user_ownership_user_a_cannot_view_user_b_order(): void
    {
        // User B places an order
        $createResponse = $this->actingAs($this->userB, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل ب',
            'phone' => '01022222222',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);

        $createResponse->assertStatus(201);
        $orderNumber = $createResponse->json('data.number');

        // User B can view their own order
        $viewByB = $this->actingAs($this->userB, 'sanctum')->getJson("/api/me/orders/{$orderNumber}");
        $viewByB->assertStatus(200);

        // User A gets 404 when querying User B's order
        $viewByA = $this->actingAs($this->userA, 'sanctum')->getJson("/api/me/orders/{$orderNumber}");
        $viewByA->assertStatus(404);
    }
}
