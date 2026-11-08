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
                    ->rules([
                        function () {
                            return function (string $attribute, $value, \Closure $fail) {
                                $normalized = Phone::normalize((string) $value);
                                if (!preg_match('/^01[0125]\d{8}$/', $normalized)) {
                                    $fail('رقم الهاتف يجب أن يكون رقم مصري صحيح مكون من 11 رقم يبدأ بـ 01');
                                }
                            };
                        },
                    ])
                    ->unique(table: 'users', column: 'phone', ignoreRecord: true),
                TextInput::make('email')
                    ->label('البريد الإلكتروني (اختياري)')
                    ->email()
                    ->maxLength(255),
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
