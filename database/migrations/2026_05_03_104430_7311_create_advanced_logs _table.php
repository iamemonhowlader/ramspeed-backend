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
        Schema::create('advanced_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('full_name');
            $table->string('user_agent');
            $table->string('ip');
            $table->string('page');
            $table->string('host');
            $table->timestamp('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advanced_logs');
    }
};
