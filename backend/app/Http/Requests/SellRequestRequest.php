<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SellRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merges = [];
        if ($this->has('phone')) {
            $merges['phone'] = Phone::normalize((string) $this->input('phone'));
        }
        if ($this->has('km')) {
            $merges['km'] = (int) $this->input('km');
        }
        if ($this->has('year')) {
            $merges['year'] = (int) $this->input('year');
        }
        if ($this->has('bikeModel') && $this->input('bikeModel')) {
            $merges['bikeModel'] = preg_replace('/^Zontes\s+/i', '', (string) $this->input('bikeModel'));
        }
        if (!empty($merges)) {
            $this->merge($merges);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^01[0125]\d{8}$/'],
            'bikeModel' => ['required', 'string', 'max:50', 'exists:bike_models,code'],
            'year' => ['required', 'integer', 'min:1990', 'max:2030'],
            'km' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'phone.required' => 'رقم الموبايل مطلوب',
            'phone.regex' => 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01',
            'bikeModel.required' => 'موديل الموتوسيكل مطلوب',
            'bikeModel.exists' => 'موديل الموتوسيكل المختار غير صالح',
            'year.required' => 'سنة الصنع مطلوبة',
            'km.required' => 'قراءة العداد مطلوبة',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => $validator->errors()->first() ?: 'بيانات طلب بيع الموتوسيكل غير صالحة',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
