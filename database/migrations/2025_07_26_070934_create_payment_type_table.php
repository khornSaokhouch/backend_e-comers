<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTypeTable extends Migration
{
    public function up()
    {
        Schema::create('payment_type', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // e.g., credit card, paypal
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_type');
    }
}

