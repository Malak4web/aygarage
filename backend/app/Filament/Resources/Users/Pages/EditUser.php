<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn ($record) => $record->id === auth()->id() || ($record->role === 'admin' && User::where('role', 'admin')->count() <= 1)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $role = $data['role'] ?? $record->role;
        unset($data['role']);

        $record->update($data);

        // Do not demote yourself or the last admin
        if ($record->id !== auth()->id() && !(User::where('role', 'admin')->count() <= 1 && $record->role === 'admin' && $role !== 'admin')) {
            $record->forceFill(['role' => $role])->save();
        }

        return $record;
    }
}
