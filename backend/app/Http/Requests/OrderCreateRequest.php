<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;

class OrderCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $customer = $this->input('customer');
        $name = $this->input('name') ?? ($customer['name'] ?? null);
        $phone = $this->input('phone') ?? ($customer['phone'] ?? null);
        $gov = $this->input('governorate') ?? ($customer['governorate'] ?? null);
        $area = $this->input('area') ?? ($customer['area'] ?? null);
        $street = $this->input('street') ?? ($this->input('address') ?? ($customer['address'] ?? ($customer['street'] ?? null)));

        $deliveryMethod = $this->input('delivery_method') ?? $this->input('deliveryMethod') ?? 'home';
        $paymentMethod = $this->input('payment_method') ?? $this->input('paymentMethod') ?? 'cod';
        $couponCode = $this->input('coupon_code') ?? $this->input('couponCode') ?? null;

        $this->merge([
            'name' => is_string($name) ? trim($name) : $name,
            'phone' => is_string($phone) ? Phone::normalize($phone) : $phone,
            'governorate' => is_string($gov) ? trim($gov) : $gov,
            'area' => is_string($area) ? trim($area) : $area,
            'street' => is_string($street) ? trim($street) : $street,
            'delivery_method' => $deliveryMethod,
            'payment_method' => $paymentMethod,
            'coupon_code' => is_string($couponCode) && trim($couponCode) !== '' ? strtoupper(trim($couponCode)) : null,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'items' => ['required', 'array', 'min:1'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^01[0125]\d{8}$/'],
            'delivery_method' => ['required', 'in:home,pickup'],
            'payment_method' => ['required', 'in:cod,pickup,card,vodafone,instapay'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->input('delivery_method') === 'home') {
            $rules['governorate'] = ['required', 'string', 'max:100'];
            $rules['area'] = ['required', 'string', 'max:100'];
            $rules['street'] = ['required', 'string', 'max:255'];
        } else {
            $rules['governorate'] = ['nullable', 'string', 'max:100'];
            $rules['area'] = ['nullable', 'string', 'max:100'];
            $rules['street'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'items.required' => 'سلة المشتريات فارغة',
            'items.min' => 'سلة المشتريات فارغة',
            'items.*.qty.required' => 'الكمية مطلوبة',
            'items.*.qty.integer' => 'الكمية غير صالحة',
            'items.*.qty.min' => 'أقل كمية مطلوبة هي 1',
            'name.required' => 'الاسم بالكامل مطلوب',
            'phone.required' => 'رقم الموبايل مطلوب',
            'phone.regex' => 'رقم الموبايل يجب أن يكون رقماً مصرياً صحيحاً (مثال: 01012345678)',
            'delivery_method.required' => 'طريقة التوصيل مطلوبة',
            'delivery_method.in' => 'طريقة التوصيل غير صالحة',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.in' => 'طريقة الدفع غير صالحة',
            'governorate.required' => 'المحافظة مطلوبة للتوصيل المنزلي',
            'area.required' => 'المنطقة مطلوبة للتوصيل المنزلي',
            'street.required' => 'العنوان بالتفصيل مطلوب للتوصيل المنزلي',
        ];
    }
}
