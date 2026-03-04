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
        // Backfill device_id and ifIndex from ports
        \DB::table('mac_accounting')
            ->join('ports', 'mac_accounting.port_id', '=', 'ports.port_id')
            ->whereNull('mac_accounting.device_id')
            ->orWhereNull('mac_accounting.ifIndex')
            ->update([
                'mac_accounting.device_id' => \DB::raw('ports.device_id'),
                'mac_accounting.ifIndex' => \DB::raw('ports.ifIndex'),
            ]);

        Schema::table('mac_accounting', function (Blueprint $table) {
            $table->unsignedBigInteger('device_id')->change();
            $table->unsignedInteger('ifIndex')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mac_accounting', function (Blueprint $table) {
            $table->unsignedBigInteger('device_id')->nullable()->change();
            $table->unsignedInteger('ifIndex')->nullable()->change();
        });
    }
};
