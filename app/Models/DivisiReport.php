<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class DivisiReport extends Model
{
    protected $table = 'customers';

    public function scopeGetReport($query)
    {
        return $query->select(
            'divisis.id as id',
            'divisis.name as nama_divisi',
            DB::raw('COUNT(DISTINCT customers.id) as total_customer'),
            DB::raw('SUM(customers.quantity) as total_quantity'),
            DB::raw('SUM(customers.total_pembayaran) as total_omset')
        )
            ->join('divisis', 'customers.divisi_id', '=', 'divisis.id')
            ->groupBy('divisis.id', 'divisis.name');
    }
    public function scopeGetProductGroupReport($query)
    {
        return $query->select(
            'products.id as product_id',
            'products.name as nama_produk',
            DB::raw('COUNT(DISTINCT customers.id) as total_customer'),
            DB::raw('SUM(customers.quantity) as total_quantity'),
            DB::raw('SUM(customers.total_pembayaran) as total_omset')
        )
            ->join('products', 'customers.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.name');
    }
}