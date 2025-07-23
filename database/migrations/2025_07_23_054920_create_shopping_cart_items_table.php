<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopping_cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_item_id');
            $table->integer('qty');
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('shopping_carts')->onDelete('cascade');
            $table->foreign('product_item_id')->references('id')->on('product_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopping_cart_items');
    }
};

