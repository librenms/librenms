<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mdadm_arrays', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id')->index();
            $table->unsignedInteger('app_id')->index();
            $table->string('uuid', 36);
            $table->string('array_name')->nullable();
            $table->string('name')->nullable();
            $table->string('level', 32)->nullable();
            $table->string('state', 64)->nullable();
            $table->unsignedSmallInteger('active_devices')->nullable();
            $table->unsignedSmallInteger('working_devices')->nullable();
            $table->unsignedSmallInteger('spare_devices')->nullable();
            $table->unsignedSmallInteger('failed_devices')->nullable();
            $table->unsignedSmallInteger('degraded')->nullable();
            $table->unsignedInteger('mismatch_cnt')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('raid_disks')->nullable();
            $table->string('metadata_version', 32)->nullable();
            $table->string('consistency_policy', 64)->nullable();
            $table->unsignedInteger('chunk_size')->nullable();
            $table->string('sync_action', 32)->nullable();
            $table->float('sync_completed_pct')->nullable();
            $table->unsignedBigInteger('sync_speed_bps')->nullable();
            $table->unsignedBigInteger('sync_speed_min_bps')->nullable();
            $table->unsignedBigInteger('sync_speed_max_bps')->nullable();
            $table->unsignedBigInteger('sync_done_bytes')->nullable();
            $table->unsignedBigInteger('sync_total_bytes')->nullable();
            $table->string('sync_last_action', 32)->nullable();
            $table->timestamps();

            $table->unique(['app_id', 'uuid']);
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
        });

        Schema::create('mdadm_drives', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id')->index();
            $table->unsignedInteger('app_id')->index();
            $table->unsignedBigInteger('mdadm_array_id')->index();
            $table->string('dev_id', 64);
            $table->string('path')->nullable();
            $table->string('state', 64)->nullable();
            $table->json('state_flags')->nullable();
            $table->unsignedInteger('errors')->nullable();
            $table->boolean('is_missing')->default(false);
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('device_role', 64)->nullable();
            $table->unsignedSmallInteger('slot')->nullable();
            $table->string('id_model', 128)->nullable();
            $table->string('id_serial_short', 64)->nullable();
            $table->timestamps();

            $table->unique(['mdadm_array_id', 'dev_id']);
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
            $table->foreign('mdadm_array_id')->references('id')->on('mdadm_arrays')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mdadm_drives');
        Schema::dropIfExists('mdadm_arrays');
    }
};
