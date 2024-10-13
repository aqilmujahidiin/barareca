<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $fillable = [
        'tanggal',
        'no_telepon',
        'nama_pelanggan',
        'nama_produk',
        'quantity',
        'alamat_pengirim',
        'id_pelacakan',
        'status_granular',
        'nama_pengirim',
        'kontak_pengirim',
        'kode_pos_pengirim',
        'cash_on_delivery',
        'transfer',
        'alamat_penerima',
        'customer_service',
        'advertiser',
        'inp',
        'ongkos_kirim',
        'potongan_ongkos_kirim',
        'potongan_lain_1',
        'potongan_lain_2',
        'potongan_lain_3',
        'status_customer',
        'alamat_penerima_2',
        'kode_pos',
        'no_invoice',
        'keterangan_promo',
        'company',
        'divisi',
    ];
}
