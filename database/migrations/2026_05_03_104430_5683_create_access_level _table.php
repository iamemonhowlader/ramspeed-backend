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
        Schema::create('access_level', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('uid');
            $table->string('menu');
            $table->string('featured');
            $table->string('news');
            $table->string('shipping');
            $table->string('banlist');
            $table->string('user_account');
            $table->string('user_level');
            $table->string('members');
            $table->string('suppliers');
            $table->string('BalanceSheet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_level');
    }
};
