<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserBikeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('odo') && !$this->has('odometer')) {
            $this->merge(['odometer' => $this->input('odo')]);
        }
    }

    public function rules(): array
    {
        return [
            'bikeModelCode' => ['sometimes', 'required', 'string', 'max:50'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['sometimes', 'required', 'integer', 'min:1990', 'max:2030'],
            'plate' => ['nullable', 'string', 'max:50'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:50'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => 'بيانات الموتوسيكل غير صالحة',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
