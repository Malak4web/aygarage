<?php

namespace App\Filament\Resources\Users\Schemas;

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
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
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
