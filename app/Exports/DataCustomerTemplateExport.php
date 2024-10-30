<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use Maatwebsite\Excel\Events\AfterSheet;

class DataCustomerTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Pelanggan',
            'No Telepon',
            'Nama Produk',
            'Quantity',
            'Alamat Pengirim',
            'ID Pelacakan',
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
            'Nama Operator'
        ];
    }

    public function array(): array
    {
        // Sample data
        return [
            [
                Carbon::now()->format('d/m/Y'),  // Tanggal
                'John Doe',                      // Nama Pelanggan
                '08123456789',                   // No Telepon
                'Produk A',                      // Nama Produk
                2,                               // Quantity
                'Jl. Contoh No. 123',           // Alamat Pengirim
                'TRK123456',                     // ID Pelacakan
                'pending',                       // Status Granular
                'Jane Doe',                      // Nama Pengirim
                '08987654321',                   // Kontak Pengirim
                '12345',                         // Kode Pos Pengirim
                'transfer',                      // Metode Pembayaran
                150000,                          // Total Pembayaran
                'Jl. Penerima No. 456',         // Alamat Penerima
                'Lantai 2',                      // Alamat Penerima 2
                '54321',                         // Kode Pos
                'INV/2024/001',                 // No Invoice
                'Diskon 10%',                    // Keterangan Promo
                null,                            // Keterangan Issue
                15000,                           // Ongkos Kirim
                5000,                            // Potongan Ongkos Kirim
                0,                               // Potongan Lain 1
                0,                               // Potongan Lain 2
                0,                               // Potongan Lain 3
                'CS001',                         // Customer Service
                'ADV001',                        // Advertiser
                'active',                        // Status Customer
                'Company A',                     // Company
                'Divisi A',                      // Divisi
                'Operator A'                     // Nama Operator
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Tanggal
            'B' => 20,  // Nama Pelanggan
            'C' => 15,  // No Telepon
            'D' => 20,  // Nama Produk
            'E' => 10,  // Quantity
            'F' => 30,  // Alamat Pengirim
            'G' => 15,  // ID Pelacakan
            'H' => 15,  // Status Granular
            'I' => 20,  // Nama Pengirim
            'J' => 15,  // Kontak Pengirim
            'K' => 15,  // Kode Pos Pengirim
            'L' => 15,  // Metode Pembayaran
            'M' => 15,  // Total Pembayaran
            'N' => 30,  // Alamat Penerima
            'O' => 30,  // Alamat Penerima 2
            'P' => 10,  // Kode Pos
            'Q' => 15,  // No Invoice
            'R' => 20,  // Keterangan Promo
            'S' => 20,  // Keterangan Issue
            'T' => 15,  // Ongkos Kirim
            'U' => 20,  // Potongan Ongkos Kirim
            'V' => 15,  // Potongan Lain 1
            'W' => 15,  // Potongan Lain 2
            'X' => 15,  // Potongan Lain 3
            'Y' => 20,  // Customer Service
            'Z' => 20,  // Advertiser
            'AA' => 15, // Status Customer
            'AB' => 20, // Company
            'AC' => 15, // Divisi
            'AD' => 20, // Nama Operator
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
            2 => [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2']
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze first row
                $sheet->freezePane('A2');

                // Add data validation for Status Granular
                $statusValidation = $sheet->getCell('H2')->getDataValidation();
                $statusValidation->setType(DataValidation::TYPE_LIST)
                    ->setAllowBlank(false)
                    ->setShowDropDown(true)
                    ->setFormula1('"pending,shipped"');
                $sheet->setDataValidation('H2:H1000', $statusValidation);

                // Add data validation for Metode Pembayaran
                $paymentValidation = $sheet->getCell('L2')->getDataValidation();
                $paymentValidation->setType(DataValidation::TYPE_LIST)
                    ->setAllowBlank(false)
                    ->setShowDropDown(true)
                    ->setFormula1('"cod,transfer"');
                $sheet->setDataValidation('L2:L1000', $paymentValidation);

                // Add number format for currency columns
                $currencyColumns = ['M', 'T', 'U', 'V', 'W', 'X'];
                foreach ($currencyColumns as $col) {
                    $sheet->getStyle($col . '2:' . $col . '1000')
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // Add date format for tanggal column
                $sheet->getStyle('A2:A1000')
                    ->getNumberFormat()
                    ->setFormatCode('DD/MM/YYYY');

                // Add number format for quantity
                $sheet->getStyle('E2:E1000')
                    ->getNumberFormat()
                    ->setFormatCode('0');

                // Add comments for required fields
                $requiredFields = [
                    'A1' => 'Wajib diisi dengan format DD/MM/YYYY',
                    'D1' => 'Wajib diisi',
                    'H1' => 'Wajib diisi (pending/shipped)',
                    'L1' => 'Wajib diisi (cod/transfer)',
                    'M1' => 'Wajib diisi dengan angka',
                    'Y1' => 'Wajib diisi',
                    'Z1' => 'Wajib diisi',
                    'AB1' => 'Wajib diisi',
                    'AC1' => 'Wajib diisi',
                    'AD1' => 'Wajib diisi dengan nama operator yang valid'
                ];

                foreach ($requiredFields as $cell => $comment) {
                    $sheet->getComment($cell)->getText()->createTextRun($comment);
                }

                // Protect sheet structure
                $sheet->getProtection()->setSheet(true);
                $sheet->getProtection()->setSort(true);
                $sheet->getProtection()->setInsertRows(true);
                $sheet->getProtection()->setFormatCells(false);
            }
        ];
    }
}