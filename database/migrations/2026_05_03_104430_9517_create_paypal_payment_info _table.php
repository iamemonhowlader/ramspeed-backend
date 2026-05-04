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
        Schema::create('paypal_payment_info', function (Blueprint $table) {
            $table->string('firstname');
            $table->string('lastname');
            $table->string('buyer_email');
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('zipcode');
            $table->string('memo');
            $table->string('itemname');
            $table->string('itemnumber');
            $table->string('os0');
            $table->string('on0');
            $table->string('os1');
            $table->string('on1');
            $table->string('quantity');
            $table->string('paymentdate');
            $table->string('paymenttype');
            $table->string('txnid');
            $table->string('mc_gross');
            $table->string('mc_fee');
            $table->string('paymentstatus');
            $table->string('pendingreason');
            $table->string('txntype');
            $table->string('tax');
            $table->string('mc_currency');
            $table->string('reasoncode');
            $table->string('custom');
            $table->string('country');
            $table->date('datecreation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_payment_info');
    }
};
