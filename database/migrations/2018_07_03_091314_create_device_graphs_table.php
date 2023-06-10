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
        Schema::create('device_graphs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('device_id')->index();
            $table->string('graph')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('device_graphs');
    }
};
