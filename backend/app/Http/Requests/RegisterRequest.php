<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
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
            'phone' => ['required', 'string', 'regex:/^01[0125]\d{8}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'bikeModel' => ['nullable', 'string', 'max:50', 'exists:bike_models,code'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب',
            'name.min' => 'الاسم يجب أن يكون حرفين على الأقل',
            'phone.required' => 'رقم الموبايل مطلوب',
            'phone.regex' => 'رقم الموبايل لازم يكون 11 رقم ويبدأ بـ 01',
            'phone.unique' => 'الرقم ده مسجل قبل كده، سجّل دخول بدل كده',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.min' => 'كلمة المرور يجب ألا تقل عن 6 أحرف',
            'bikeModel.exists' => 'موديل الموتوسيكل المختار غير صالح',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => $validator->errors()->first() ?: 'بيانات التسجيل غير صالحة',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
