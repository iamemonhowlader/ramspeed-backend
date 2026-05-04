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
        Schema::create('mails', function (Blueprint $table) {
            $table->integer('id');
            $table->string('subject');
            $table->text('content_html');
            $table->text('content_text');
            $table->date('created');
            $table->date('send_date');
            $table->integer('status');
            $table->integer('type');
            $table->integer('configuration_id');
            $table->integer('unsubscribed');
            $table->date('modified');
            $table->integer('template_id');
            $table->integer('last_step');
            $table->text('final_html');
            $table->date('send_on');
            $table->string('prepared');
            $table->integer('campaign_id');
            $table->integer('active');
            $table->integer('delay');
            $table->integer('sendtof');
            $table->integer('private');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mails');
    }
};
