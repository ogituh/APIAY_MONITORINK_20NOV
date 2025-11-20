<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'bpid',
        'part_no',
        'name',
        'description',
        'unit',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'bpid', 'bpid');
    }
}
