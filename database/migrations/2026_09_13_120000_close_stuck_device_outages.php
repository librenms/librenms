<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Close outages that stayed open after the device came back up.
     */
    public function up(): void
    {
        DB::table('device_outages')
            ->join('devices', 'devices.device_id', '=', 'device_outages.device_id')
            ->whereNull('device_outages.up_again') // only open outages
            ->where('devices.status', 1) // devices that are currently up
            ->select('device_outages.id', 'device_outages.device_id', 'device_outages.going_down')
            ->chunkById(250, function ($outages): void {
                foreach ($outages as $outage) {
                    DB::table('device_outages')
                        ->where('id', $outage->id)
                        ->update(['up_again' => $this->recoveryTime((int) $outage->device_id, (int) $outage->going_down)]);
                }
            }, 'device_outages.id', 'id');
    }

    /**
     * Find when the device came back up. needed to construct the actual end of the outage
     */
    private function recoveryTime(int $deviceId, int $goingDown): int
    {
        $recovered = DB::table('eventlog')
            ->where('device_id', $deviceId)
            ->where('type', 'up')
            ->where('datetime', '>', date('Y-m-d H:i:s', $goingDown))
            ->orderBy('datetime')
            ->value('datetime');

        return $recovered ? strtotime($recovered) : $goingDown;
    }

    public function down(): void
    {
    }
};
