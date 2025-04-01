<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('ospfv3_areas');
        Schema::create('ospfv3_areas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('device_id');
            $table->unsignedInteger('ospfv3_instance_id');
            $table->unsignedInteger('ospfv3AreaId');
            $table->string('ospfv3AreaImportAsExtern', 32);
            $table->unsignedInteger('ospfv3AreaSpfRuns');
            $table->unsignedInteger('ospfv3AreaBdrRtrCount');
            $table->unsignedInteger('ospfv3AreaAsBdrRtrCount');
            $table->unsignedInteger('ospfv3AreaScopeLsaCount');
            $table->unsignedInteger('ospfv3AreaScopeLsaCksumSum');
            $table->string('ospfv3AreaSummary', 32);
            $table->unsignedInteger('ospfv3AreaStubMetric');
            $table->string('ospfv3AreaStubMetricType', 32);
            $table->string('ospfv3AreaNssaTranslatorRole', 32);
            $table->string('ospfv3AreaNssaTranslatorState', 32);
            $table->unsignedInteger('ospfv3AreaNssaTranslatorStabInterval');
            $table->unsignedInteger('ospfv3AreaNssaTranslatorEvents');
            $table->string('ospfv3AreaTEEnabled', 32);
            $table->string('context_name', 128);
            $table->unique(['device_id', 'ospfv3AreaId', 'context_name']);
            $table->index('ospfv3_instance_id', 'ospfv3AreaId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ospfv3_areas');
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
};
