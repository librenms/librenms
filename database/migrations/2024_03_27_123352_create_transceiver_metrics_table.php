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
        Schema::create('transceiver_metrics', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('device_id');
            $table->bigInteger('transceiver_id');
            $table->smallInteger('channel')->default(0);
            $table->string('type', 16);
            $table->string('oid');
            $table->float('value')->nullable();
            $table->float('value_prev')->nullable();
            $table->integer('multiplier')->default(1);
            $table->integer('divisor')->default(1);
            $table->tinyInteger('status')->default(0);
            $table->string('transform_function')->nullable();
            $table->float('threshold_min_critical')->nullable();
            $table->float('threshold_min_warning')->nullable();
            $table->float('threshold_max_warning')->nullable();
            $table->float('threshold_max_critical')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transceiver_metrics');
    }
};
