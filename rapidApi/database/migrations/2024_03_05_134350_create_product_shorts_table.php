<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_shorts', function (Blueprint $table) {
            $table->id();
            $table->string('video');
            $table->string('image');
            $table->string('title');
            $table->unsignedBigInteger('product');
            $table->foreign('product')->references('id')->on('products')->onDelete('cascade');
            $table->string('status')->default('published');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_shorts');
    }
};
