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
        Schema::create('alert_device_map', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rule_id');
            $table->unsignedInteger('device_id');
            $table->unique(['rule_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('alert_device_map');
    }
};
