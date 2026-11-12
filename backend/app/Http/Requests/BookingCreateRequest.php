<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\Setting;
use App\Models\UserBike;
use App\Support\Phone;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BookingCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $userBikeId = $this->input('user_bike_id') ?? $this->input('userBikeId') ?? $this->input('bike_id') ?? $this->input('bikeChoice');
        if ($userBikeId === 'custom' || empty($userBikeId)) {
            $userBikeId = null;
        } elseif (is_numeric($userBikeId)) {
            $userBikeId = (int) $userBikeId;
        }

        $serviceId = $this->input('service_id') ?? $this->input('serviceId');
        if (is_numeric($serviceId)) {
            $serviceId = (string) $serviceId;
        }

        $time = $this->input('time');
        if (is_string($time)) {
            $time = trim($time);
            $slots = Setting::get('booking_slots', []);
            foreach (is_array($slots) ? $slots : [] as $s) {
                if (! is_array($s) || ! isset($s['time']) || ! is_string($s['time'])) {
                    continue;
                }
                if ($time === $s['time'] || (isset($s['label']) && $time === $s['label'])) {
                    $time = $s['time'];
                    break;
                }
            }
        }

        $rawPhone = $this->input('phone');
        $phone = is_string($rawPhone) && trim($rawPhone) !== ''
            ? Phone::normalize($rawPhone)
            : ($this->user()?->phone ? Phone::normalize($this->user()->phone) : null);

        $customModel = $this->input('custom_model') ?? $this->input('customModel');
        if (empty($customModel) && empty($userBikeId)) {
            $customModel = $this->input('bike');
        }

        $odo = $this->input('odometer') ?? $this->input('customOdo') ?? $this->input('odo');

        $this->merge([
            'user_bike_id' => $userBikeId,
            'service_id' => $serviceId,
            'time' => $time,
            'phone' => $phone,
            'custom_model' => is_string($customModel) ? trim($customModel) : null,
            'odometer' => is_numeric($odo) ? (int) $odo : null,
            'notes' => is_string($this->input('notes')) ? trim($this->input('notes')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'string', 'max:50'],
            'date' => ['required', 'string', 'date_format:Y-m-d'],
            'time' => ['required', 'string', 'max:10'],
            'user_bike_id' => ['nullable', 'integer'],
            'custom_model' => ['nullable', 'string', 'max:100'],
            'odometer' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'phone' => ['nullable', 'string', 'regex:/^01[0125]\d{8}$/'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // 1. Validate service exists and is active (Item 4 & 10)
            $serviceId = $this->input('service_id');
            if (is_string($serviceId)) {
                $service = Service::where('id', $serviceId)->orWhere('code', $serviceId)->first();
                if (!$service) {
                    $validator->errors()->add('service_id', 'الخدمة المطلوبة غير متوفرة');
                } elseif (!$service->is_active) {
                    $validator->errors()->add('service_id', 'الخدمة المختارة غير متاحة حالياً');
                }
            }

            // 2. Validate time is a valid, open slot in booking_slots (Item 2)
            $time = $this->input('time');
            if (is_string($time)) {
                $slots = Setting::get('booking_slots', []);
                if (is_string($slots)) {
                    $slots = json_decode($slots, true);
                }
                if (!is_array($slots)) {
                    $slots = [];
                }
                $matchedSlot = collect($slots)->first(function ($s) use ($time) {
                    return is_array($s) && (($s['time'] ?? '') === $time || ($s['label'] ?? '') === $time);
                });

                if (!$matchedSlot || !empty($matchedSlot['off'])) {
                    $validator->errors()->add('time', 'الميعاد ده مش متاح');
                }
            }

            // 3. Validate date is within booking window (today through 30 days) and not Friday (Item 2)
            $date = $this->input('date');
            if (is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                try {
                    $targetDate = Carbon::createFromFormat('Y-m-d', $date, 'Africa/Cairo')->startOfDay();
                    $today = Carbon::today('Africa/Cairo');
                    $daysAhead = (int) Setting::get('booking_days_ahead', 30);
                    $maxDate = (clone $today)->addDays($daysAhead);

                    if ($targetDate->lt($today)) {
                        $validator->errors()->add('date', 'الميعاد المختار قد مضى بالفعل، يرجى اختيار موعد قادم');
                    } elseif ($targetDate->gt($maxDate)) {
                        $validator->errors()->add('date', "تاريخ الحجز يتجاوز الحد الأقصى المسموح به للحجز ({$daysAhead} يوماً)");
                    } elseif ($targetDate->isFriday()) {
                        $validator->errors()->add('date', 'الجراج إجازة يوم الجمعة، يرجى اختيار يوم آخر');
                    }
                } catch (\Throwable $e) {
                    $validator->errors()->add('date', 'تاريخ الحجز غير صالح');
                }
            }

            // 4. Validate past time today (Item 2)
            if (is_string($date) && is_string($time) && empty($validator->errors()->get('date')) && empty($validator->errors()->get('time'))) {
                try {
                    $slotDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'Africa/Cairo');
                    if ($slotDateTime->isPast()) {
                        $validator->errors()->add('time', 'الميعاد المختار قد مضى بالفعل، يرجى اختيار موعد قادم');
                    }
                } catch (\Throwable $e) {}
            }

            // 5. Bike validation
            $userBikeId = $this->input('user_bike_id');
            $customModel = $this->input('custom_model');

            if (empty($userBikeId) && empty($customModel)) {
                $validator->errors()->add('custom_model', 'يرجى اختيار موتوسيكل مسجل أو تحديد موديل الموتوسيكل');
            }

            if (!empty($userBikeId)) {
                $owned = UserBike::where('id', $userBikeId)
                    ->where('user_id', $this->user()?->id)
                    ->exists();

                if (!$owned) {
                    $validator->errors()->add('user_bike_id', 'الموتوسيكل المختار غير موجود في حسابك');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'الخدمة المطلوبة يجب تحديدها',
            'service_id.string' => 'معرّف الخدمة غير صالح',
            'date.required' => 'تاريخ الحجز مطلوب',
            'date.string' => 'تاريخ الحجز غير صالح',
            'date.date_format' => 'تاريخ الحجز غير صالح (الصيغة المطلوبة: YYYY-MM-DD)',
            'time.required' => 'وقت الحجز مطلوب',
            'time.string' => 'وقت الحجز غير صالح',
            'phone.regex' => 'رقم الموبايل يجب أن يكون رقماً مصرياً صحيحاً (مثال: 01012345678)',
            'odometer.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً',
            'odometer.min' => 'قراءة العداد لا يمكن أن تكون بالسالب',
        ];
    }
}
