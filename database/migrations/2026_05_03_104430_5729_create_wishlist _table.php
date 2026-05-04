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
        Schema::create('wishlist', function (Blueprint $table) {
            $table->increments('id'); // Fixed
            $table->unsignedInteger('member_id');
            $table->unsignedInteger('product_id');
            $table->timestamp('date_added')->nullable(); // Renamed from created_at to match DB
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist'); // Fixed
    }
};
