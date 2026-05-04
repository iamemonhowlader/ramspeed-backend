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
        Schema::create('rma_repairs', function (Blueprint $table) {
            $table->integer('id');
            $table->string('ticket_number');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->string('device_type');
            $table->string('device_type_other');
            $table->string('brand');
            $table->string('brand_other');
            $table->string('model');
            $table->string('password_type');
            $table->string('password_value');
            $table->text('accessories');
            $table->text('problem_description');
            $table->string('price');
            $table->text('signature');
            $table->string('status');
            $table->timestamp('created_at');
            $table->string('custom_status');
            $table->integer('delivered');
            $table->text('technician_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rma_repairs');
    }
};
