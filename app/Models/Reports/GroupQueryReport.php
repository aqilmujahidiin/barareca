<?php

// namespace App\Models\Reports;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Eloquent\Builder;

// class GroupQueryReport extends Model
// {
//     protected $table = "customers";

//     public function scopeWithStats(Builder $query): Builder
//     {
//         return $query->select(
//             'divisis.id',
//             'divisis.name as nama_divisi',
//             DB::raw('COUNT(DISTINCT customers.id) as total_customer'),
//             DB::raw('SUM(customers.quantity) as total_quantity'),
//             DB::raw('SUM(customers.total_pembayaran) as total_omset')
//         )
//             ->leftJoin('customers', 'divisis.id', '=', 'customers.divisi_id')
//             ->groupBy('divisis.id', 'divisis.name');
//     }
// }