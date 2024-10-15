<?php

namespace App\Filament\Imports;

use Carbon\Carbon;
use App\Models\Marketing;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;

class MarketingImporter extends Importer
{
    protected static ?string $model = Marketing::class;

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
            ImportColumn::make('budget_iklan')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('lead')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('closing')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('quantity')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('omset')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),
            ImportColumn::make('target_omset')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0', 'max:99999999.99'])
                ->castStateUsing(function ($state) {
                    if (blank($state))
                        return 0;
                    $state = preg_replace('/[^0-9.]/', '', $state);
                    return round(floatval($state), 2);
                }),
            ImportColumn::make('produk')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('divisi')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('company')->requiredMapping()->rules(['required', 'max:255']),
        ];
    }
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
    public function resolveRecord(): ?Marketing
    {
        // return Marketing::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Marketing();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your marketing import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
