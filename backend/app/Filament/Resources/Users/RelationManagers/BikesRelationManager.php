<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Models\BikeModel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BikesRelationManager extends RelationManager
{
    protected static string $relationship = 'bikes';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'موتوسيكلات العميل';
    }

    public static function getModelLabel(): string
    {
        return 'موتوسيكل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'موتوسيكلات';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bike_model_code')
                    ->label('موديل الموتوسيكل')
                    ->options(fn () => BikeModel::pluck('name', 'code')->toArray())
                    ->required(),
                TextInput::make('model_name')
                    ->label('اسم الموديل المخصص')
                    ->placeholder('اختياري إذا اختلف عن الموديل الأساسي'),
                TextInput::make('year')
                    ->label('سنة الصنع (الموديل)')
                    ->numeric(),
                TextInput::make('plate')
                    ->label('رقم اللوحة')
                    ->placeholder('مثال: أ ب ج 1234')
                    ->maxLength(50),
                TextInput::make('odometer')
                    ->label('العداد الحالي (كم)')
                    ->numeric()
                    ->default(0),
                TextInput::make('next_service_km')
                    ->label('ميعاد الصيانة القادمة (كم)')
                    ->numeric(),
                TextInput::make('license_until')
                    ->label('سريان الرخصة')
                    ->placeholder('مثال: 2026-12-01'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('plate')
            ->columns([
                TextColumn::make('bikeModel.name')
                    ->label('الموديل')
                    ->badge()
                    ->color('info'),
                TextColumn::make('plate')
                    ->label('اللوحة')
                    ->searchable()
                    ->default('بدون لوحة'),
                TextColumn::make('year')
                    ->label('سنة الصنع'),
                TextColumn::make('odometer')
                    ->label('العداد')
                    ->numeric()
                    ->suffix(' كم')
                    ->sortable(),
                TextColumn::make('next_service_km')
                    ->label('الصيانة القادمة')
                    ->numeric()
                    ->suffix(' كم'),
                TextColumn::make('last_visit_at')
                    ->label('آخر زيارة')
                    ->date('Y-m-d')
                    ->placeholder('لا يوجد'),
            ])
            ->headerActions([
                CreateAction::make()->label('إضافة موتوسيكل للعميل'),
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
