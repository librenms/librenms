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
        Schema::create('entPhysical_state', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id');
            $table->string('entPhysicalIndex', 64);
            $table->string('subindex', 64)->nullable();
            $table->string('group', 64);
            $table->string('key', 64);
            $table->string('value');
            $table->index(['device_id', 'entPhysicalIndex'], 'device_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('entPhysical_state');
    }
};
