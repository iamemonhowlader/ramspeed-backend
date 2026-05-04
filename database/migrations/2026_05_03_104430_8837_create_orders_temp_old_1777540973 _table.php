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
        Schema::create('orders_temp_old_1777540973', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id');
            $table->integer('client_id');
            $table->string('full_name');
            $table->string('address');
            $table->string('post_code');
            $table->string('city');
            $table->integer('country');
            $table->string('shipping_type');
            $table->decimal('shipping_cost');
            $table->decimal('subtotal');
            $table->decimal('subtotal_euro');
            $table->decimal('grand_total');
            $table->decimal('grand_total_euro');
            $table->decimal('discount');
            $table->decimal('total_line_discount');
            $table->decimal('total_after_discount');
            $table->decimal('vat');
            $table->integer('vat_percentage');
            $table->decimal('discount_percentage_to_amount');
            $table->decimal('other_1');
            $table->decimal('other_2');
            $table->decimal('other_3');
            $table->date('date');
            $table->string('status');
            $table->string('payment_type');
            $table->string('store_payment_type');
            $table->string('credit_payment');
            $table->string('delivered');
            $table->string('cancelled');
            $table->integer('ZeroVAT');
            $table->integer('discount_type');
            $table->integer('code_version');
            $table->integer('VivaWallet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_temp_old_1777540973');
    }
};
