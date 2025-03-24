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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // reviewee: the user being reviewed; reviewer: the user leaving the review
            $table->unsignedBigInteger('reviewee_id');
            $table->unsignedBigInteger('reviewer_id');

            $table->text('review');

            $table->foreign('reviewee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');

            // Ensure a reviewer can only leave one review per reviewee
            $table->unique(['reviewee_id', 'reviewer_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
