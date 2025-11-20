<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verifies', function (Blueprint $table) {
            $table->id();
            $table->string('bpid');
            $table->string('otp');
            $table->string('hp')->nullable(); // nomor HP yang terkait dengan OTP
            $table->dateTime('expired_date');
            $table->timestamps();

            $table->foreign('bpid')->references('bpid')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verifies');
    }
};
