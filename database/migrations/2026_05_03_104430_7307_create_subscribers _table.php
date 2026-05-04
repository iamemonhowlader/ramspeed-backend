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
        Schema::create('subscribers', function (Blueprint $table) {
            $table->integer('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('mail_adresse');
            $table->text('notes');
            $table->date('created');
            $table->integer('deleted');
            $table->string('unsubscribe_code');
            $table->integer('form_id');
            $table->string('custom1');
            $table->string('custom2');
            $table->string('custom3');
            $table->string('custom4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
