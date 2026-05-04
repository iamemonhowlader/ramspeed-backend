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
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('namegr');
            $table->unsignedInteger('parent');
            $table->string('active_page');
            $table->string('type');
            $table->unsignedInteger('sort');
            $table->string('preview');
            $table->string('icon');
            $table->string('custom_link');
            $table->string('link_target');
            $table->string('show_in_menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
