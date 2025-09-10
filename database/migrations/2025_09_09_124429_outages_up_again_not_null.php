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
        // Set all up_again to 0
        DB::table('device_outages')->whereNull('up_again')->update(['up_again' => 0]);

        // Set up_again to down_at for devices with multiple up_again entries
        $devices = DB::table('device_outages')
            ->select('device_id')
            ->where('up_again', 0)
            ->groupBy('device_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('device_id');

        foreach ($devices as $deviceId) {
            $latestId = DB::table('device_outages')
                ->where('device_id', $deviceId)
                ->where('up_again', 0)
                ->orderByDesc('id')
                ->value('id');

            DB::table('device_outages')
                ->where('device_id', $deviceId)
                ->where('up_again', 0)
                ->where('id', '<>', $latestId)
                ->update([
                    'up_again' => DB::raw('down_at'),
                ]);
        }

        Schema::table('device_outages', function (Blueprint $table) {
            $table->unsignedBigInteger('going_down')->change();
            $table->unsignedBigInteger('up_again')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('device_outages', function (Blueprint $table) {
            $table->bigInteger('going_down')->change();
            $table->bigInteger('up_again')->nullable()->change();
        });
    }
};
