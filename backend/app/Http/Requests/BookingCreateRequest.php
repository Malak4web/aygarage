<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Models\UserBike;
use App\Support\Phone;
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

        $time = $this->input('time');
        if (is_string($time)) {
            $time = trim($time);
            $slots = Setting::get('booking_slots', []);
            foreach ($slots as $s) {
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
            'service_id' => ['required'],
            'date' => ['required', 'date_format:Y-m-d'],
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
            'date.required' => 'تاريخ الحجز مطلوب',
            'date.date_format' => 'تاريخ الحجز غير صالح (الصيغة المطلوبة: YYYY-MM-DD)',
            'time.required' => 'وقت الحجز مطلوب',
            'time.string' => 'وقت الحجز غير صالح',
            'phone.regex' => 'رقم الموبايل يجب أن يكون رقماً مصرياً صحيحاً (مثال: 01012345678)',
            'odometer.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً',
            'odometer.min' => 'قراءة العداد لا يمكن أن تكون بالسالب',
        ];
    }
}
