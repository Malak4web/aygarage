<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $title = 'إعدادات المتجر والجراج';

    protected static ?string $navigationLabel = 'الإعدادات العامة';

    protected static ?string $slug = 'settings';

    protected string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Ensure array structures for repeaters
        if (isset($settings['booking_slots']) && is_string($settings['booking_slots'])) {
            $settings['booking_slots'] = json_decode($settings['booking_slots'], true) ?: [];
        }
        if (isset($settings['governorates']) && is_string($settings['governorates'])) {
            $settings['governorates'] = json_decode($settings['governorates'], true) ?: [];
        }
        if (isset($settings['payment_methods']) && is_string($settings['payment_methods'])) {
            $settings['payment_methods'] = json_decode($settings['payment_methods'], true) ?: [];
        }

        // Phone numbers must be strings to avoid losing leading 0
        if (isset($settings['phone'])) {
            $settings['phone'] = (string) $settings['phone'];
        }
        if (isset($settings['whatsapp'])) {
            $settings['whatsapp'] = (string) $settings['whatsapp'];
        }

        $this->form->fill($settings);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Tabs::make('SettingsTabs')
                    ->tabs([
                        Tab::make('بيانات المتجر')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
                            ->schema([
                                TextInput::make('store_name')
                                    ->label('اسم المتجر (بالعربية)')
                                    ->required(),
                                TextInput::make('store_name_en')
                                    ->label('اسم المتجر (بالإنجليزية)'),
                                TextInput::make('tagline')
                                    ->label('شعار المتجر (Tagline)'),
                                TextInput::make('phone')
                                    ->label('رقم هاتف المتجر')
                                    ->tel()
                                    ->required()
                                    ->helperText('يُحفظ كنص ولا يفقد الصفر في البداية'),
                                TextInput::make('whatsapp')
                                    ->label('رقم الواتساب')
                                    ->tel()
                                    ->required()
                                    ->helperText('يُحفظ كنص ولا يفقد الصفر في البداية'),
                                TextInput::make('announcement')
                                    ->label('شريط الإعلانات أعلى الموقع')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('الشحن والتوصيل')
                            ->icon(Heroicon::OutlinedTruck)
                            ->schema([
                                TextInput::make('delivery_fee')
                                    ->label('سعر التوصيل الأساسي (ج.م)')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('free_delivery_threshold')
                                    ->label('الحد الأدنى للشحن المجاني (ج.م)')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('delivery_days_count')
                                    ->label('عدد أيام التوصيل (رقم)')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('delivery_estimate_days')
                                    ->label('نص مدة التوصيل التقديرية')
                                    ->placeholder('مثال: 2-3 أيام عمل'),
                            ])
                            ->columns(2),

                        Tab::make('حجوزات الصيانة')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->schema([
                                TextInput::make('booking_capacity_per_slot')
                                    ->label('سعة ميعاد الحجز الواحد (عدد الموتوسيكلات)')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('booking_days_ahead')
                                    ->label('الحد الأقصى لأيام الحجز المسبق')
                                    ->numeric()
                                    ->default(30)
                                    ->required(),
                                Repeater::make('booking_slots')
                                    ->label('مواعيد العمل اليومية المتاحة للحجز')
                                    ->schema([
                                        TextInput::make('time')
                                            ->label('التوقيت (24h مثل 13:00)')
                                            ->required(),
                                        TextInput::make('label')
                                            ->label('الاسم الظاهر (مثل 1:00 م)')
                                            ->required(),
                                        Toggle::make('off')
                                            ->label('مغلق / استراحة')
                                            ->default(false),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        Tab::make('المحافظات ومناطق الشحن')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->schema([
                                Repeater::make('governorates')
                                    ->label('المحافظات والمناطق التابعة')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('اسم المحافظة')
                                            ->required(),
                                        TagsInput::make('areas')
                                            ->label('المناطق المتاحة للتوصيل')
                                            ->placeholder('اكتب اسم المنطقة واضغط Enter')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('طرق الدفع')
                            ->icon(Heroicon::OutlinedCreditCard)
                            ->schema([
                                Repeater::make('payment_methods')
                                    ->label('طرق الدفع المتاحة في المتجر')
                                    ->schema([
                                        TextInput::make('id')
                                            ->label('رمز الطريقة (مثل cod, pickup)')
                                            ->required(),
                                        TextInput::make('title')
                                            ->label('اسم الطريقة الظاهر')
                                            ->required(),
                                        TextInput::make('desc')
                                            ->label('الوصف التوضيحي للعميل')
                                            ->required(),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('إعدادات أخرى')
                            ->icon(Heroicon::OutlinedSparkles)
                            ->schema([
                                TextInput::make('return_days')
                                    ->label('فترة الاسترجاع (أيام)')
                                    ->numeric(),
                                TextInput::make('return_policy_note')
                                    ->label('ملاحظة سياسة الاسترجاع'),
                                TextInput::make('hero_kit_price')
                                    ->label('سعر باقة Hero Kit للصيانة (ج.م)')
                                    ->numeric(),
                                TextInput::make('hero_kit_discount')
                                    ->label('نسبة خصم باقة الصيانة'),
                                TextInput::make('total_inspected_bikes')
                                    ->label('عدد الموتوسيكلات المفحوصة (عداد الثقة)')
                                    ->numeric(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ جميع الإعدادات')
                ->action('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Type each field correctly before saving
        foreach ($data as $key => $val) {
            // Strings: phone, whatsapp must remain pure strings
            if (in_array($key, ['phone', 'whatsapp', 'store_name', 'store_name_en', 'tagline', 'announcement', 'delivery_estimate_days', 'return_policy_note', 'hero_kit_discount'])) {
                $typedVal = is_null($val) ? null : (string) $val;
            } elseif (in_array($key, ['delivery_fee', 'free_delivery_threshold', 'delivery_days_count', 'booking_capacity_per_slot', 'booking_days_ahead', 'return_days', 'hero_kit_price', 'total_inspected_bikes'])) {
                $typedVal = is_numeric($val) ? (int) $val : 0;
            } elseif (is_array($val)) {
                $typedVal = array_values($val);
            } else {
                $typedVal = $val;
            }

            Setting::set($key, $typedVal);
        }

        Notification::make()
            ->title('تم حفظ وتحديث الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
