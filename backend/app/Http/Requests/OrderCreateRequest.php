<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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

        $items = $this->input('items');
        if (is_array($items)) {
            foreach ($items as &$item) {
                if (is_array($item)) {
                    if (isset($item['productId']) && is_numeric($item['productId'])) {
                        $item['productId'] = (string) $item['productId'];
                    }
                    if (isset($item['product_id']) && is_numeric($item['product_id'])) {
                        $item['product_id'] = (string) $item['product_id'];
                    }
                    if (isset($item['optionCode']) && is_numeric($item['optionCode'])) {
                        $item['optionCode'] = (string) $item['optionCode'];
                    }
                    if (isset($item['option_code']) && is_numeric($item['option_code'])) {
                        $item['option_code'] = (string) $item['option_code'];
                    }
                }
            }
            unset($item);
        }

        $mergeData = [
            'name' => is_string($name) ? trim($name) : $name,
            'phone' => is_string($phone) ? Phone::normalize($phone) : $phone,
            'governorate' => is_string($gov) ? trim($gov) : $gov,
            'area' => is_string($area) ? trim($area) : $area,
            'street' => is_string($street) ? trim($street) : $street,
            'delivery_method' => is_string($deliveryMethod) ? trim($deliveryMethod) : $deliveryMethod,
            'payment_method' => is_string($paymentMethod) ? trim($paymentMethod) : $paymentMethod,
            'coupon_code' => is_string($couponCode) && trim($couponCode) !== '' ? strtoupper(trim($couponCode)) : null,
        ];
        if (is_array($items)) {
            $mergeData['items'] = $items;
        }

        $this->merge($mergeData);
    }

    public function rules(): array
    {
        $configuredMethods = Setting::get('payment_methods', []);
        $allowedPaymentMethods = collect(is_array($configuredMethods) ? $configuredMethods : [])
            ->filter(fn ($m) => is_array($m) && is_string($m['id'] ?? null))
            ->pluck('id')
            ->intersect(['cod', 'pickup'])
            ->values()
            ->all() ?: ['cod', 'pickup'];

        $rules = [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*' => ['required', 'array'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.productId' => ['nullable', 'string', 'max:100'],
            'items.*.product_id' => ['nullable', 'string', 'max:100'],
            'items.*.slug' => ['nullable', 'string', 'max:100'],
            'items.*.optionCode' => ['nullable', 'string', 'max:20'],
            'items.*.option_code' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^01[0125]\d{8}$/'],
            'delivery_method' => ['required', 'string', 'in:home,pickup'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', $allowedPaymentMethods)],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->input('delivery_method') === 'home') {
            $rules['governorate'] = ['required', 'string', 'max:100'];
            $rules['area'] = ['required', 'string', 'max:100'];
            $rules['street'] = ['required', 'string', 'max:255'];
            $rules['payment_method'][] = 'in:cod';
        } else {
            $rules['governorate'] = ['nullable', 'string', 'max:100'];
            $rules['area'] = ['nullable', 'string', 'max:100'];
            $rules['street'] = ['nullable', 'string', 'max:255'];
            $rules['payment_method'][] = 'in:pickup';
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items');
            if (is_array($items)) {
                foreach ($items as $idx => $item) {
                    if (!is_array($item)) {
                        $validator->errors()->add("items.{$idx}", 'عنصر السلة غير صالح');
                        continue;
                    }

                    if (!$validator->errors()->has("items.{$idx}.productId")
                        && !$validator->errors()->has("items.{$idx}.product_id")
                        && !$validator->errors()->has("items.{$idx}.slug")) {
                        $hasId = (!empty($item['productId']) && is_string($item['productId']))
                            || (!empty($item['product_id']) && is_string($item['product_id']))
                            || (!empty($item['slug']) && is_string($item['slug']));

                        if (!$hasId) {
                            $validator->errors()->add("items.{$idx}", 'معرّف المنتج مطلوب');
                        }
                    }
                }
            }

            $delivery = $this->input('delivery_method');
            $payment = $this->input('payment_method');

            // Validate delivery and payment method alignment only if payment_method is fundamentally valid
            if (!$validator->errors()->has('payment_method')) {
                if ($delivery === 'home' && $payment !== 'cod') {
                    $validator->errors()->add('payment_method', 'طريقة الدفع غير متوافقة مع التوصيل المنزلي (المتاح كاش عند الاستلام فقط)');
                } elseif ($delivery === 'pickup' && $payment !== 'pickup') {
                    $validator->errors()->add('payment_method', 'طريقة الدفع غير متوافقة مع استلام الجراج (المتاح الدفع عند الاستلام في الجراج)');
                }
            }

            // Validate governorate & area against settings
            if ($delivery === 'home') {
                $govName = $this->input('governorate');
                $areaName = $this->input('area');

                if ($govName) {
                    $govs = Setting::get('governorates', []);
                    $validGov = collect(is_array($govs) ? $govs : [])
                        ->filter(fn ($g) => is_array($g))
                        ->firstWhere('name', $govName);
                    if (!$validGov) {
                        $validator->errors()->add('governorate', 'المحافظة المختارة غير متاحة للتوصيل');
                    } elseif ($areaName) {
                        $areas = is_array($validGov['areas'] ?? null) ? $validGov['areas'] : [];
                        if (!in_array($areaName, $areas, true)) {
                            $validator->errors()->add('area', 'المنطقة المختارة غير متاحة للتوصيل في هذه المحافظة');
                        }
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'سلة المشتريات فارغة',
            'items.min' => 'سلة المشتريات فارغة',
            'items.max' => 'الحد الأقصى لعدد عناصر السلة هو 50',
            'items.*.array' => 'عنصر السلة غير صالح',
            'items.*.qty.required' => 'الكمية مطلوبة',
            'items.*.qty.integer' => 'الكمية غير صالحة',
            'items.*.qty.min' => 'أقل كمية مطلوبة هي 1',
            'items.*.qty.max' => 'أقصى كمية مسموح بها هي 99',
            'items.*.productId.string' => 'معرّف المنتج غير صالح',
            'items.*.product_id.string' => 'معرّف المنتج غير صالح',
            'items.*.slug.string' => 'معرّف المنتج غير صالح',
            'items.*.optionCode.string' => 'رمز الخيار غير صالح',
            'items.*.option_code.string' => 'رمز الخيار غير صالح',
            'name.required' => 'الاسم بالكامل مطلوب',
            'name.string' => 'الاسم يجب أن يكون نصاً صالحاً',
            'phone.required' => 'رقم الموبايل مطلوب',
            'phone.regex' => 'رقم الموبايل يجب أن يكون رقماً مصرياً صحيحاً (مثال: 01012345678)',
            'delivery_method.required' => 'طريقة التوصيل مطلوبة',
            'delivery_method.in' => 'طريقة التوصيل غير صالحة',
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.in' => 'طريقة الدفع غير صالحة، المتاح حالياً هو الدفع عند الاستلام فقط',
            'governorate.required' => 'المحافظة مطلوبة للتوصيل المنزلي',
            'area.required' => 'المنطقة مطلوبة للتوصيل المنزلي',
            'street.required' => 'العنوان بالتفصيل مطلوب للتوصيل المنزلي',
        ];
    }
}
