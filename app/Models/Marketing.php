<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marketing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tanggal',
        'budget_iklan',
        'lead',
        'closing',
        'quantity',
        'omset',
        'target_omset',
        'produk',
        'divisi',
        'company',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'budget_iklan' => 'decimal:2',
        'lead' => 'integer',
        'closing' => 'integer',
        'quantity' => 'integer',
        'omset' => 'decimal:2',
        'target_omset' => 'decimal:2',
    ];

}
