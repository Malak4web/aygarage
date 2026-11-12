<?php

namespace App\Filament\Pages\Auth;

use App\Support\Phone;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

class Login extends BaseLogin
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('رقم الهاتف أو البريد الإلكتروني')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.login' => 'رقم الهاتف أو كلمة المرور غير صحيحة',
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim((string) ($data['login'] ?? ''));
        $password = $data['password'] ?? '';

        if (str_contains($login, '@')) {
            return [
                'email' => $login,
                'password' => $password,
            ];
        }

        return [
            'phone' => Phone::normalize($login),
            'password' => $password,
        ];
    }
}
