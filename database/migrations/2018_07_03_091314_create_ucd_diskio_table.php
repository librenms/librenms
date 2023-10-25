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
        Schema::create('ucd_diskio', function (Blueprint $table) {
            $table->increments('diskio_id');
            $table->unsignedInteger('device_id')->index();
            $table->integer('diskio_index');
            $table->string('diskio_descr', 32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ucd_diskio');
    }
};
