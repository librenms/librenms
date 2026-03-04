<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill device_id and ifIndex from ports
        DB::table('mac_accounting')
            ->whereNull('device_id')
            ->orWhereNull('ifIndex')
            ->get()
            ->each(function ($row) {
                $port = DB::table('ports')->where('port_id', $row->port_id)->first(['device_id', 'ifIndex']);
                if ($port) {
                    DB::table('mac_accounting')
                        ->where('ma_id', $row->ma_id)
                        ->update([
                            'device_id' => $port->device_id,
                            'ifIndex' => $port->ifIndex,
                        ]);
                }
            });

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
