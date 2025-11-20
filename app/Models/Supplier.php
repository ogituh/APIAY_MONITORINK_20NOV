<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';
    protected $fillable = [
        'bpid',
        'name',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'supplier', 'bpid');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'supplier', 'bpid');
    }
}
