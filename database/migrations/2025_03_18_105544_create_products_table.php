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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Changed from foreignId to string for UUID support
            $table->foreignId('category_id')->constrained();
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('condition_id')->constrained();
            $table->foreignId('address_id')->constrained();
            $table->string('title');
            $table->integer('quantity')->default(1);
            $table->integer('quantity_left')->default(1);
            $table->string('approval_status')->default('pending');
            $table->string('managed_by_closyyyy')->default('0');
            $table->text('description')->nullable(); // Changed to text
            $table->string('location');
            $table->string('city');
            $table->string('shipping_type');
            
            // ✅ FIX: Use smallInteger instead of boolean for PostgreSQL compatibility
            $table->smallInteger('active')->default(1);
            $table->smallInteger('sold')->default(0);
            $table->smallInteger('allow_offers')->default(1);
            
            $table->unsignedBigInteger('price');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
