<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadOrderHistory extends Model
{
    use HasFactory;

    protected $table = 'upload_order_histories';

    protected $fillable = [
        'file_name',
        'uploaded_at',
        'upload_by',
        'type',
    ];
}
