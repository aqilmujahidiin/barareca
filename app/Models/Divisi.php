<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function __toString()
    {
        return $this->name;
    }
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    // Jika Divisi juga terkait dengan CustomerService
    public function customerServices()
    {
        return $this->hasMany(CustomerService::class);
    }

    // Jika Anda ingin melihat semua Customer yang terkait dengan Divisi ini
    public function customers()
    {
        return $this->hasManyThrough(Customer::class, Company::class);
    }
}
