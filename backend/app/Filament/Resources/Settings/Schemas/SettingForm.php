<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SettingForm
{
    protected static array $labels = [
        'phone' => 'رقم هاتف المتجر',
        'whatsapp' => 'رقم الواتساب',
        'store_name' => 'اسم المتجر بالعربي',
        'store_name_en' => 'اسم المتجر بالإنجليزي',
        'tagline' => 'شعار المتجر (Tagline)',
        'announcement' => 'شريط الإعلانات أعلى الموقع',
        'delivery_fee' => 'سعر التوصيل الأساسي (ج.م)',
        'free_delivery_threshold' => 'الحد الأدنى للشحن المجاني (ج.م)',
        'delivery_days_count' => 'عدد أيام التوصيل',
        'delivery_estimate_days' => 'نص مدة التوصيل',
        'return_days' => 'مدة الاسترجاع (بالأيام)',
        'return_policy_note' => 'ملاحظة سياسة الاسترجاع',
        'booking_capacity_per_slot' => 'سعة ميعاد الحجز الواحد (أقصى عدد موتوسيكلات)',
        'hero_kit_price' => 'سعر باقة الصيانة الأساسية Hero Kit (ج.م)',
        'hero_kit_discount' => 'نسبة خصم باقة الصيانة',
        'total_inspected_bikes' => 'إجمالي الموتوسيكلات المفحوصة',
        'payment_methods' => 'طرق الدفع المفعلة (قائمة)',
        'booking_slots' => 'أوقات العمل المتاحة للحجز',
        'pickup_times' => 'مواعيد الاستلام من الجراج',
        'governorates' => 'المحافظات ومناطق الشحن',
        'models' => 'موديلات زونتس المدعومة',
        'years' => 'سنوات الصنع المتاحة',
        'trust_items' => 'بنود الثقة والضمان',
        'service_features' => 'مميزات الصيانة بالجراج',
        'default_installment_monthly' => 'القسط الشهري الافتراضي',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('مفتاح الإعداد (Key)')
                    ->disabled(fn (string $context) => $context === 'edit')
                    ->required()
                    ->helperText(fn ($record) => $record ? (self::$labels[$record->key] ?? 'إعداد عام') : 'أدخل مفتاح الإعداد بالإنجليزية'),
                Textarea::make('value')
                    ->label('القيمة')
                    ->rows(8)
                    ->required()
                    ->helperText('يمكن كتابة نص عادي، رقم، أو مصفوفة JSON للقيم المعقدة (مثل مواعيد الحجز والمحافظات)')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state) || is_object($state)) {
                            return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                        }
                        return (string) $state;
                    })
                    ->dehydrateStateUsing(function ($state) {
                        $decoded = json_decode((string) $state, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            return $decoded;
                        }
                        if (is_numeric($state)) {
                            return str_contains($state, '.') ? (float) $state : (int) $state;
                        }
                        if ($state === 'true') return true;
                        if ($state === 'false') return false;
                        return $state;
                    })
                    ->columnSpanFull(),
            ]);
    }
}
