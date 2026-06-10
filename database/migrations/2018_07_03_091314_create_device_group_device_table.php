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
        Schema::create('device_group_device', function (Blueprint $table) {
            $table->unsignedInteger('device_group_id')->unsigned()->index();
            $table->unsignedInteger('device_id')->unsigned()->index();
            $table->primary(['device_group_id', 'device_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('device_group_device');
    }
};
