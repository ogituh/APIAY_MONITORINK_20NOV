<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_delv_date',
        'supplier',
        'part_no',
        'qty_po',
        'previous_qty_po',
        'qty_po_change',
        'stock',
        'standard'
    ];

    // Tambahkan casting untuk memastikan tipe data
    protected $casts = [
        'stock' => 'decimal:2',
        'qty_po' => 'decimal:2',
        'previous_qty_po' => 'decimal:2',
        'qty_po_change' => 'decimal:2'
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'bpid', 'supplier');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'part_no', 'part_no');
    }
}
