<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('device_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->default('')->unique();
            $table->string('desc')->default('');
            $table->text('pattern')->nullable();
            $table->text('params')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('device_groups');
    }
};
