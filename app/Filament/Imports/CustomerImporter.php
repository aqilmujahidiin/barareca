<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('tanggal')
                ->requiredMapping()
                ->rules(['required', 'date'])
                ->castStateUsing(function ($state) {
                    $state = Carbon::parse($state)->format('Y-m-d');
                    return $state;
                }),

            ImportColumn::make('no_telepon')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('nama_pelanggan')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('product_id')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('quantity')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),

            ImportColumn::make('alamat_pengirim')
                ->rules(['nullable', 'max:400']),

            ImportColumn::make('id_pelacakan')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('status_granular')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('nama_pengirim')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('kontak_pengirim')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('kode_pos_pengirim')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('metode_pembayaran')
                ->rules(['required', 'string', 'in:cod,transfer'])
                ->requiredMapping()
                ->example('cod atau transfer'),
            ImportColumn::make('total_pembayaran')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('alamat_penerima')
                ->requiredMapping()
                ->rules(['nullable', 'max:400']),

            ImportColumn::make('customer_service_id')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('advertiser_id')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('operator_id')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('ongkos_kirim')
                ->requiredMapping()
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('potongan_ongkos_kirim')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('potongan_lain_1')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('potongan_lain_2')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('potongan_lain_3')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('status_customer_id')
                ->requiredMapping()
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('alamat_penerima_2')
                ->rules(['nullable', 'max:400']),

            ImportColumn::make('kode_pos')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('no_invoice')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('keterangan_promo')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('company_id')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('divisi_id')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }
    public function resolveRecord(): ?Customer
    {
        // return Customer::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Customer();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your customer import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
