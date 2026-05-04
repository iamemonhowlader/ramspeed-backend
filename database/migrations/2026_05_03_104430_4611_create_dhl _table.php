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
        Schema::create('dhl', function (Blueprint $table) {
            $table->integer('id');
            $table->decimal('kg');
            $table->decimal('zone1');
            $table->decimal('zone2');
            $table->decimal('zone3');
            $table->decimal('zone4');
            $table->decimal('zone5');
            $table->decimal('zone6');
            $table->decimal('zone7');
            $table->decimal('zone8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dhl');
    }
};
