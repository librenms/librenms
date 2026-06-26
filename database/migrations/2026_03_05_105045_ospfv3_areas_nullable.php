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
        Schema::table('ospfv3_areas', function (Blueprint $table) {
            $table->unsignedInteger('ospfv3AreaStubMetric')->nullable()->change();
            $table->string('ospfv3AreaStubMetricType', 32)->nullable()->change();
            $table->string('ospfv3AreaNssaTranslatorRole', 32)->nullable()->change();
            $table->string('ospfv3AreaNssaTranslatorState', 32)->nullable()->change();
            $table->unsignedInteger('ospfv3AreaNssaTranslatorStabInterval')->nullable()->change();
            $table->unsignedInteger('ospfv3AreaNssaTranslatorEvents')->nullable()->change();
            $table->string('ospfv3AreaTEEnabled', 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ospfv3_areas', function (Blueprint $table) {
            $table->unsignedInteger('ospfv3AreaStubMetric')->change();
            $table->string('ospfv3AreaStubMetricType', 32)->change();
            $table->string('ospfv3AreaNssaTranslatorRole', 32)->change();
            $table->string('ospfv3AreaNssaTranslatorState', 32)->change();
            $table->unsignedInteger('ospfv3AreaNssaTranslatorStabInterval')->change();
            $table->unsignedInteger('ospfv3AreaNssaTranslatorEvents')->change();
            $table->string('ospfv3AreaTEEnabled', 32)->change();
        });
    }
};
