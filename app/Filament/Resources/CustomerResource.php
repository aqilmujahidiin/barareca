<?php

namespace App\Filament\Resources;

use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('customerTabs')
                    ->tabs([
                        // tab 1
                        Forms\Components\Tabs\Tab::make('Customer Detail')
                            ->schema([
                                DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->required()
                                    ->displayFormat('d/m/Y'),
                                TextInput::make('no_telepon')
                                    ->tel(),
                                TextInput::make('nama_pelanggan'),
                                TextInput::make('alamat_penerima'),
                                TextInput::make('alamat_penerima_2'),
                                TextInput::make('kode_pos'),
                                TextInput::make('inp')
                                    ->label(strtoupper('inp')),
                                TextInput::make('status_granular'),
                                TextInput::make('status_customer'),
                            ])->columns(2),
                        // tab 2
                        Forms\Components\Tabs\Tab::make('Alamat Customer')
                            ->schema([
                                TextInput::make('kode_pos_pengirim'),
                                TextInput::make('alamat_penerima'),
                                TextInput::make('kode_pos'),
                            ])->columns(2),
                        // tab 3
                        Forms\Components\Tabs\Tab::make('Produk')
                            ->schema([
                                TextInput::make('nama_produk')
                                    ->required(),
                                TextInput::make('quantity')
                                    ->required(),
                                TextInput::make('keterangan_promo'),
                                TextInput::make('id_pelacakan'),
                            ])->columns(2),
                        // tab 4
                        Forms\Components\Tabs\Tab::make('Lain-lain')
                            ->schema([
                                TextInput::make('customer_service')
                                    ->required(),
                                TextInput::make('advertiser')
                                    ->required(),
                                TextInput::make('divisi')
                                    ->required(),
                                TextInput::make('company')
                                    ->required(),
                            ])->columns(2),
                        // tab 5
                        Forms\Components\Tabs\Tab::make('Pembayaran')
                            ->schema([
                                TextInput::make('no_invoice'),
                                TextInput::make('cash_on_delivery')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 2),
                                TextInput::make('transfer')
                                    ->currencyMask(thousandSeparator: ',', decimalSeparator: '.', precision: 2),
                                TextInput::make('ongkos_kirim')
                                    ->numeric(),
                                TextInput::make('potongan_ongkos_kirim')
                                    ->numeric(),
                            ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Pengirim')
                            ->schema([
                                TextInput::make('nama_pengirim'),
                                TextInput::make('alamat_pengirim'),
                                TextInput::make('kontak_pengirim'),
                                TextInput::make('ongkos_kirim')
                                    ->numeric(),
                                TextInput::make('kode_pos_pengirim'),
                            ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Potongan lain-lain')
                            ->schema([
                                TextInput::make('potongan_lain_1')
                                    ->numeric(),
                                TextInput::make('potongan_lain_2')
                                    ->numeric(),
                                TextInput::make('potongan_lain_3')
                                    ->numeric(),

                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('No')
                    ->rowIndex(),
                TextColumn::make('tanggal'),
                TextColumn::make('no_invoice'),
                TextColumn::make('nama_pelanggan'),
                TextColumn::make('status_customer'),
                TextColumn::make('nama_produk'),
                TextColumn::make('quantity'),
                TextColumn::make('transfer')
                    ->label('Transfer')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('cash_on_delivery')
                    ->label('COD')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customer_service'),
                TextColumn::make('divisi'),
                TextColumn::make('advertiser'),
                TextColumn::make('company'),
                TextColumn::make('ongkos_kirim')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_ongkos_kirim')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_lain_1')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_lain_2')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('potongan_lain_3')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.')),
                TextColumn::make('keterangan_promo'),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_pelanggan', 'no_telepon', 'nama_produk', 'no_invoice'];
    }
}
