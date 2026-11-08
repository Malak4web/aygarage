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
        $rawCode = $this->input('bikeModelCode') ?: $this->input('model');
        if ($rawCode) {
            $cleanCode = preg_replace('/^Zontes\s+/i', '', trim($rawCode));
            $this->merge(['bikeModelCode' => $cleanCode]);
        }

        if ($this->has('odo') && !$this->has('odometer')) {
            $this->merge(['odometer' => $this->input('odo')]);
        }

        if ($this->has('licenseUntil') && !$this->has('license_until')) {
            $this->merge(['license_until' => $this->input('licenseUntil')]);
        }
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'bikeModelCode' => [$isCreate ? 'required' : 'sometimes', 'string', 'exists:bike_models,code'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['sometimes', 'required', 'integer', 'min:1990', 'max:2030'],
            'plate' => ['nullable', 'string', 'max:50'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'color' => ['nullable', 'string', 'max:50'],
            'licenseUntil' => ['nullable', 'string', 'max:50'],
            'license_until' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'bikeModelCode.required' => 'موديل الموتوسيكل مطلوب',
            'bikeModelCode.exists' => 'موديل الموتوسيكل غير صالح',
            'year.required' => 'سنة الصنع مطلوبة',
            'year.integer' => 'سنة الصنع غير صالحة',
            'year.min' => 'سنة الصنع غير صالحة',
            'year.max' => 'سنة الصنع غير صالحة',
            'odometer.integer' => 'قراءة العداد غير صالحة',
            'odometer.min' => 'قراءة العداد غير صالحة',
            'licenseUntil.max' => 'تاريخ الرخصة غير صالح',
            'license_until.max' => 'تاريخ الرخصة غير صالح',
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
