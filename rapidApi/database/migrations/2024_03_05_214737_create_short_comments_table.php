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
        Schema::create('short_comments', function (Blueprint $table) {
            $table->id();
            $table->mediumText('comment');
            $table->unsignedBigInteger('short');
            $table->unsignedBigInteger('user');
            $table->unsignedBigInteger('parent_comment')->nullable();
            $table->foreign('short')->references('id')->on('product_shorts')->onDelete('cascade');
            $table->foreign('user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_comment')->references('id')->on('short_comments')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('short_comments');
    }
};
