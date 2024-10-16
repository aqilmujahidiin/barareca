<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusCustomer extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function __toString()
    {
        return $this->name;
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
