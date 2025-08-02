<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserReviewTable extends Migration
{
    public function up()
    {
        Schema::create('user_review', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_product_id');
            $table->text('review_text')->nullable();
            $table->integer('rating');
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_product_id')->references('id')->on('order_lines')->onDelete('cascade'); // <-- note the plural here
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('user_review');
    }
}
