<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceRecord;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserBike;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Service $service;
    protected UserBike $bikeA;
    protected UserBike $bikeB;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::updateOrCreate(
            ['key' => 'booking_capacity_per_slot'],
            ['value' => 2]
        );

        Setting::updateOrCreate(
            ['key' => 'booking_slots'],
            ['value' => [
                ['time' => '11:00', 'label' => '11:00 ص', 'off' => false],
                ['time' => '12:00', 'label' => '12:00 م', 'off' => true],
                ['time' => '13:00', 'label' => '1:00 م',  'off' => false],
                ['time' => '14:00', 'label' => '2:00 م',  'off' => false],
                ['time' => '15:00', 'label' => '3:00 م',  'off' => false],
            ]]
        );

        $this->userA = User::create([
            'name' => 'عميل أ',
            'phone' => '01011111111',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->userB = User::create([
            'name' => 'عميل ب',
            'phone' => '01022222222',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->service = Service::create([
            'code' => 's1',
            'name' => 'تغيير زيت وفلتر',
            'duration_label' => '30 دقيقة',
            'price' => 1770,
            'price_label' => '1,770 ج.م',
            'is_active' => true,
            'sort' => 1,
        ]);

        $this->bikeA = UserBike::create([
            'user_id' => $this->userA->id,
            'bike_model_code' => 'ZT350-T',
            'model_name' => 'Zontes ZT350-T',
            'year' => 2024,
            'odometer' => 12000,
        ]);

        $this->bikeB = UserBike::create([
            'user_id' => $this->userB->id,
            'bike_model_code' => 'ZT350-GK',
            'model_name' => 'Zontes ZT350-GK',
            'year' => 2023,
            'odometer' => 5000,
        ]);
    }

    public function test_get_booking_slots_returns_availability(): void
    {
        // Choose next Saturday (never a Friday)
        $futureDate = Carbon::now('Africa/Cairo')->next(Carbon::SATURDAY)->format('Y-m-d');

        $response = $this->getJson("/api/booking/slots?date={$futureDate}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'date',
            'isFriday',
            'data' => [
                '*' => ['time', 'label', 'val', 'available', 'off', 'booked', 'capacity'],
            ],
        ]);

        $data = $response->json('data');
        // 12:00 is off in settings
        $slot12 = collect($data)->firstWhere('time', '12:00');
        $this->assertNotNull($slot12);
        $this->assertFalse($slot12['available']);
        $this->assertTrue($slot12['off']);

        // 11:00 is open in settings
        $slot11 = collect($data)->firstWhere('time', '11:00');
        $this->assertNotNull($slot11);
        $this->assertTrue($slot11['available']);
        $this->assertFalse($slot11['off']);
    }

    public function test_full_slot_is_rejected(): void
    {
        $futureDate = Carbon::now('Africa/Cairo')->next(Carbon::SATURDAY)->format('Y-m-d');

        // Book slot 1st time
        $res1 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'bike_id' => $this->bikeA->id,
            'date' => $futureDate,
            'time' => '11:00',
        ]);
        $res1->assertStatus(201);

        // Book slot 2nd time (capacity = 2)
        $res2 = $this->actingAs($this->userB, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'bike_id' => $this->bikeB->id,
            'date' => $futureDate,
            'time' => '11:00',
        ]);
        $res2->assertStatus(201);

        // Verify slot is now unavailable in slots API
        $slotRes = $this->getJson("/api/booking/slots?date={$futureDate}");
        $slot11 = collect($slotRes->json('data'))->firstWhere('time', '11:00');
        $this->assertEquals(2, $slot11['booked']);
        $this->assertFalse($slot11['available']);

        // 3rd booking to the same slot must fail with 422
        $res3 = $this->actingAs($this->userA, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'custom_model' => 'ZT125-U',
            'date' => $futureDate,
            'time' => '11:00',
        ]);
        $res3->assertStatus(422)
            ->assertJson(['message' => 'هذا الموعد محجوز بالكامل، يرجى اختيار موعد آخر']);
    }

    public function test_past_slot_is_rejected(): void
    {
        // Yesterday date
        $yesterday = Carbon::now('Africa/Cairo')->subDay()->format('Y-m-d');

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'bike_id' => $this->bikeA->id,
            'date' => $yesterday,
            'time' => '11:00',
        ]);

        $response->assertStatus(422)
            ->assertJson(['message' => 'الميعاد المختار قد مضى بالفعل، يرجى اختيار موعد قادم']);
    }

    public function test_price_snapshot_comes_from_service(): void
    {
        $futureDate = Carbon::now('Africa/Cairo')->next(Carbon::SATURDAY)->format('Y-m-d');

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'bike_id' => $this->bikeA->id,
            'date' => $futureDate,
            'time' => '13:00',
            'price' => 50, // Malicious / bogus price from client
        ]);

        $response->assertStatus(201);
        $booking = $response->json('data') ?: $response->json();
        // Must take $this->service->price (1770)
        $this->assertEquals(1770, $booking['priceSnapshot']);
        $this->assertEquals(1770, $booking['price']);
    }

    public function test_ownership_user_a_cannot_view_or_cancel_user_b_booking(): void
    {
        $futureDate = Carbon::now('Africa/Cairo')->next(Carbon::SATURDAY)->format('Y-m-d');

        // User B creates a booking
        $createRes = $this->actingAs($this->userB, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'bike_id' => $this->bikeB->id,
            'date' => $futureDate,
            'time' => '14:00',
        ]);
        $createRes->assertStatus(201);
        $bNumber = $createRes->json('data.number') ?: $createRes->json('number');

        // User A lists their bookings - must NOT contain User B's booking
        $listRes = $this->actingAs($this->userA, 'sanctum')->getJson('/api/me/bookings');
        $listRes->assertStatus(200);
        $numbers = collect($listRes->json('data'))->pluck('number')->all();
        $this->assertNotContains($bNumber, $numbers);

        // User A attempts to cancel User B's booking - MUST return 404
        $cancelRes = $this->actingAs($this->userA, 'sanctum')->postJson("/api/me/bookings/{$bNumber}/cancel");
        $cancelRes->assertStatus(404);

        // User B can cancel their own booking
        $cancelByB = $this->actingAs($this->userB, 'sanctum')->postJson("/api/me/bookings/{$bNumber}/cancel");
        $cancelByB->assertStatus(200)
            ->assertJson(['message' => 'تم إلغاء الحجز بنجاح']);
    }

    public function test_ownership_service_records_user_a_cannot_view_user_b_records(): void
    {
        // Create service record for User B's bike
        $recordB = ServiceRecord::create([
            'user_bike_id' => $this->bikeB->id,
            'date' => '2026-08-10',
            'odometer' => 4500,
            'work' => 'صيانة الألفين',
            'technician' => 'الأسطى شريف',
            'cost' => 1500,
        ]);

        // User A attempts to view User B's bike service records - MUST return 404
        $resA = $this->actingAs($this->userA, 'sanctum')->getJson("/api/me/service-records?bikeId={$this->bikeB->id}");
        $resA->assertStatus(404);

        // User A lists all their service records - should NOT include User B's record
        $listA = $this->actingAs($this->userA, 'sanctum')->getJson('/api/me/service-records');
        $listA->assertStatus(200);
        $records = $listA->json('data');
        $this->assertEmpty($records);

        // User B lists their service records - should include recordB
        $listB = $this->actingAs($this->userB, 'sanctum')->getJson('/api/me/service-records');
        $listB->assertStatus(200);
        $recordsB = $listB->json('data');
        $this->assertCount(1, $recordsB);
        $this->assertEquals('صيانة الألفين', $recordsB[0]['work']);
    }

    public function test_booking_phone_is_normalized(): void
    {
        $futureDate = Carbon::now('Africa/Cairo')->next(Carbon::SATURDAY)->format('Y-m-d');

        $response = $this->actingAs($this->userA, 'sanctum')->postJson('/api/bookings', [
            'service_id' => $this->service->id,
            'custom_model' => 'ZT350-D',
            'date' => $futureDate,
            'time' => '15:00',
            'phone' => '+20 10 1234 5678', // with country code and spaces
        ]);

        $response->assertStatus(201);
        $phoneVal = $response->json('data.phone') ?: $response->json('phone');
        $this->assertEquals('01012345678', $phoneVal);
        $number = $response->json('data.number') ?: $response->json('number');
        $this->assertEquals('01012345678', Booking::where('number', $number)->value('phone'));
    }
}
