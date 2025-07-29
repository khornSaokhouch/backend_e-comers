<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('invoice_number')->unique();
            $table->timestamp('generated_at')->nullable();
            $table->integer('total_amount');
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('shop_order')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('invoice', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('invoice');
    }
}

