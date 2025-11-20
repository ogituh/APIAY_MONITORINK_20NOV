<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_add_upload_source_to_orders_table.php
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('upload_source', ['admin', 'supplier'])->default('admin')->after('stock');
            $table->boolean('downloaded_by_supplier')->default(false)->after('upload_source');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['upload_source', 'downloaded_by_supplier']);
        });
    }
};
