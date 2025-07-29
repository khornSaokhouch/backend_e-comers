<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLineTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('order_line', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_item_id');
            $table->unsignedBigInteger('order_id');
            $table->integer('quantity');
            $table->integer('price');
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_item_id')->references('id')->on('product_items')->onDelete('cascade');

            $table->foreign('order_id')->references('id')->on('shop_order')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('order_line');
    }
}
