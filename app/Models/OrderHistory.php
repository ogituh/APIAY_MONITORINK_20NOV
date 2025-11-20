<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    use HasFactory;

    protected $table = 'order_histories';

    protected $fillable = [
        'supplier',
        'part_no',
        'previous_qty_po',
        'new_qty_po',
        'qty_po_change',
        'previous_stock',
        'new_stock',
        'stock_change',
        'standard',
        'updated_by',
        'file_name'
    ];

    protected $casts = [
        'previous_qty_po' => 'decimal:2',
        'new_qty_po' => 'decimal:2',
        'qty_po_change' => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock' => 'decimal:2',
        'stock_change' => 'decimal:2',
    ];

    /**
     * Relationship dengan user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by', 'bpid');
    }
}
