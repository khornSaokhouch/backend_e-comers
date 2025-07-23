<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreIdToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id'); 
            // Add nullable() if existing records exist, otherwise remove nullable()
            
            // If you want foreign key constraint (recommended if stores table exists)
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['store_id']); // drop foreign key first
            $table->dropColumn('store_id');
        });
    }
}

