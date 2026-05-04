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
        Schema::create('paypal_cart_info', function (Blueprint $table) {
            $table->string('txnid');
            $table->string('itemname');
            $table->string('itemnumber');
            $table->string('os0');
            $table->string('on0');
            $table->string('os1');
            $table->string('on1');
            $table->string('quantity');
            $table->string('invoice');
            $table->string('custom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_cart_info');
    }
};
