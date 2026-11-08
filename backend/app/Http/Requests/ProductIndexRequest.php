<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:100'],
            'q' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable'],
            'brand.*' => ['string', 'max:100'],
            'viscosity' => ['nullable'],
            'viscosity.*' => ['string', 'max:50'],
            'type' => ['nullable'],
            'type.*' => ['string', 'max:50'],
            'minPrice' => ['nullable', 'integer', 'min:0'],
            'maxPrice' => ['nullable', 'integer', 'min:0'],
            'inStock' => ['nullable', 'boolean'],
            'onSale' => ['nullable', 'boolean'],
            'bestSeller' => ['nullable', 'boolean'],
            'weeklyOffer' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'in:new,newest,price_asc,price-asc,price_desc,price-desc,rating,best,bestseller'],
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
