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
        Schema::create('paypal_subscription_info', function (Blueprint $table) {
            $table->string('subscr_id');
            $table->string('sub_event');
            $table->string('subscr_date');
            $table->string('subscr_effective');
            $table->string('period1');
            $table->string('period2');
            $table->string('period3');
            $table->string('amount1');
            $table->string('amount2');
            $table->string('amount3');
            $table->string('mc_amount1');
            $table->string('mc_amount2');
            $table->string('mc_amount3');
            $table->string('recurring');
            $table->string('reattempt');
            $table->string('retry_at');
            $table->string('recur_times');
            $table->string('username');
            $table->string('password');
            $table->string('payment_txn_id');
            $table->string('subscriber_emailaddress');
            $table->date('datecreation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paypal_subscription_info');
    }
};
