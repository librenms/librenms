<?php
/*
 * Transceivers.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\Transceiver;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class Transceivers implements Module
{
    use SyncsModels;

    public function dependencies(): array
    {
        return ['ports'];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof TransceiverDiscovery;
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    public function discover(OS $os): void
    {
        if ($os instanceof TransceiverDiscovery) {
            $discoveredTransceivers = $os->discoverTransceivers();

            // save transceivers
            ModuleModelObserver::observe(Transceiver::class);
            $this->syncModels($os->getDevice(), 'transceivers', $discoveredTransceivers);
        }
    }

    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
    }

    public function dataExists(Device $device): bool
    {
        return $device->transceivers()->exists();
    }

    public function cleanup(Device $device): int
    {
        return $device->transceivers()->delete();
    }

    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'poller') {
            return null;
        }

        return [
            'transceivers' => $device->transceivers()->orderBy('index')
                ->leftJoin('ports', 'transceivers.port_id', 'ports.port_id')
                ->select(['transceivers.*', 'ifIndex'])
                    ->get()->map->makeHidden(['id', 'created_at', 'updated_at', 'device_id', 'port_id']),
        ];
    }
}
