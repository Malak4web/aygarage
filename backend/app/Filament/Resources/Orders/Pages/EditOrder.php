<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Save the order and its stock change (cancel / re-activate) in one
     * transaction, with the order row locked so two admins can't both
     * apply the same status change.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return DB::transaction(function () use ($record, $data) {
                $fresh = Order::whereKey($record->getKey())->lockForUpdate()->firstOrFail();
                $record->setRawAttributes($fresh->getAttributes(), true);

                return parent::handleRecordUpdate($record, $data);
            });
        } catch (ValidationException $e) {
            // The model reports stock problems under "status"; show them on the form field.
            $messages = [];
            foreach ($e->errors() as $key => $errors) {
                $messages[str_starts_with($key, 'data.') ? $key : "data.{$key}"] = $errors;
            }

            throw ValidationException::withMessages($messages);
        }
    }
}
