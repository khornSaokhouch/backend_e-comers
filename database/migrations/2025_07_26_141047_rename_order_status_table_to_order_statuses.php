<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameOrderStatusTableToOrderStatuses extends Migration
{
    /**
     * Run the migrations.
     * Rename the existing 'order_status' table to 'order_statuses'.
     */
    public function up()
    {
        // Rename table from singular 'order_status' to plural 'order_statuses'
        Schema::rename('order_status', 'order_statuses');
    }

    /**
     * Reverse the migrations.
     * Rename the 'order_statuses' table back to 'order_status'.
     */
    public function down()
    {
        // Rollback the rename: change 'order_statuses' back to 'order_status'
        Schema::rename('order_statuses', 'order_status');
    }
}

