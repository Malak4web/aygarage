<?php

namespace App\Http\Requests;

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
        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            ]);
        }
        if ($this->has('km')) {
            $this->merge([
                'km' => (int) $this->input('km'),
            ]);
        }
        if ($this->has('year')) {
            $this->merge([
                'year' => (int) $this->input('year'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'bikeModel' => ['required', 'string', 'max:50'],
            'year' => ['required', 'integer', 'min:1990', 'max:2030'],
            'km' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => 'بيانات طلب بيع الموتوسيكل غير صالحة',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
