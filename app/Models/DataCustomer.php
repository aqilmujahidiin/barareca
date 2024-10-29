<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataCustomer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'data_customers';

    // Status Granular constants
    const STATUS_PENDING = 'pending';
    const STATUS_SHIPPED = 'shipped';

    // Status Customer constants
    const PAYMENT_COD = 'cod';
    const PAYMENT_TRANSFER = 'transfer';

    protected $fillable = [
        'tanggal',
        'nama_pelanggan',
        'no_telepon',
        'nama_produk',
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
        'keterangan_issue',
        'ongkos_kirim',
        'potongan_ongkos_kirim',
        'potongan_lain_1',
        'potongan_lain_2',
        'potongan_lain_3',
        'customer_service',
        'advertiser',
        'operator_id',
        'status_customer',
        'company',
        'divisi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total_pembayaran' => 'decimal:2',
        'ongkos_kirim' => 'decimal:2',
        'potongan_ongkos_kirim' => 'decimal:2',
        'potongan_lain_1' => 'decimal:2',
        'potongan_lain_2' => 'decimal:2',
        'potongan_lain_3' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Required fields validation rules
    public static $rules = [
        'tanggal' => 'required|date',
        'total_pembayaran' => 'required|numeric',
        'customer_service' => 'required|string',
        'advertiser' => 'required|string',
        'operator_id' => 'required|exists:users,id',
        'company' => 'required|string',
        'divisi' => 'required|string',
        'nama_produk' => 'required|string',
        'status_granular' => 'required|in:pending,shipped',
    ];

    // Relasi ke User (operator)
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Accessor untuk total setelah potongan
    public function getTotalSetelahPotonganAttribute()
    {
        return $this->total_pembayaran
            - ($this->potongan_ongkos_kirim ?? 0)
            - ($this->potongan_lain_1 ?? 0)
            - ($this->potongan_lain_2 ?? 0)
            - ($this->potongan_lain_3 ?? 0);
    }

    // Scope untuk filter berdasarkan status
    public function scopeStatus($query, $status)
    {
        return $query->where('status_granular', $status);
    }

    // Scope untuk filter berdasarkan tanggal
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    // Scope untuk filter berdasarkan operator
    public function scopeByOperator($query, $operatorId)
    {
        return $query->where('operator_id', $operatorId);
    }

    // Scope untuk filter berdasarkan company
    public function scopeByCompany($query, $company)
    {
        return $query->where('company', $company);
    }

    // Scope untuk filter berdasarkan metode pembayaran
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('status_customer', $method);
    }

    // Scope untuk filter data pending
    public function scopePending($query)
    {
        return $query->where('status_granular', self::STATUS_PENDING);
    }

    // Scope untuk filter data shipped
    public function scopeShipped($query)
    {
        return $query->where('status_granular', self::STATUS_SHIPPED);
    }
}