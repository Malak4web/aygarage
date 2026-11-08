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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Product $product;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create(['key' => 'delivery_fee', 'value' => 60]);
        Setting::create(['key' => 'free_delivery_threshold', 'value' => 3000]);
        Setting::create(['key' => 'delivery_days_count', 'value' => 3]);
        Setting::create([
            'key' => 'governorates',
            'value' => [
                ['name' => 'القاهرة', 'areas' => ['مدينة نصر', 'مصر الجديدة']],
                ['name' => 'الجيزة', 'areas' => ['الدقي', 'المهندسين']],
            ],
        ]);

        $this->userA = User::factory()->create(['phone' => '01011111111']);
        $this->userB = User::factory()->create(['phone' => '01022222222']);

        $this->category = Category::create([
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
            'category_id' => $this->category->id,
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

        $this->assertEquals(1000, $order['subtotal']);
        $this->assertEquals(100, $order['discount']);
        $this->assertEquals(60, $order['deliveryFee']);
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
        $response->assertJsonValidationErrors(['items.0.qty']);
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
        $this->assertEquals(3, $this->product->fresh()->stock);
    }

    public function test_two_orders_for_the_last_unit_cannot_both_succeed(): void
    {
        $this->product->update(['stock' => 1]);

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
        $secondOrder->assertJsonValidationErrors(['items.0.qty']);
    }

    public function test_user_ownership_user_a_cannot_view_user_b_order(): void
    {
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

        $viewByB = $this->actingAs($this->userB, 'sanctum')->getJson("/api/me/orders/{$orderNumber}");
        $viewByB->assertStatus(200);

        $viewByA = $this->actingAs($this->userA, 'sanctum')->getJson("/api/me/orders/{$orderNumber}");
        $viewByA->assertStatus(404);
    }

    public function test_invalid_option_returns_422(): void
    {
        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1, 'optionCode' => 'bogus'],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.optionCode']);
    }

    public function test_product_with_options_requires_valid_option(): void
    {
        ProductOption::create([
            'product_id' => $this->product->id,
            'code' => '1l',
            'label' => '1 لتر',
            'price' => 520,
        ]);

        // 1. Missing optionCode
        $resMissing = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $resMissing->assertStatus(422);
        $resMissing->assertJsonValidationErrors(['items.0.optionCode']);

        // 2. Bogus optionCode
        $resBogus = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1, 'optionCode' => 'bogus'],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $resBogus->assertStatus(422);
        $resBogus->assertJsonValidationErrors(['items.0.optionCode']);

        // 3. Valid optionCode
        $resValid = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1, 'optionCode' => '1l'],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $resValid->assertStatus(201);
        $this->assertEquals(520, $resValid->json('data.totalNum'));
    }

    public function test_inactive_product_returns_422(): void
    {
        $this->product->update(['is_active' => false]);

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);

        $response->assertStatus(422);
    }

    public function test_array_inputs_return_422_never_500(): void
    {
        // productId as array
        $res1 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => ['x'], 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $res1->assertStatus(422);

        // optionCode as array
        $res2 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'optionCode' => ['a'], 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $res2->assertStatus(422);

        // qty as non-integer string
        $res3 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 'abc'],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'pickup',
        ]);
        $res3->assertStatus(422);
    }

    public function test_payment_method_other_than_cod_or_pickup_returns_422(): void
    {
        $resCard = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'home',
            'payment_method' => 'card',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع الثورة',
        ]);
        $resCard->assertStatus(422);
        $resCard->assertJsonValidationErrors(['payment_method']);

        $resVodafone = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'home',
            'payment_method' => 'vodafone',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع الثورة',
        ]);
        $resVodafone->assertStatus(422);
        $resVodafone->assertJsonValidationErrors(['payment_method']);
    }

    public function test_mismatched_delivery_and_payment_method_returns_422(): void
    {
        // home delivery with pickup payment
        $res1 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'home',
            'payment_method' => 'pickup',
            'governorate' => 'القاهرة',
            'area' => 'مدينة نصر',
            'street' => 'شارع الثورة',
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['payment_method']);

        // pickup delivery with cod payment
        $res2 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/orders', [
            'items' => [
                ['productId' => $this->product->id, 'qty' => 1],
            ],
            'name' => 'عميل تجريبي',
            'phone' => '01011111111',
            'delivery_method' => 'pickup',
            'payment_method' => 'cod',
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['payment_method']);
    }

    public function test_normal_percent_coupon_on_orders(): void
    {
        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'min_subtotal' => 200,
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
            'coupon_code' => 'SAVE10',
        ]);

        $response->assertStatus(201);
        $order = $response->json('data');

        // Product is 500, 10% of 500 = 50
        $this->assertEquals(50, $order['discount']);
        $this->assertEquals(450, $order['totalNum']);
    }

    public function test_percent_coupon_capped_at_100_percent(): void
    {
        Coupon::create([
            'code' => 'SUPER150',
            'type' => 'percent',
            'value' => 150,
            'min_subtotal' => 0,
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
            'coupon_code' => 'SUPER150',
        ]);

        $response->assertStatus(201);
        $order = $response->json('data');

        // Discount is capped at subtotal (500), cannot exceed 500
        $this->assertEquals(500, $order['discount']);
        $this->assertEquals(0, $order['totalNum']);
    }
}
