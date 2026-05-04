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
        Schema::create('promo_banners', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('sort_order');
            $table->enum('is_active', [0, 1]);
            $table->string('label_en');
            $table->string('label_gr');
            $table->string('title_en');
            $table->string('title_gr');
            $table->string('description_en');
            $table->string('description_gr');
            $table->string('link_url');
            $table->string('image_path');
            $table->timestamp('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_banners');
    }
};
