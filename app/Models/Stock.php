<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_delv_date',
        'supplier',
        'part_no',
        'qty_po',
    ];

    // Relationship opsional: Jika ada tabel parts/suppliers terpisah
    public function part()
    {
        return $this->belongsTo(Part::class, 'part_no', 'part_no');  // Asumsi model Part ada
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier', 'bpid');  // Asumsi model Supplier ada
    }
}
