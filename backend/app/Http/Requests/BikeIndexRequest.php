<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BikeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merges = [];
        foreach (['family', 'year'] as $arrField) {
            if ($this->has($arrField) && !is_null($this->input($arrField)) && !is_array($this->input($arrField))) {
                $merges[$arrField] = [$this->input($arrField)];
            }
        }
        if (!empty($merges)) {
            $this->merge($merges);
        }
    }

    public function rules(): array
    {
        return [
            'family' => ['nullable', 'array'],
            'family.*' => ['string', 'max:50'],
            'model' => ['nullable', 'string', 'max:100'],
            'minPrice' => ['nullable', 'integer', 'min:0'],
            'maxPrice' => [
                'nullable',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->filled('minPrice') && (int) $value < (int) $this->input('minPrice')) {
                        $fail('الحد الأقصى للسعر يجب أن يكون أكبر من أو يساوي الحد الأدنى');
                    }
                },
            ],
            'kmRange' => ['nullable', 'string', 'in:under5k,5kTo20k,5k-20k,over20k'],
            'minScore' => ['nullable', 'integer', 'min:0', 'max:100'],
            'maxScore' => [
                'nullable',
                'integer',
                'min:0',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($this->filled('minScore') && (int) $value < (int) $this->input('minScore')) {
                        $fail('الحد الأقصى لدرجة الفحص يجب أن يكون أكبر من أو يساوي الحد الأدنى');
                    }
                },
            ],
            'year' => ['nullable', 'array'],
            'year.*' => ['integer', 'min:1900', 'max:2100'],
            'sort' => ['nullable', 'string', 'in:popular,score-desc,score_desc,price-asc,price_asc,price-desc,price_desc,new,newest'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:48'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'message' => 'بيانات الفلترة غير صالحة',
            'errors' => $validator->errors(),
        ], 422);

        throw new ValidationException($validator, $response);
    }
}
