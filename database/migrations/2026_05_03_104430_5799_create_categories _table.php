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
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id'); // Fixed
            $table->string('name');
            $table->string('namegr')->nullable(); // Added missing
            $table->text('description')->nullable();
            $table->integer('subscriber_count')->default(0);
            $table->string('active')->default('yes'); // Added missing
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories'); // Fixed
    }
};
