<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
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
        'booking_capacity_per_slot' => 'سعة ميعاد الحجز الواحد (موتوسيكلات)',
        'hero_kit_price' => 'سعر باقة Hero Kit (ج.م)',
        'hero_kit_discount' => 'نسبة خصم باقة الصيانة',
        'total_inspected_bikes' => 'إجمالي الموتوسيكلات المفحوصة',
        'payment_methods' => 'طرق الدفع المفعلة',
        'booking_slots' => 'أوقات العمل المتاحة للحجز',
        'pickup_times' => 'مواعيد الاستلام من الجراج',
        'governorates' => 'المحافظات ومناطق الشحن',
        'models' => 'موديلات زونتس المدعومة',
        'years' => 'سنوات الصنع المتاحة',
        'trust_items' => 'بنود الثقة والضمان',
        'service_features' => 'مميزات الصيانة بالجراج',
        'default_installment_monthly' => 'القسط الشهري الافتراضي',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('اسم الإعداد')
                    ->state(fn ($record) => self::$labels[$record->key] ?? $record->key)
                    ->weight('bold')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('key', 'like', "%{$search}%");
                    }),
                TextColumn::make('key')
                    ->label('المفتاح (Key)')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('display_value')
                    ->label('القيمة الحالية')
                    ->state(function ($record) {
                        $v = $record->value;
                        if (is_array($v)) {
                            return 'مصفوفة بيانات (' . count($v) . ' عنصر)';
                        }
                        if (is_bool($v)) {
                            return $v ? 'نعم' : 'لا';
                        }
                        return (string) $v;
                    })
                    ->limit(60),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
