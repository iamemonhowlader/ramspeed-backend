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
        Schema::create('recipients', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('subscriber_id');
            $table->date('send_date');
            $table->integer('sent');
            $table->date('read_date');
            $table->integer('mail_id');
            $table->integer('failed');
            $table->integer('read');
            $table->string('country');
            $table->integer('open_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipients');
    }
};
