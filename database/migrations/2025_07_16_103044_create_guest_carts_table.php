<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_carts', function (Blueprint $table) {
            $table->id();
            $table->uuid('guest_id')->unique();
            $table->json('items')->nullable();  // ADD THIS - for cart items
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_carts');
    }
};
