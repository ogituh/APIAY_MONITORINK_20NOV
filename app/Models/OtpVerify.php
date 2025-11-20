<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerify extends Model
{
    use HasFactory;

    // Tambahkan ini jika nama tabel tidak sesuai konvensi
    protected $table = 'otp_verifies';

    // Tambahkan fillable jika ingin mass assignment
    protected $fillable = [
        'bpid',
        'otp',
        'hp',
        'expired_date',
    ];
}
