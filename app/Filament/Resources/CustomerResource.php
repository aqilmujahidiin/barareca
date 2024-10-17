<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\CustomerResource\Pages;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

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
                                Select::make('operator_id')
                                    ->relationship('operator', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('email')
                                            ->email(),
                                    ]),

                                TextInput::make('status_granular'),
                                Select::make('status_customer_id')
                                    ->label('Status Customer')
                                    ->relationship('statuscustomer', 'name')
                                    ->nullable()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->nullable()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('description')
                                            ->nullable()
                                            ->maxLength(255),
                                    ]),
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
                                Select::make('product_id')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->required()
                                    ->preload(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('keterangan_promo'),
                                TextInput::make('id_pelacakan'),
                            ])->columns(2),
                        // tab 4
                        Forms\Components\Tabs\Tab::make('Lain-lain')
                            ->schema([
                                Select::make('customer_service_id')
                                    ->relationship('CustomerService', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('description')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Select::make('advertiser_id')
                                    ->relationship('advertiser', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('email')
                                            ->required()
                                            ->email(),
                                    ]),

                                Select::make('divisi_id')
                                    ->relationship('divisi', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                                Select::make('company_id')
                                    ->relationship('company', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable(),
                            ])->columns(2),

                        // tab 5
                        Forms\Components\Tabs\Tab::make('Pembayaran')
                            ->schema([
                                TextInput::make('no_invoice'),
                                Select::make('metode_pembayaran')
                                    ->options([
                                        'transfer' => 'Transfer',
                                        'cod' => 'Cash on Delivery',
                                    ])
                                    ->required(),
                                TextInput::make('total_pembayaran')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('ongkos_kirim')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric(),
                                TextInput::make('potongan_ongkos_kirim')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric(),
                            ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Pengirim')
                            ->schema([
                                TextInput::make('nama_pengirim'),
                                TextInput::make('alamat_pengirim'),
                                TextInput::make('kontak_pengirim'),
                                TextInput::make('ongkos_kirim')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric(),
                                TextInput::make('kode_pos_pengirim'),
                            ])->columns(2),
                        Forms\Components\Tabs\Tab::make('Potongan lain-lain')
                            ->schema([
                                TextInput::make('potongan_lain_1')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric(),
                                TextInput::make('potongan_lain_2')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric(),
                                TextInput::make('potongan_lain_3')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
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
                TextColumn::make('tanggal')->date('d F Y')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('no_invoice'),
                TextColumn::make('operator.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama_pelanggan'),
                TextColumn::make('product.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('statusCustomer.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('quantity'),
                TextColumn::make('metode_pembayaran')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_pembayaran')
                    ->label('Total Pembayaran')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('customerService.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('divisi.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('advertiser.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),
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
                DateRangeFilter::make('tanggal')

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
        return ['nama_pelanggan', 'no_telepon', 'no_invoice'];
    }
}
