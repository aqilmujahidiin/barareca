<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'no_telepon',
        'nama_pelanggan',
        'product_id',
        'quantity',
        'alamat_pengirim',
        'id_pelacakan',
        'status_granular',
        'nama_pengirim',
        'kontak_pengirim',
        'kode_pos_pengirim',
        'metode_pembayaran',
        'total_pembayaran',
        'alamat_penerima',
        'alamat_penerima_2',
        'kode_pos',
        'no_invoice',
        'keterangan_promo',
        'ongkos_kirim',
        'potongan_ongkos_kirim',
        'potongan_lain_1',
        'potongan_lain_2',
        'potongan_lain_3',
        'customer_service_id',
        'advertiser_id',
        'operator_id',
        'status_customer_id',
        'company_id',
        'divisi_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_pembayaran' => 'decimal:2',
    ];

    // Relasi
    public function customerService(): BelongsTo
    {
        return $this->belongsTo(related: CustomerService::class);
    }
    public function product(): BelongsTo
    {
        return $this->belongsTo(related: Product::class);
    }
    public function advertiser(): BelongsTo
    {
        return $this->belongsTo(related: Advertiser::class);
    }

    public function operator(): BelongsTo // Perubahan disini
    {
        return $this->belongsTo(related: Operator::class);
    }

    public function statusCustomer(): BelongsTo
    {
        return $this->belongsTo(related: StatusCustomer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(related: Company::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(related: Divisi::class);
    }

    // Metode tambahan
    public function getTotalPotonganAttribute(): mixed
    {
        return $this->potongan_ongkos_kirim + $this->potongan_lain_1 +
            $this->potongan_lain_2 + $this->potongan_lain_3;
    }

    public function getNettoPaymentAttribute(): float
    {
        return $this->total_pembayaran - $this->getTotalPotonganAttribute();
    }
}