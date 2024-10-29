<?php

namespace App\Exports;

use App\Models\ImportError;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataCustomerErrorExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $importLogId;

    public function __construct($importLogId)
    {
        $this->importLogId = $importLogId;
    }

    public function collection()
    {
        return ImportError::where('import_log_id', $this->importLogId)
            ->orderBy('row_number')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Baris ke',
            'Error Message',
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

    public function map($importError): array
    {
        $rowData = $importError->row_data;

        return [
            $importError->row_number,
            $importError->error_message,
            $rowData['tanggal'] ?? '',
            $rowData['nama_pelanggan'] ?? '',
            $rowData['no_telepon'] ?? '',
            $rowData['nama_produk'] ?? '',
            $rowData['quantity'] ?? '',
            $rowData['alamat_pengirim'] ?? '',
            $rowData['id_pelacakan'] ?? '',
            $rowData['status_granular'] ?? '',
            $rowData['nama_pengirim'] ?? '',
            $rowData['kontak_pengirim'] ?? '',
            $rowData['kode_pos_pengirim'] ?? '',
            $rowData['metode_pembayaran'] ?? '',
            $rowData['total_pembayaran'] ?? '',
            $rowData['alamat_penerima'] ?? '',
            $rowData['alamat_penerima_2'] ?? '',
            $rowData['kode_pos'] ?? '',
            $rowData['no_invoice'] ?? '',
            $rowData['keterangan_promo'] ?? '',
            $rowData['keterangan_issue'] ?? '',
            $rowData['ongkos_kirim'] ?? '',
            $rowData['potongan_ongkos_kirim'] ?? '',
            $rowData['potongan_lain_1'] ?? '',
            $rowData['potongan_lain_2'] ?? '',
            $rowData['potongan_lain_3'] ?? '',
            $rowData['customer_service'] ?? '',
            $rowData['advertiser'] ?? '',
            $rowData['status_customer'] ?? '',
            $rowData['company'] ?? '',
            $rowData['divisi'] ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE0E0'] // Light red background
                ]
            ],
            // Style untuk kolom error message
            'B' => ['font' => ['color' => ['rgb' => 'FF0000']]], // Red text for error messages
        ];
    }
}