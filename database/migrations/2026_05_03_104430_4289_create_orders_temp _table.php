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
        Schema::create('orders_temp', function (Blueprint $table) {
            $table->increments('id'); // Fixed
            $table->integer('member_id');
            $table->integer('client_id');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->string('post_code');
            $table->string('city');
            $table->integer('country');
            $table->string('akis_branch')->nullable();
            $table->string('shipping_type')->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('subtotal_euro', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->decimal('grand_total_euro', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_line_discount', 10, 2)->default(0);
            $table->decimal('total_after_discount', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->integer('vat_percentage')->default(0);
            $table->decimal('discount_percentage_to_amount', 10, 2)->default(0);
            $table->string('other_1')->nullable();
            $table->string('other_2')->nullable();
            $table->string('other_3')->nullable();
            $table->string('boxnow_locker_id')->nullable();
            $table->string('boxnow_locker_name')->nullable();
            $table->string('boxnow_locker_address')->nullable();
            $table->string('boxnow_voucher_status')->nullable();
            $table->dateTime('date')->nullable(); // Changed to dateTime
            $table->string('status')->default('pending');
            $table->string('payment_type')->nullable();
            $table->string('store_payment_type')->nullable();
            $table->string('credit_payment')->default('no');
            $table->string('delivered')->default('no');
            $table->string('cancelled')->default('no');
            $table->integer('ZeroVAT')->default(0);
            $table->integer('discount_type')->default(0);
            $table->integer('code_version')->default(1);
            $table->integer('VivaWallet')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_temp'); // Fixed
    }
};
