<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->string('supplier');
            $table->string('part_no');
            $table->decimal('previous_qty_po', 15, 2)->nullable();
            $table->decimal('new_qty_po', 15, 2);
            $table->decimal('qty_po_change', 15, 2);
            $table->decimal('previous_stock', 15, 2)->nullable();
            $table->decimal('new_stock', 15, 2);
            $table->decimal('stock_change', 15, 2);
            $table->string('standard');
            $table->string('updated_by'); // User yang melakukan update
            $table->string('file_name')->nullable(); // Nama file yang diupload
            $table->timestamps();

            // Index untuk performa query
            $table->index(['supplier', 'part_no']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_histories');
    }
}
