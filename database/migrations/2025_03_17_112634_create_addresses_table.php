<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            // FIX: Change user_id to unsignedBigInteger (works on both MySQL & PostgreSQL)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state_province_or_region')->nullable();
            $table->string('zip_or_postal_code')->nullable();
            $table->string('address_type')->default('shipping');
            $table->boolean('is_guest_address')->default(false);
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
