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
            $table->unsignedInteger('snmp_index')->nullable();
            $table->string('uuid', 36);
            $table->string('array_name')->nullable();
            $table->string('md_id')->nullable();
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

            // Array meta (discovery): layout, resync/reshape progress, write-intent bitmap config.
            $table->integer('layout')->nullable();
            $table->unsignedBigInteger('resync_start_sectors')->nullable();
            $table->unsignedBigInteger('reshape_position_sectors')->nullable();
            $table->string('bitmap_type', 16)->nullable();
            $table->string('bitmap_location')->nullable();
            $table->unsignedInteger('bitmap_chunksize')->nullable();
            $table->string('bitmap_metadata', 32)->nullable();
            $table->unsignedInteger('bitmap_time_base')->nullable();

            // Array health (poll): mount/swap status, bitmap backlog, RAID-5/6 stripe cache + journal.
            $table->boolean('is_mounted')->nullable();
            $table->string('mount_points')->nullable();
            $table->boolean('is_swap')->nullable();
            $table->unsignedInteger('bitmap_backlog')->nullable();
            $table->unsignedInteger('bitmap_max_backlog')->nullable();
            $table->boolean('bitmap_can_clear')->nullable();
            $table->unsignedInteger('stripe_cache_size')->nullable();
            $table->unsignedInteger('stripe_cache_active')->nullable();
            $table->string('journal_mode', 16)->nullable();

            // Array sync (poll): check/repair scope range.
            $table->unsignedBigInteger('sync_min_sectors')->nullable();
            $table->unsignedBigInteger('sync_max_sectors')->nullable();
            $table->timestamps();

            $table->unique(['app_id', 'uuid']);
            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
        });

        Schema::create('mdadm_drives', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('device_id')->index();
            $table->unsignedInteger('app_id')->index();
            $table->unsignedBigInteger('mdadm_array_id')->index();
            $table->unsignedInteger('snmp_index')->nullable();
            $table->string('dev_id', 64);
            $table->string('path')->nullable();
            $table->string('state', 64)->nullable();
            $table->longText('state_flags')->nullable();
            $table->unsignedInteger('errors')->nullable();
            $table->boolean('is_missing')->default(false);
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('device_role', 64)->nullable();
            $table->unsignedSmallInteger('slot')->nullable();
            $table->string('id_model', 128)->nullable();
            $table->string('id_serial_short', 64)->nullable();

            // Device meta (discovery): component offset and Partial Parity Log location.
            $table->unsignedBigInteger('offset_sectors')->nullable();
            $table->unsignedBigInteger('ppl_sector')->nullable();
            $table->unsignedBigInteger('ppl_size_sectors')->nullable();

            // Device health (poll): superblock event count, rebuild resume point, bad-block logs.
            $table->unsignedBigInteger('events')->nullable();
            $table->unsignedBigInteger('recovery_start_sectors')->nullable();
            $table->unsignedInteger('bad_block_count')->nullable();
            $table->unsignedInteger('unack_bad_block_count')->nullable();
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
