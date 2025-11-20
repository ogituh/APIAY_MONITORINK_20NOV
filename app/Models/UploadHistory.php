<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadHistory extends Model
{
    use HasFactory;

    // Nama tabel
    protected $table = 'upload_histories';

    // Field yang boleh diisi
    protected $fillable = [
        'bpid',
        'file_name',
        'uploaded_at',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'bpid', 'bpid');
    }
}
