<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->unsignedBigInteger('category_id'); // Foreign Key
            $table->string('name');
            $table->text('description')->nullable();
            $table->binary('product_image')->nullable(); // Blob for image
            $table->integer('price');
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
