<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromotionCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('promotion_category', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Composite primary key (optional but recommended)
            $table->primary(['promotion_id', 'category_id']);

            // Foreign keys
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('promotion_category');
    }
}
