<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LibreNMS\Util\Notifications;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The proxmox application is replaced by the Proxmox API polling method and the vminfo module.
     */
    public function up(): void
    {
        $hadVms = Schema::hasTable('proxmox') && DB::table('proxmox')->exists();

        Schema::dropIfExists('proxmox');
        Schema::dropIfExists('proxmox_ports');

        $appIds = DB::table('applications')->where('app_type', 'proxmox')->pluck('app_id');
        DB::table('application_metrics')->whereIn('app_id', $appIds)->delete();
        DB::table('applications')->whereIn('app_id', $appIds)->delete();

        DB::table('config')->where('config_name', 'enable_proxmox')->delete();

        if ($hadVms || $appIds->isNotEmpty()) {
            Notifications::create(
                'Proxmox application removed',
                'The Proxmox application has been replaced and its data removed, those devices are no longer collecting Proxmox data. '
                . 'To monitor Proxmox VE, an administrator needs to add the Proxmox API polling method to each host. It collects CPU, memory, disk and network usage of VMs and LXC containers. '
                . 'Existing Proxmox traffic graphs are not carried over. The proxmox agent script can be removed from the hosts. '
                . 'See https://docs.librenms.org/Extensions/Proxmox/',
                'migration',
                2,
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('proxmox', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('device_id')->default(0);
            $table->integer('vmid');
            $table->string('cluster');
            $table->string('description')->nullable();
            $table->timestamp('last_seen')->useCurrent();
            $table->unique(['cluster', 'vmid']);
        });

        Schema::create('proxmox_ports', function (Blueprint $table): void {
            $table->increments('id');
            $table->integer('vm_id');
            $table->string('port', 10);
            $table->timestamp('last_seen')->useCurrent();
            $table->unique(['vm_id', 'port']);
        });
    }
};
