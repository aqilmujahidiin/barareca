<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataCustomerResource\Pages;
use App\Models\DataCustomer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class DataCustomerResource extends Resource
{
    protected static ?string $model = DataCustomer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    // protected static ?string $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Data Customer';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 0 ? 'success' : 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Data Customer')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Utama')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('tanggal')
                                            ->required()
                                            ->label('Tanggal Order')
                                            ->default(now()),

                                        Forms\Components\TextInput::make('no_invoice')
                                            ->label('Nomor Invoice')
                                            ->placeholder('AUTO')
                                            ->disabled()
                                            ->dehydrated(),

                                        Forms\Components\Select::make('status_granular')
                                            ->options([
                                                'pending' => 'Pending',
                                                'shipped' => 'Shipped'
                                            ])
                                            ->required()
                                            ->label('Status')
                                            ->default('pending'),

                                        Forms\Components\Select::make('metode_pembayaran')
                                            ->options([
                                                'cod' => 'Cash on Delivery',
                                                'transfer' => 'Bank Transfer'
                                            ])
                                            ->required()
                                            ->label('Metode Pembayaran')
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Data Pelanggan')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_pelanggan')
                                            ->required()
                                            ->label('Nama Pelanggan')
                                            ->placeholder('Masukkan nama pelanggan'),

                                        Forms\Components\TextInput::make('no_telepon')
                                            ->label('No. Telepon')
                                            ->tel()
                                            ->placeholder('Contoh: 08123456789'),

                                        Forms\Components\Textarea::make('alamat_penerima')
                                            ->required()
                                            ->label('Alamat Pengiriman')
                                            ->rows(3),

                                        Forms\Components\Textarea::make('alamat_penerima_2')
                                            ->label('Alamat Pengiriman (Tambahan)')
                                            ->rows(2),

                                        Forms\Components\TextInput::make('kode_pos')
                                            ->label('Kode Pos')
                                            ->numeric(),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Informasi Produk & Pengiriman')
                            ->schema([
                                Forms\Components\Section::make('Informasi Produk')
                                    ->schema([
                                        Forms\Components\TextInput::make('nama_produk')
                                            ->required()
                                            ->label('Nama Produk'),

                                        Forms\Components\TextInput::make('quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),

                                        Forms\Components\TextInput::make('total_pembayaran')
                                            ->required()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Total Pembayaran'),
                                    ])
                                    ->columns(3),

                                Forms\Components\Section::make('Informasi Pengiriman')
                                    ->schema([
                                        Forms\Components\TextInput::make('id_pelacakan')
                                            ->label('ID Pelacakan'),

                                        Forms\Components\TextInput::make('nama_pengirim')
                                            ->required()
                                            ->label('Nama Pengirim'),

                                        Forms\Components\TextInput::make('kontak_pengirim')
                                            ->label('Kontak Pengirim'),

                                        Forms\Components\TextInput::make('ongkos_kirim')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->label('Ongkos Kirim'),
                                    ])
                                    ->columns(2),
                            ]),

                        Forms\Components\Tabs\Tab::make('Informasi Tambahan')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Select::make('customer_service')
                                            ->relationship('operator', 'name')
                                            ->required()
                                            ->label('Customer Service'),

                                        Forms\Components\TextInput::make('advertiser')
                                            ->required()
                                            ->label('Advertiser'),

                                        Forms\Components\TextInput::make('company')
                                            ->required(),

                                        Forms\Components\TextInput::make('divisi')
                                            ->required(),

                                        Forms\Components\Textarea::make('keterangan_promo')
                                            ->label('Keterangan Promo')
                                            ->rows(2),

                                        Forms\Components\Textarea::make('keterangan_issue')
                                            ->label('Keterangan Issue')
                                            ->rows(2),
                                    ])
                                    ->columns(2),

                                Forms\Components\Section::make('Potongan')
                                    ->schema([
                                        Forms\Components\TextInput::make('potongan_ongkos_kirim')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0),

                                        Forms\Components\TextInput::make('potongan_lain_1')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0),

                                        Forms\Components\TextInput::make('potongan_lain_2')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0),

                                        Forms\Components\TextInput::make('potongan_lain_3')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->persistTabInQueryString()
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // Tables\Columns\TextColumn::make('no_invoice')
                //     ->searchable()
                //     ->sortable()
                //     ->copyable()
                //     ->label('Invoice'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('nama_produk')
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('total_pembayaran')
                    ->money('idr')
                    ->sortable(),

                Tables\Columns\IconColumn::make('status_granular')
                    ->icon(fn(string $state): string => match ($state) {
                        'shipped' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-clock',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'shipped' => 'success',
                        default => 'warning',
                    })
                    ->label('Status'),

                Tables\Columns\TextColumn::make('metode_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'cod' => 'warning',
                        'transfer' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('operator.name')
                    ->label('CS')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status_granular')
                    ->options([
                        'pending' => 'Pending',
                        'shipped' => 'Shipped',
                    ]),

                SelectFilter::make('metode_pembayaran')
                    ->options([
                        'cod' => 'COD',
                        'transfer' => 'Transfer',
                    ]),

                SelectFilter::make('operator')
                    ->relationship('operator', 'name')
                    ->label('Customer Service'),

                Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-truck')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['status_granular' => 'shipped']);
                        })
                        ->deselectRecordsAfterCompletion()
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->striped()
            // ->defaultGroup('tanggal')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistColumnSearchesInSession();
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
            'index' => Pages\ListDataCustomers::route('/'),
            'create' => Pages\CreateDataCustomer::route('/create'),
            'edit' => Pages\EditDataCustomer::route('/{record}/edit'),
            // 'view' => Pages\ViewDataCustomer::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['operator']);
    }
}