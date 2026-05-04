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
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id'); // Fixed to auto-increment
            $table->integer('supplier_id');
            $table->integer('category_id'); // Added missing column
            $table->integer('menu_item_id');
            $table->string('name');
            $table->string('options');
            $table->decimal('price_cy', 10, 2);
            $table->decimal('price_sup_cy', 10, 2);
            $table->decimal('price_cy_unconverted', 10, 2);
            $table->text('namegr');
            $table->decimal('price', 10, 2);
            $table->string('code');
            $table->text('description');
            $table->text('descriptiongr');
            $table->integer('availability');
            $table->integer('availability_cy');
            $table->string('active')->default('yes');
            $table->string('color')->nullable();
            $table->string('color_gr')->nullable();
            $table->string('material')->nullable();
            $table->string('material_gr')->nullable();
            $table->string('ledtype')->nullable();
            $table->string('tasi')->nullable();
            $table->string('lumens')->nullable();
            $table->string('lifetime')->nullable();
            $table->string('usage_en')->nullable();
            $table->string('usage_gr')->nullable();
            $table->string('lightangle')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('basetype')->nullable();
            $table->string('basetype_gr')->nullable();
            $table->string('cover')->nullable();
            $table->string('cover_gr')->nullable();
            $table->string('supply')->nullable();
            $table->string('supply_gr')->nullable();
            $table->string('tasi_exodou')->nullable();
            $table->string('output')->nullable();
            $table->string('output_type')->nullable();
            $table->string('temp_use')->nullable();
            $table->decimal('profit', 10, 2)->default(0);
            $table->string('sintelestis_isxios')->nullable();
            $table->string('warranty')->nullable();
            $table->string('certificate')->nullable();
            $table->string('offer')->default('no');
            $table->string('new_arrival')->default('no');
            $table->string('apodosi_xromatos')->nullable();
            $table->string('skey')->nullable();
            $table->decimal('weight', 10, 2)->default(0); // Fixed typo from 'weihgt'
            $table->string('size')->nullable();
            $table->integer('minquantity')->default(1);
            $table->decimal('store_profit', 10, 2)->default(0); // Changed to decimal
            $table->decimal('wholesaler_profit', 10, 2)->default(0); // Changed to decimal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products'); // Fixed trailing space
    }
};
