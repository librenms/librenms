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
        Schema::create('ciscoASA', function (Blueprint $table) {
            $table->increments('ciscoASA_id');
            $table->unsignedInteger('device_id')->index();
            $table->string('oid');
            $table->bigInteger('data');
            $table->bigInteger('high_alert');
            $table->bigInteger('low_alert');
            $table->tinyInteger('disabled')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ciscoASA');
    }
};
