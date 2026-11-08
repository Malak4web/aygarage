<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingSlotRequest;
use App\Models\Booking;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class BookingSlotController extends Controller
{
    public function index(BookingSlotRequest $request): JsonResponse
    {
        $dateStr = $request->query('date') ?? $request->input('date');
        $targetDate = Carbon::createFromFormat('Y-m-d', $dateStr, 'Africa/Cairo')->startOfDay();

        $today = Carbon::today('Africa/Cairo');
        $isPastDate = $targetDate->lt($today);
        $isFriday = $targetDate->isFriday();

        $defaultSlots = [
            ['time' => '11:00', 'label' => '11:00 ص', 'off' => false],
            ['time' => '12:00', 'label' => '12:00 م', 'off' => true],
            ['time' => '13:00', 'label' => '1:00 م',  'off' => false],
            ['time' => '14:00', 'label' => '2:00 م',  'off' => false],
            ['time' => '15:00', 'label' => '3:00 م',  'off' => false],
            ['time' => '16:00', 'label' => '4:00 م',  'off' => false],
            ['time' => '17:00', 'label' => '5:00 م',  'off' => true],
            ['time' => '18:00', 'label' => '6:00 م',  'off' => false],
            ['time' => '19:00', 'label' => '7:00 م',  'off' => false],
            ['time' => '20:00', 'label' => '8:00 م',  'off' => false],
        ];

        $slots = Setting::get('booking_slots', $defaultSlots);
        $capacity = (int) Setting::get('booking_capacity_per_slot', 2);

        $bookings = Booking::whereDate('date', $dateStr)
            ->where('status', '!=', 'cancelled')
            ->get(['time']);

        $counts = [];
        foreach ($bookings as $b) {
            $t = substr($b->time, 0, 5);
            $counts[$t] = ($counts[$t] ?? 0) + 1;
        }

        $nowCairo = Carbon::now('Africa/Cairo');
        $result = [];

        foreach ($slots as $slot) {
            $slotTime = substr($slot['time'], 0, 5);
            try {
                $slotDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$dateStr} {$slotTime}", 'Africa/Cairo');
            } catch (\Throwable $e) {
                $slotDateTime = null;
            }

            $isPast = $isPastDate || ($targetDate->equalTo($today) && $slotDateTime && $slotDateTime->lte($nowCairo));
            $isSlotOff = !empty($slot['off']);
            $booked = $counts[$slotTime] ?? 0;
            $isFull = $booked >= $capacity;

            $available = !$isPastDate && !$isFriday && !$isSlotOff && !$isPast && !$isFull;

            $result[] = [
                'time' => $slotTime,
                'label' => $slot['label'] ?? $slotTime,
                'val' => $slot['label'] ?? $slotTime,
                'available' => $available,
                'off' => !$available,
                'booked' => $booked,
                'capacity' => $capacity,
            ];
        }

        return response()->json([
            'date' => $dateStr,
            'isFriday' => $isFriday,
            'data' => $result,
        ]);
    }
}
