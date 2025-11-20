<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->date('plan_delv_date');
            $table->string('supplier');
            $table->string('part_no');
            $table->integer('qty_po');

            // Aslinya di DB sekarang "stock" boleh NULL
            $table->integer('stock')->nullable();

            // Tambahan kolom hasil gabungan dari migration lain
            $table->decimal('previous_qty_po', 15, 2)->nullable();
            $table->decimal('qty_po_change', 15, 2)->nullable();
            $table->string('standard')->nullable();
            $table->decimal('previous_stock', 15, 2)->nullable();
            $table->decimal('stock_change', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
