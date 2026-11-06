<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Vminfo;
use Illuminate\Database\Seeder;

class VminfoSeeder extends Seeder
{
    /**
     * Seed vminfo test data.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('VminfoSeeder: Skipping execution in production.');

            return;
        }

        $devices = Device::take(5)->get();

        if ($devices->isEmpty()) {
            $devices = Device::factory()->count(2)->create();
        }

        foreach ($devices as $device) {
            Vminfo::factory()->count(4)->create([
                'device_id' => $device->device_id,
            ]);
        }

        // Link at least one VM to a child device if multiple devices exist
        if ($devices->count() > 1) {
            $host = $devices->first();
            $guest = $devices->last();

            Vminfo::factory()->on()->create([
                'device_id' => $host->device_id,
                'vmwVmDisplayName' => $guest->hostname,
            ]);
        }
    }
}
