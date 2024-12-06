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
        Schema::create('sensors_to_state_indexes', function (Blueprint $table) {
            $table->increments('sensors_to_state_translations_id');
            $table->unsignedInteger('sensor_id');
            $table->unsignedInteger('state_index_id')->index();
            $table->unique(['sensor_id', 'state_index_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('sensors_to_state_indexes');
    }
};
