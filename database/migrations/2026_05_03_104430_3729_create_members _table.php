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
        Schema::create('members', function (Blueprint $table) {
            $table->increments('id'); // Fixed
            $table->string('username')->unique();
            $table->string('password');
            $table->string('full_name');
            $table->string('email');
            $table->string('active')->default('yes');
            $table->string('b2b_approved')->default('no');
            $table->string('address')->nullable();
            $table->string('post_code')->nullable();
            $table->string('city')->nullable();
            $table->integer('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('skey')->nullable();
            $table->string('vat_num')->nullable();
            $table->string('company_reg_num')->nullable();
            $table->string('type')->nullable();
            $table->string('cperson')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members'); // Fixed
    }
};
