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
        Schema::create('configurations', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name');
            $table->text('description');
            $table->string('host');
            $table->integer('port');
            $table->integer('smtp_auth');
            $table->string('username');
            $table->string('password');
            $table->string('from');
            $table->string('reply_to');
            $table->integer('mails_per_connection');
            $table->date('free');
            $table->integer('inbox');
            $table->string('inbox_host');
            $table->integer('inbox_port');
            $table->string('inbox_flags');
            $table->string('mailbox');
            $table->string('inbox_username');
            $table->string('inbox_password');
            $table->integer('inbox_wait');
            $table->date('inbox_free');
            $table->integer('mails_per_time');
            $table->integer('time');
            $table->integer('mcount');
            $table->integer('delivery');
            $table->string('aws_access_key');
            $table->string('aws_secret_key');
            $table->string('sendmail_path');
            $table->string('bounce_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
