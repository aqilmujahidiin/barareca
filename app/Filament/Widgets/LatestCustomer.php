<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\DataCustomerResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\DateFilter;
use Illuminate\Database\Eloquent\Builder;

class LatestCustomer extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Data Customer Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DataCustomerResource::getEloquentQuery()
                    ->latest('tanggal')
                    ->latest('created_at')
            )
            ->columns([
                TextColumn::make('No')
                    ->rowIndex()
                    ->color('gray'),

                TextColumn::make('tanggal')
                    ->date('d F Y')
                    ->sortable()
                    ->searchable()
                    ->color('primary'),

                TextColumn::make('no_invoice')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('No Invoice disalin')
                    ->icon('heroicon-m-document-text'),

                TextColumn::make('operator.name')
                    ->label('Operator')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-m-user')
                    ->color('success'),

                TextColumn::make('nama_pelanggan')
                    ->searchable()
                    ->icon('heroicon-m-user-circle'),


                TextColumn::make('nama_produk')
                    ->sortable()
                    ->searchable()
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('status_sustomer')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'New' => 'success',
                        'Repeat Order' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('metode_pembayaran')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('total_pembayaran')
                    ->label('Total Pembayaran')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                // ->summarize([
                //     'sum' => [
                //         'label' => 'Total',
                //         'money' => 'IDR',
                //     ],
                // ]),

                TextColumn::make('customer_service')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('divisi')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('advertiser')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('company')
                    ->label('Company')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ongkos_kirim')
                    ->money('IDR')
                    ->toggleable(),

                TextColumn::make('potongan_ongkos_kirim')
                    ->money('IDR')
                    ->toggleable(),

                TextColumn::make('potongan_lain_1')
                    ->money('IDR')
                    ->toggleable()
                    ->visible(fn($state) => $state > 0),

                TextColumn::make('potongan_lain_2')
                    ->money('IDR')
                    ->toggleable()
                    ->visible(fn($state) => $state > 0),

                TextColumn::make('potongan_lain_3')
                    ->money('IDR')
                    ->toggleable()
                    ->visible(fn($state) => $state > 0),

                TextColumn::make('keterangan_promo')
                    ->wrap()
                    ->toggleable()
                    ->visible(fn($state) => !empty ($state)),
            ])
            ->defaultSort('tanggal', 'desc')
            // ->filters([
            //     SelectFilter::make('divisi')
            //         ->options([
            //             'facebook' => 'Facebook',
            //             'tiktok' => 'Tiktok',
            //             'marketplace' => 'Marketplace',
            //             'crm' => 'CRM',
            //         ]),
            //     SelectFilter::make('status_sustomer')
            //         ->options([
            //             'New' => 'New',
            //             'Repeat Order' => 'Repeat Order',
            //         ]),
            //     DateFilter::make('tanggal'),
            // ])
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersTriggerAction(
                fn($action) => $action
                    ->button()
                    ->label('Filter')
            )
            ->groups([
                'divisi',
                'status_sustomer',
                'tanggal',
            ])
            ->striped()
            ->paginated([10, 25, 50, 100]);
        // ->poll('30s')
        // ->emptyStateHeading('Tidak ada data customer')
        // ->emptyStateDescription('Data customer akan muncul di sini ketika tersedia.')
        // ->emptyStateIcon('heroicon-o-users')
        // ->defaultGroup('tanggal')
        // ->persistFiltersInSession()
        // ->persistSortInSession()
        // ->persistSearchInSession()
        // ->persistColumnSearchesInSession()
        // ->persistGroupsInSession()
        // ->searchable(['nama_pelanggan', 'no_invoice', 'nama_produk'])
        // ->deferLoading();
    }
}