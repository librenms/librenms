<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('ciscoASA');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('ciscoASA', function (Blueprint $table) {
            $table->increments('ciscoASA_id');
            $table->unsignedInteger('device_id')->index('ciscoasa_device_id_index');
            $table->string('oid');
            $table->bigInteger('data');
            $table->bigInteger('high_alert')->default(-1);
            $table->bigInteger('low_alert')->default(0);
            $table->tinyInteger('disabled')->default(0);
        });
    }
};
