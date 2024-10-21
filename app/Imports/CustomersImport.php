<?php
namespace App\Imports;

use Carbon\Carbon;
use App\Models\Divisi;
use App\Models\Company;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Operator;
use App\Models\Advertiser;
use App\Models\StatusCustomer;
use App\Models\CustomerService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CustomersImport implements ToModel, WithChunkReading, WithHeadingRow, ShouldQueue
{
    use Importable;

    protected $companies;
    protected $divisions;
    protected $customerServices;
    protected $advertisers;
    protected $operators;
    protected $statusCustomers;
    protected $products;

    public function __construct()
    {
        $this->companies = Company::select('id', 'name')->get();
        $this->divisions = Divisi::select('id', 'name')->get();
        $this->customerServices = CustomerService::select('id', 'name')->get();
        $this->advertisers = Advertiser::select('id', 'name')->get();
        $this->operators = Operator::select('id', 'name')->get();
        $this->statusCustomers = StatusCustomer::select('id', 'name')->get();
        $this->products = Product::select('id', 'name')->get();
    }

    public function model(array $row)
    {
        $company = $this->getOrCreateEntity($this->companies, 'Company', $row['company']);
        $division = $this->getOrCreateEntity($this->divisions, 'Divisi', $row['divisi']);
        $customerService = $this->getOrCreateEntity($this->customerServices, 'CustomerService', $row['customer_service']);
        $advertiser = $this->getOrCreateEntity($this->advertisers, 'Advertiser', $row['advertiser']);
        $operator = $this->getOrCreateEntity($this->operators, 'Operator', $row['operator']);
        $statusCustomer = !empty($row['status_customer'])
            ? $this->getOrCreateEntity($this->statusCustomers, 'StatusCustomer', $row['status_customer'])
            : null;
        $product = $this->getOrCreateEntity($this->products, 'Product', $row['nama_produk']);

        return new Customer([
            'tanggal' => $this->parseDate($row['tanggal'] ?? now()->format('d/m/Y')),
            'no_telepon' => $row['no_telepon'] ?? '',
            'nama_pelanggan' => $row['nama_pelanggan'] ?? '',
            'product_id' => $product->id,
            'quantity' => $row['quantity'] ?? 0,
            'alamat_pengirim' => $row['alamat_pengirim'] ?? '',
            'id_pelacakan' => $row['id_pelacakan'] ?? null,
            'status_granular' => $row['status_granular'] ?? null,
            'nama_pengirim' => $row['nama_pengirim'] ?? '',
            'kontak_pengirim' => $row['kontak_pengirim'] ?? '',
            'kode_pos_pengirim' => $row['kode_pos_pengirim'] ?? '',
            'metode_pembayaran' => strtolower($row['metode_pembayaran']),
            'total_pembayaran' => $this->cleanCurrency($row['total_pembayaran'] ?? '0'),
            'alamat_penerima' => $row['alamat_penerima'] ?? '',
            'alamat_penerima_2' => $row['alamat_penerima_2'] ?? null,
            'kode_pos' => $row['kode_pos'] ?? '',
            'no_invoice' => $row['no_invoice'] ?? '',
            'keterangan_promo' => $row['keterangan_promo'] ?? null,
            'ongkos_kirim' => $this->cleanCurrency($row['ongkos_kirim'] ?? '0'),
            'potongan_ongkos_kirim' => $this->cleanCurrency($row['potongan_ongkos_kirim'] ?? '0'),
            'potongan_lain_1' => $this->cleanCurrency($row['potongan_lain_1'] ?? '0'),
            'potongan_lain_2' => $this->cleanCurrency($row['potongan_lain_2'] ?? '0'),
            'potongan_lain_3' => $this->cleanCurrency($row['potongan_lain_3'] ?? '0'),
            'company_id' => $company->id,
            'divisi_id' => $division->id,
            'customer_service_id' => $customerService->id,
            'advertiser_id' => $advertiser->id,
            'operator_id' => $operator->id,
            'status_customer_id' => $statusCustomer ? $statusCustomer->id : null,
        ]);
    }

    private function getOrCreateEntity($collection, $modelName, $value)
    {
        if (empty($value)) {
            return null;
        }

        $entity = $collection->firstWhere('name', $value);
        if (!$entity) {
            $entity = app("App\\Models\\$modelName")::create(['name' => $value]);
            $collection->push($entity);
        }
        return $entity;
    }
    private function parseDate($date)
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    private function cleanCurrency($value)
    {
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    public function chunkSize(): int
    {
        return 500;
    }
    // public function batchSize(): int
    // {
    //     return 100;
    // }
}