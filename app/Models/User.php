<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\OtpVerify;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'bpid',
        'username',
        'password',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function otps(): HasMany
    {
        return $this->hasMany(OtpVerify::class, 'bpid', 'bpid');
    }

    public function generateOTP(): string
    {
        $this->otps()->delete();
        $otp = rand(100000, 999999);
        $this->otps()->create([
            'otp' => $otp,
            'hp' => $this->phone,
            'expired_date' => now()->addMinutes(5),
        ]);
        return $otp;
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'bpid', 'bpid');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'insert_by', 'username');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'bpid', 'bpid');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'bpid', 'bpid');
    }
}
