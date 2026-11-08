<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingCreateRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['service', 'userBike'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => BookingResource::collection($bookings),
        ]);
    }

    public function store(BookingCreateRequest $request): JsonResponse
    {
        $date = $request->input('date');
        $time = substr($request->input('time'), 0, 5);

        // 1. Timezone verification for Cairo
        try {
            $slotDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'Africa/Cairo');
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'وقت الحجز غير صالح',
                'errors' => ['time' => ['وقت الحجز غير صالح']],
            ], 422);
        }

        if ($slotDateTime->isPast()) {
            return response()->json([
                'message' => 'الميعاد المختار قد مضى بالفعل، يرجى اختيار موعد قادم',
                'errors' => ['time' => ['الميعاد المختار قد مضى بالفعل']],
            ], 422);
        }

        if (Carbon::parse($date)->isFriday()) {
            return response()->json([
                'message' => 'الجراج إجازة يوم الجمعة، يرجى اختيار يوم آخر',
                'errors' => ['date' => ['الجراج إجازة يوم الجمعة']],
            ], 422);
        }

        // 2. Concurrency & capacity check inside transaction
        $booking = DB::transaction(function () use ($request, $date, $time) {
            $capacity = (int) Setting::get('booking_capacity_per_slot', 2);

            $bookedCount = Booking::whereDate('date', $date)
                ->where(function ($q) use ($time) {
                    $q->where('time', $time)
                        ->orWhere('time', 'LIKE', "{$time}%");
                })
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate()
                ->count();

            if ($bookedCount >= $capacity) {
                abort(response()->json([
                    'message' => 'هذا الموعد محجوز بالكامل، يرجى اختيار موعد آخر',
                    'errors' => ['time' => ['هذا الموعد محجوز بالكامل']],
                ], 422));
            }

            // Price snapshot strictly from service in DB
            $serviceId = $request->input('service_id');
            $service = Service::where('id', $serviceId)->orWhere('code', $serviceId)->first();
            if (!$service) {
                abort(response()->json([
                    'message' => 'الخدمة المطلوبة غير متوفرة',
                    'errors' => ['service_id' => ['الخدمة المطلوبة غير متوفرة']],
                ], 422));
            }

            $priceSnapshot = $service->price;

            // Sequential number generator (BK-XXXX)
            $lastBooking = Booking::lockForUpdate()->orderByDesc('id')->first();
            $lastNum = 1042;
            if ($lastBooking && preg_match('/BK-(\d+)/', $lastBooking->number, $m)) {
                $lastNum = max($lastNum, intval($m[1]));
            }
            $number = 'BK-' . ($lastNum + 1);

            return Booking::create([
                'number' => $number,
                'user_id' => $request->user()->id,
                'user_bike_id' => $request->input('user_bike_id'),
                'custom_model' => $request->input('custom_model'),
                'odometer' => $request->input('odometer'),
                'service_id' => $service->id,
                'date' => $date,
                'time' => $time,
                'price_snapshot' => $priceSnapshot,
                'status' => 'confirmed',
                'phone' => $request->input('phone'),
                'notes' => $request->input('notes'),
            ]);
        });

        return (new BookingResource($booking->load(['service', 'userBike'])))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(Request $request, string $number): JsonResponse
    {
        $booking = Booking::where('number', $number)
            ->orWhere('id', $number)
            ->first();

        // Ownership rule: 404 for anyone else's booking
        if (!$booking || $booking->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'الحجز غير موجود',
            ], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'هذا الحجز تم إلغاؤه بالفعل',
            ], 422);
        }

        if ($booking->status === 'done') {
            return response()->json([
                'message' => 'لا يمكن إلغاء حجز صيانة مكتمل',
            ], 422);
        }

        // Slot must be in the future (Africa/Cairo)
        try {
            $slotDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $booking->date->format('Y-m-d') . ' ' . substr($booking->time, 0, 5),
                'Africa/Cairo'
            );
            if ($slotDateTime->isPast()) {
                return response()->json([
                    'message' => 'لا يمكن إلغاء موعد حجز في الماضي',
                ], 422);
            }
        } catch (\Throwable $e) {
            // If date/time cannot be parsed, allow proceed or handle
        }

        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'تم إلغاء الحجز بنجاح',
            'data' => new BookingResource($booking->load(['service', 'userBike'])),
        ]);
    }
}
