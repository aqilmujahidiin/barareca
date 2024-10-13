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
                    if (empty($state)) {
                        return null;
                    }

                    return self::FormatTanggal($state);
                }),

            ImportColumn::make('no_telepon')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('nama_pelanggan')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('nama_produk')
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

            ImportColumn::make('cash_on_delivery')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),

            ImportColumn::make('transfer')
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

            ImportColumn::make('customer_service')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('advertiser')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('inp')
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

            ImportColumn::make('status_customer')
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

            ImportColumn::make('company')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('divisi')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    // Tambahkan metode ini ke dalam class Anda
    public static function FormatTanggal($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim($value);

        try {
            // Coba format "21 August 2024"
            if (preg_match('/^\d{1,2}\s+[A-Za-z]+\s+\d{4}$/', $value)) {
                return Carbon::createFromFormat('d F Y', $value)->format('Y-m-d');
            }

            // Coba format "2024-10-13"
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
            }

            // Jika tidak cocok dengan format di atas, gunakan Carbon::parse
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            \Log::warning("Gagal parsing tanggal: $value", ['exception' => $e->getMessage()]);
            return null;
        }
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
