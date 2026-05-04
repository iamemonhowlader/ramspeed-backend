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
        Schema::create('suppliers_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->integer('user_id');
            $table->string('full_name');
            $table->string('cperson');
            $table->string('email');
            $table->string('address');
            $table->string('post_code');
            $table->string('city');
            $table->string('country');
            $table->decimal('profit');
            $table->decimal('cyprofit');
            $table->decimal('cysupprofit');
            $table->decimal('cytax');
            $table->string('phone');
            $table->string('fax');
            $table->string('vat_num');
            $table->string('company_reg_num');
            $table->string('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers_info');
    }
};
