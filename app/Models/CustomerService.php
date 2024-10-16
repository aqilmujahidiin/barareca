<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerService extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function __toString()
    {
        return $this->name;
    }
    public function customers(): HasMany
    {
        return $this->hasMany(related: Customer::class);
    }
}
