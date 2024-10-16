<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        // Tambahkan field lain yang mungkin Anda perlukan
    ];
    public function __toString()
    {
        return $this->name;
    }
    // Relasi dengan Divisi (Company belongs to a Divisi)
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(related: Divisi::class);
    }

    // Relasi dengan Customer (Company has many Customers)
    public function customers(): HasMany
    {
        return $this->hasMany(related: Customer::class);
    }
    public function operator(): BelongsTo // Perubahan disini
    {
        return $this->BelongsTo(related: Operator::class);
    }
    // Relasi dengan CustomerService (jika setiap Company memiliki CustomerService tersendiri)
    public function customerServices(): HasMany
    {
        return $this->hasMany(related: CustomerService::class);
    }

    // Relasi dengan StatusCustomer (jika relevan)
    public function statusCustomers(): HasMany
    {
        return $this->hasMany(related: StatusCustomer::class);
    }

    // Metode untuk mendapatkan jumlah customer
    public function getCustomerCountAttribute(): mixed
    {
        return $this->customers()->count();
    }

    // Metode untuk mendapatkan customer aktif
    public function activeCustomers(): mixed
    {
        return $this->customers()->where('status', 'active');
    }

    // Scope untuk company yang aktif (jika Anda memiliki field 'is_active')
    public function scopeActive($query): mixed
    {
        return $query->where('is_active', true);
    }

    // Metode untuk mendapatkan total pendapatan dari semua customer (jika relevan)
    public function getTotalRevenueAttribute(): mixed
    {
        return $this->customers()->sum('revenue');
    }
}