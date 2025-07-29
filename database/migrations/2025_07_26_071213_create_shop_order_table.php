<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopOrderTable extends Migration
{
    public function up()
    {
        Schema::create('shop_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->date('order_date');
            $table->unsignedBigInteger('payment_method_id');
            $table->string('shipping_address');
            $table->unsignedBigInteger('shipping_method_id');
            $table->integer('order_total');
            $table->unsignedBigInteger('order_status_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('user_payment_method')->onDelete('cascade');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_method')->onDelete('cascade');
            $table->foreign('order_status_id')->references('id')->on('order_status')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('shop_order', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_method_id']);
            $table->dropForeign(['shipping_method_id']);
            $table->dropForeign(['order_status_id']);
        });

        Schema::dropIfExists('shop_order');
    }
}

