<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BookingSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'string', 'date_format:Y-m-d'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $dateStr = $this->query('date') ?? $this->input('date');

            if (is_string($dateStr) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                try {
                    $targetDate = Carbon::createFromFormat('Y-m-d', $dateStr, 'Africa/Cairo')->startOfDay();
                    $today = Carbon::today('Africa/Cairo');
                    $daysAhead = (int) Setting::get('booking_days_ahead', 30);
                    $maxDate = (clone $today)->addDays($daysAhead);

                    if ($targetDate->lt($today)) {
                        $validator->errors()->add('date', 'تاريخ الحجز يجب أن يكون اليوم أو في المستقبل');
                    } elseif ($targetDate->gt($maxDate)) {
                        $validator->errors()->add('date', "تاريخ الحجز يتجاوز الحد الأقصى المسموح به للحجز ({$daysAhead} يوماً)");
                    }
                } catch (\Throwable $e) {
                    $validator->errors()->add('date', 'تاريخ الحجز غير صالح');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'date.required' => 'تاريخ الحجز مطلوب',
            'date.string' => 'تاريخ الحجز غير صالح',
            'date.date_format' => 'تاريخ الحجز غير صالح (الصيغة المطلوبة: YYYY-MM-DD)',
        ];
    }
}
