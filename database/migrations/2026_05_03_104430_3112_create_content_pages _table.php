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
        Schema::create('content_pages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('menu_item_id');
            $table->string('title');
            $table->string('title2');
            $table->string('title3');
            $table->string('titlegr');
            $table->string('titlegr2');
            $table->string('titlegr3');
            $table->text('content');
            $table->text('content2');
            $table->text('content3');
            $table->text('contentgr');
            $table->text('contentgr2');
            $table->text('contentgr3');
            $table->string('title_en');
            $table->string('title_en2');
            $table->string('title_en3');
            $table->string('title_gr');
            $table->string('title_gr2');
            $table->string('title_gr3');
            $table->string('active');
            $table->string('link');
            $table->string('link2');
            $table->string('link3');
            $table->string('linkgr');
            $table->string('linkgr2');
            $table->string('linkgr3');
            $table->integer('preview');
            $table->integer('preview2');
            $table->integer('preview3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_pages');
    }
};
