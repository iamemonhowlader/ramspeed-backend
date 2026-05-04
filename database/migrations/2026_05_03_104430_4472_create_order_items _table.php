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
        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id');
            $table->integer('store_type');
            $table->integer('product_id');
            $table->integer('temp_id');
            $table->integer('quantity');
            $table->decimal('price');
            $table->decimal('price_euro');
            $table->decimal('discount');
            $table->string('options_msg');
            $table->string('temp_name');
            $table->string('discount_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
