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
        Schema::create('forms', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->text('description');
            $table->string('title');
            $table->text('content');
            $table->text('selected_categories');
            $table->text('thanks_text');
            $table->text('style');
            $table->text('unsubscribe_text');
            $table->string('unsubscribe_title');
            $table->string('thanks_title');
            $table->text('confirm_mail');
            $table->text('notify_mail');
            $table->integer('notify');
            $table->integer('confirm');
            $table->string('notify_addresse');
            $table->text('confirm_text');
            $table->string('confirm_title');
            $table->integer('configuration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
