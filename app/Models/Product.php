<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
    ];
    public function __toString()
    {
        return $this->name;
    }
    protected $casts = [
        'price' => 'decimal:2',
    ];

    // Relasi dengan Customer
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
    // Accessor untuk format harga
    public function getFormattedPriceAttribute()
    {
        return $this->price ? 'Rp ' . number_format($this->price, 2, ',', '.') : '-';
    }
}