<?php

namespace App\Filament\Resources\CashRegisters\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransfersRelationManager extends RelationManager
{
    protected static string $relationship = 'transfers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->label('Referencia')
                    ->maxLength(255),
                TextInput::make('amount')
                    ->label('Monto')
                    ->prefix('$')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'rejected' => 'Rechazada',
                    ])
                    ->default('confirmed')
                    ->required(),
                Textarea::make('notes')
                    ->label('Notas')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('reservation.id')
                    ->searchable(),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                ImageColumn::make('receipt_image_path'),
                TextColumn::make('confirmed_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('confirmed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
