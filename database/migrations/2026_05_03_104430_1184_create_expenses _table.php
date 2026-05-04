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
        Schema::create('expenses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('supplier_id');
            $table->string('Company_reg_num');
            $table->string('invoice');
            $table->string('other');
            $table->date('date');
            $table->date('Exp_Date');
            $table->decimal('GROSS');
            $table->decimal('VAT');
            $table->decimal('Calculated_VAT');
            $table->decimal('Calculated_NET');
            $table->integer('cancelled');
            $table->string('type');
            $table->integer('Service_Receipt');
            $table->decimal('ZeroVAT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
