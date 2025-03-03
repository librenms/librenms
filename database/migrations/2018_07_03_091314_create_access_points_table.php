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
        Schema::create('access_points', function (Blueprint $table) {
            $table->increments('accesspoint_id');
            $table->unsignedInteger('device_id');
            $table->string('name');
            $table->tinyInteger('radio_number')->nullable();
            $table->string('type', 16);
            $table->string('mac_addr', 24);
            $table->boolean('deleted')->default(0)->index();
            $table->tinyInteger('channel')->unsigned()->default(0);
            $table->tinyInteger('txpow')->default(0);
            $table->tinyInteger('radioutil')->default(0);
            $table->smallInteger('numasoclients')->default(0);
            $table->smallInteger('nummonclients')->default(0);
            $table->tinyInteger('numactbssid')->default(0);
            $table->tinyInteger('nummonbssid')->default(0);
            $table->unsignedTinyInteger('interference');
            $table->index(['name', 'radio_number'], 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('access_points');
    }
};
