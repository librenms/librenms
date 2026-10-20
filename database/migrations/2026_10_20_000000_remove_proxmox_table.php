<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * The proxmox application is replaced by the Proxmox API polling method and the vminfo module.
     */
    public function up(): void
    {
        Schema::dropIfExists('proxmox');

        $appIds = DB::table('applications')->where('app_type', 'proxmox')->pluck('app_id');
        DB::table('application_metrics')->whereIn('app_id', $appIds)->delete();
        DB::table('applications')->whereIn('app_id', $appIds)->delete();
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
    }
};
