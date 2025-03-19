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
        Schema::create('ospfv3_areas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->string('ospfv3AreaId', 32);
            $table->string('ospfv3AreaImportAsExtern', 128);
            $table->integer('ospfv3AreaSpfRuns');
            $table->integer('ospfv3AreaBdrRtrCount');
            $table->integer('ospfv3AreaAsBdrRtrCount');
            $table->integer('ospfv3AreaScopeLsaCount');
            $table->integer('ospfv3AreaScopeLsaCksumSum');
            $table->string('ospfv3AreaSummary', 64);
            $table->string('ospfv3AreaStubMetric', 64);
            $table->string('ospfv3AreaStubMetricType', 64);
            $table->string('context_name', 128)->nullable();
            $table->unique(['device_id', 'ospfv3AreaId', 'context_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::drop('ospfv3_areas');
    }
};
