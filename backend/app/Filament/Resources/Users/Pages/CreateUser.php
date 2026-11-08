<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $role = $data['role'] ?? 'customer';
        unset($data['role']);

        $user = static::getModel()::create($data);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
