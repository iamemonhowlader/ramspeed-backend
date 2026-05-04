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
        Schema::create('importtasks', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->text('connection');
            $table->text('query');
            $table->text('fields');
            $table->text('categories');
            $table->text('description');
            $table->string('act');
            $table->integer('form');
            $table->integer('resubscribe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importtasks');
    }
};
