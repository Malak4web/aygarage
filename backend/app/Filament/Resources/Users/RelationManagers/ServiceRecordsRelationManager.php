<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\UserBike;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceRecords';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'سجل الصيانة والمتابعة الفنية';
    }

    public static function getModelLabel(): string
    {
        return 'عملية صيانة';
    }

    public static function getPluralModelLabel(): string
    {
        return 'سجل الصيانات';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_bike_id')
                    ->label('الموتوسيكل')
                    ->options(fn ($livewire) => $livewire->ownerRecord->bikes->mapWithKeys(fn ($b) => [$b->id => ($b->plate ? $b->plate . ' (' . $b->bike_model_code . ')' : $b->bike_model_code)]))
                    ->required(),
                DatePicker::make('date')
                    ->label('تاريخ الصيانة')
                    ->default(now()->toDateString())
                    ->required()
                    ->native(false),
                TextInput::make('odometer')
                    ->label('العداد (كم)')
                    ->numeric()
                    ->required(),
                TextInput::make('work')
                    ->label('العمل المنجز / قطع الغيار')
                    ->required()
                    ->maxLength(255),
                TextInput::make('technician')
                    ->label('الفني المسئول')
                    ->default('أبو يوسف')
                    ->required(),
                TextInput::make('cost')
                    ->label('التكلفة الإجمالية (ج.م)')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('work')
            ->columns([
                TextColumn::make('date')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('userBike.plate')
                    ->label('اللوحة / الموديل')
                    ->default(fn ($record) => $record->userBike?->bike_model_code),
                TextColumn::make('work')
                    ->label('العمل المنجز')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('odometer')
                    ->label('العداد')
                    ->numeric()
                    ->suffix(' كم')
                    ->sortable(),
                TextColumn::make('technician')
                    ->label('الفني'),
                TextColumn::make('cost')
                    ->label('التكلفة')
                    ->money('EGP', divideBy: 0, decimalPlaces: 0)
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة عملية صيانة يدوياً')
                    ->after(function ($record) {
                        if ($record->userBike) {
                            $record->userBike->update([
                                'odometer' => max($record->userBike->odometer ?? 0, (int) $record->odometer),
                                'last_visit_at' => $record->date,
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
