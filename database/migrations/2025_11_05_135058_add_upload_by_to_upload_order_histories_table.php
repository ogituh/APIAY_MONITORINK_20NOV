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
        Schema::table('upload_order_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('upload_order_histories', 'upload_by')) {
                $table->string('upload_by')->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upload_order_histories', function (Blueprint $table) {
            if (Schema::hasColumn('upload_order_histories', 'upload_by')) {
                $table->dropColumn('upload_by');
            }
        });
    }
};
