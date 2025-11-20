<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUploadedAtToOrderHistoriesTable extends Migration
{
    public function up()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->timestamp('uploaded_at')->nullable()->after('file_name');
        });
    }

    public function down()
    {
        Schema::table('order_histories', function (Blueprint $table) {
            $table->dropColumn('uploaded_at');
        });
    }
}
