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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CustomersImport implements ToModel, WithChunkReading, WithHeadingRow, ShouldQueue, WithValidation, WithEvents
{
    use Importable;

    protected $companies;
    protected $divisions;
    protected $customerServices;
    protected $advertisers;
    protected $operators;
    protected $statusCustomers;
    protected $products;
    private $rows = 0;
    protected $filePath;
    protected $cachePrefix = 'import_cache_';
    protected $cacheDuration = 3600; // 1 hour

    public function __construct()
    {
        // Initialize collections and cache them
        $this->initializeCollections();
    }

    protected function initializeCollections()
    {
        // Load all related data with caching
        $this->companies = $this->getCachedCollection('companies', function () {
            return Company::select('id', 'name')->get();
        });
        $this->divisions = $this->getCachedCollection('divisions', function () {
            return Divisi::select('id', 'name')->get();
        });
        $this->customerServices = $this->getCachedCollection('customer_services', function () {
            return CustomerService::select('id', 'name')->get();
        });
        $this->advertisers = $this->getCachedCollection('advertisers', function () {
            return Advertiser::select('id', 'name')->get();
        });
        $this->operators = $this->getCachedCollection('operators', function () {
            return Operator::select('id', 'name')->get();
        });
        $this->statusCustomers = $this->getCachedCollection('status_customers', function () {
            return StatusCustomer::select('id', 'name')->get();
        });
        $this->products = $this->getCachedCollection('products', function () {
            return Product::select('id', 'name')->get();
        });
    }

    protected function getCachedCollection(string $key, callable $callback): Collection
    {
        $cacheKey = $this->cachePrefix . $key;
        return Cache::remember($cacheKey, $this->cacheDuration, $callback);
    }

    public function model(array $row)
    {
        $this->rows++;

        try {
            // Get or create related entities with error handling
            $company = $this->getOrCreateEntity($this->companies, Company::class, $row['company']);
            $division = $this->getOrCreateEntity($this->divisions, Divisi::class, $row['divisi']);
            $customerService = $this->getOrCreateEntity($this->customerServices, CustomerService::class, $row['customer_service']);
            $advertiser = $this->getOrCreateEntity($this->advertisers, Advertiser::class, $row['advertiser']);
            $operator = $this->getOrCreateEntity($this->operators, Operator::class, $row['operator']);
            $statusCustomer = !empty($row['status_customer'])
                ? $this->getOrCreateEntity($this->statusCustomers, StatusCustomer::class, $row['status_customer'])
                : null;
            $product = $this->getOrCreateEntity($this->products, Product::class, $row['nama_produk']);

            return new Customer([
                'tanggal' => $this->parseDate($row['tanggal'] ?? now()->format('d/m/Y')),
                'no_telepon' => $row['no_telepon'] ?? '',
                'nama_pelanggan' => $row['nama_pelanggan'] ?? '',
                'product_id' => $product?->id,
                'quantity' => $row['quantity'] ?? 0,
                'alamat_pengirim' => $row['alamat_pengirim'] ?? '',
                'id_pelacakan' => $row['id_pelacakan'] ?? null,
                'status_granular' => $row['status_granular'] ?? null,
                'nama_pengirim' => $row['nama_pengirim'] ?? '',
                'kontak_pengirim' => $row['kontak_pengirim'] ?? '',
                'kode_pos_pengirim' => $row['kode_pos_pengirim'] ?? '',
                'metode_pembayaran' => strtolower($row['metode_pembayaran'] ?? ''),
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
                'company_id' => $company?->id,
                'divisi_id' => $division?->id,
                'customer_service_id' => $customerService?->id,
                'advertiser_id' => $advertiser?->id,
                'operator_id' => $operator?->id,
                'status_customer_id' => $statusCustomer?->id,
            ]);
        } catch (\Exception $e) {
            Log::error("Error importing row {$this->rows}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function getOrCreateEntity($collection, string $modelClass, ?string $value)
    {
        if (empty($value)) {
            return null;
        }

        // Normalize the value
        $value = trim($value);

        // Try to find in collection first (case-insensitive)
        $entity = $collection->first(function ($item) use ($value) {
            return strtolower($item->name) === strtolower($value);
        });

        if (!$entity) {
            try {
                // If not found in collection, try to find in database
                $entity = $modelClass::firstOrCreate(
                    ['name' => $value],
                    ['name' => $value]
                );

                // Add to collection for future lookups
                $collection->push($entity);

                // Update cache
                $cacheKey = $this->cachePrefix . strtolower(class_basename($modelClass)) . 's';
                Cache::put($cacheKey, $collection, $this->cacheDuration);
            } catch (\Exception $e) {
                Log::error("Error creating {$modelClass} with value {$value}: " . $e->getMessage());
                throw $e;
            }
        }

        return $entity;
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return now()->format('Y-m-d');
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Invalid date format: {$date}. Using current date.");
            return now()->format('Y-m-d');
        }
    }

    private function cleanCurrency($value)
    {
        if (empty($value)) {
            return 0;
        }
        return (float) preg_replace('/[^0-9.]/', '', $value);
    }

    public function rules(): array
    {
        return [
            '*.metode_pembayaran' => 'nullable|string',
            '*.total_pembayaran' => 'nullable|numeric',
            '*.company' => 'required|string',
            '*.divisi' => 'required|string',
            '*.customer_service' => 'required|string',
            '*.advertiser' => 'required|string',
            '*.operator' => 'required|string',
            '*.nama_produk' => 'required|string',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                Log::info("Import completed successfully. Total rows imported: " . $this->rows);
                // Clear cache after import
                Cache::tags([$this->cachePrefix])->flush();
            },
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
}