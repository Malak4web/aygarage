<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Support\Phone;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم العميل')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required()
                    ->dehydrateStateUsing(fn ($state) => Phone::normalize((string) $state))
                    // Validate the normalized number, so "+20 10…" and "010…" count as the same phone.
                    ->rules([
                        fn (?User $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                            $normalized = Phone::normalize((string) $value);
                            if (! preg_match('/^01[0125]\d{8}$/', $normalized)) {
                                $fail('رقم الهاتف يجب أن يكون رقم مصري صحيح مكون من 11 رقم يبدأ بـ 01');

                                return;
                            }
                            $taken = User::where('phone', $normalized)
                                ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                ->exists();
                            if ($taken) {
                                $fail('الرقم ده مسجل لعميل تاني');
                            }
                        },
                    ]),
                TextInput::make('email')
                    ->label('البريد الإلكتروني (اختياري)')
                    ->email()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? strtolower(trim($state)) : null)
                    ->rules([
                        fn (?User $record) => function (string $attribute, $value, \Closure $fail) use ($record) {
                            if (blank($value)) {
                                return;
                            }
                            $taken = User::whereRaw('LOWER(email) = ?', [strtolower(trim((string) $value))])
                                ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                ->exists();
                            if ($taken) {
                                $fail('الإيميل ده مسجل لعميل تاني');
                            }
                        },
                    ]),
                Select::make('role')
                    ->label('الصلاحية')
                    ->options([
                        'customer' => 'عميل عادي',
                        'admin' => 'مدير النظام (أبو يوسف)',
                    ])
                    ->default('customer')
                    ->disabled(function ($record) {
                        if (!$record) return false;
                        if ($record->id === auth()->id()) return true;
                        if ($record->role === 'admin' && User::where('role', 'admin')->count() <= 1) return true;
                        return false;
                    })
                    ->helperText(function ($record) {
                        if ($record && $record->id === auth()->id()) {
                            return 'لا يمكنك تغيير صلاحيتك كمدير مسجل الدخول';
                        }
                        if ($record && $record->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
                            return 'لا يمكن تقليل صلاحية آخر مدير في النظام';
                        }
                        return null;
                    })
                    ->required(),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? Hash::make($state) : null)
                    ->dehydrated(fn ($state) => !empty($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->helperText('اتركها فارغة إذا لم تكن تريد تغيير كلمة المرور'),
            ]);
    }
}
