<?php

namespace App\Imports\Templates;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class DataCustomerTemplate implements ToCollection, WithHeadingRow, WithEvents, ShouldAutoSize, WithStyles
{
    public function collection(Collection $rows)
    {
        // Template kosong, hanya header
        return new Collection();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Pelanggan',
            'No Telepon',
            'Nama Produk',
            'Quantity',
            'Alamat Pengirim',
            'Id Pelacakan',
            'Status Granular',
            'Nama Pengirim',
            'Kontak Pengirim',
            'Kode Pos Pengirim',
            'Metode Pembayaran',
            'Total Pembayaran',
            'Alamat Penerima',
            'Alamat Penerima 2',
            'Kode Pos',
            'No Invoice',
            'Keterangan Promo',
            'Keterangan Issue',
            'Ongkos Kirim',
            'Potongan Ongkos Kirim',
            'Potongan Lain 1',
            'Potongan Lain 2',
            'Potongan Lain 3',
            'Customer Service',
            'Advertiser',
            'Status Customer',
            'Company',
            'Divisi',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ]
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet->getDelegate()->freezePane('A2');
            },
            AfterSheet::class => function (AfterSheet $event) {
                // Auto width
                foreach (range('A', 'Z') as $column) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                }

                // Set validasi untuk beberapa kolom
                $lastRow = $event->sheet->getDelegate()->getHighestRow();

                // Status Granular validation (pending/shipped)
                $event->sheet->getDelegate()->getCell('H2')->getDataValidation()
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(true)
                    ->setShowDropDown(true)
                    ->setFormula1('"pending,shipped"');

                // Metode Pembayaran validation (cod/transfer)
                $event->sheet->getDelegate()->getCell('L2')->getDataValidation()
                    ->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST)
                    ->setAllowBlank(false)
                    ->setShowDropDown(true)
                    ->setFormula1('"cod,transfer"');
            }
        ];
    }
}