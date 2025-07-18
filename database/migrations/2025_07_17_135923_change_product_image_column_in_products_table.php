<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('products', function (Blueprint $table) {
        // Change column type from binary to string
        $table->string('product_image')->nullable()->change();
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        // Revert back to binary if needed
        $table->binary('product_image')->nullable()->change();
    });
}

};
