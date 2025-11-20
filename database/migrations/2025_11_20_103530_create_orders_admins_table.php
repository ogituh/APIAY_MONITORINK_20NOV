<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders_admins', function (Blueprint $table) {
            $table->id();
            $table->date('plan_delv_date');
            $table->string('supplier');
            $table->string('part_no');
            $table->integer('qty_po');
            $table->integer('stock')->nullable();
            $table->enum('upload_source', ['admin', 'supplier'])->default('admin');
            $table->boolean('downloaded_by_supplier')->default(false);
            $table->decimal('previous_qty_po', 15, 2)->nullable();
            $table->decimal('qty_po_change', 15, 2)->nullable();
            $table->string('standard')->nullable();
            $table->decimal('previous_stock', 15, 2)->nullable();
            $table->decimal('stock_change', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_admins');
    }
};
