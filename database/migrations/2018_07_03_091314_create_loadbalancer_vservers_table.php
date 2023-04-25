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
        Schema::create('loadbalancer_vservers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('classmap_id');
            $table->string('classmap', 128);
            $table->string('serverstate', 64);
            $table->unsignedInteger('device_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('loadbalancer_vservers');
    }
};
