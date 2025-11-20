<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('bpid')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('phone')->nullable(); // nomor HP user
            $table->integer('status')->nullable(); // status user (amdmin/non-admin)
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
