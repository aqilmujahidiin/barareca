<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Marketing;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MarketingResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MarketingResource\RelationManagers;

class MarketingResource extends Resource
{
    protected static ?string $model = Marketing::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->displayFormat('d/m/Y'),
                TextInput::make('budget_iklan')
                    ->numeric(),
                TextInput::make('lead')
                    ->required()
                    ->numeric(),
                TextInput::make('closing')
                    ->required()
                    ->numeric(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('omset')
                    ->required()
                    ->numeric(),
                TextInput::make('target_omset')
                    ->numeric(),
                TextInput::make('produk')
                    ->required()
                    ->maxLength(255),
                TextInput::make('divisi')
                    ->required()
                    ->maxLength(255),
                TextInput::make('company')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                TextColumn::make('budget_iklan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lead')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('closing')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('omset')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_omset')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('produk')
                    ->searchable(),
                TextColumn::make('divisi')
                    ->searchable(),
                TextColumn::make('company')
                    ->searchable(),
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
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketings::route('/'),
            'create' => Pages\CreateMarketing::route('/create'),
            'edit' => Pages\EditMarketing::route('/{record}/edit'),
        ];
    }
}
