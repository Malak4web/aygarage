<?php

namespace App\Filament\Resources\UsedBikes\Pages;

use App\Filament\Resources\UsedBikes\UsedBikeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsedBike extends EditRecord
{
    protected static string $resource = UsedBikeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
