<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPaymentMethodTable extends Migration
{
    public function up()
    {
        Schema::create('user_payment_method', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_type_id');
            $table->string('provider');
            $table->string('card_number');
            $table->date('expiry_date');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('payment_type_id')->references('id')->on('payment_type')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('user_payment_method', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['payment_type_id']);
        });

        Schema::dropIfExists('user_payment_method');
    }
}

