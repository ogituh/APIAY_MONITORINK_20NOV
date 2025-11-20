<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdersAdmin extends Model
{
    use HasFactory;

    protected $table = 'orders_admins';

    protected $fillable = [
        'plan_delv_date',
        'supplier',
        'part_no',
        'qty_po',
        'stock',
        'upload_source',
        'downloaded_by_supplier',
        'previous_qty_po',
        'qty_po_change',
        'standard',
        'previous_stock',
        'stock_change',
    ];

    protected $casts = [
        'plan_delv_date'         => 'date',
        'downloaded_by_supplier' => 'boolean',
        'previous_qty_po'        => 'decimal:2',
        'qty_po_change'          => 'decimal:2',
        'previous_stock'         => 'decimal:2',
        'stock_change'           => 'decimal:2',
        'qty_po'                 => 'decimal:2',
        'stock'                  => 'decimal:2',
    ];

    // RELASI YANG SAMA DENGAN Order
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'part_no', 'part_no');
    }

    public function supplierRelation(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier', 'bpid');
    }
}
