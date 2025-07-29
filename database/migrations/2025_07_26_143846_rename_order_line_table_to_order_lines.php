<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOrderLineTableToOrderLines extends Migration
{
    public function up()
    {
        Schema::rename('order_line', 'order_lines');
    }

    public function down()
    {
        Schema::rename('order_lines', 'order_line');
    }
}
