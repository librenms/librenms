<?php

/*
 * PortsVoiceVlan.php
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
 * @copyright  2025 Michael Adams
 * @author     Michael Adams <mradams@ilstu.edu>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\PortsVoiceVlanDiscovery;
use LibreNMS\Interfaces\Module;
use LibreNMS\Interfaces\Polling\PortsVoiceVlanPolling;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class PortsVoiceVlan implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortsVoiceVlanDiscovery;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $this->poll($os, app('Datastore'));
    }

    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice()) && $os instanceof PortsVoiceVlanPolling;
    }

    /**
     * Poll data for this module and update the DB
     *
     * @param  \LibreNMS\OS  $os
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        if ($os instanceof PortsVoiceVlanPolling) {
            $device = $os->getDevice();
            $port_voice_vlan = $os->pollPortsVoiceVlan($os, $device);
            ModuleModelObserver::observe(\App\Models\PortsVoiceVlan::class);
            $this->syncModels($device, 'portsVoiceVlan', $port_voice_vlan);
        }
    }

    public function dataExists(Device $device): bool
    {
        return $device->portsVoiceVlan()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->portsVoiceVlan()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return [
            'PortsVoiceVlan' => $device->portsVoiceVlan()->orderBy('port_id')
                ->get()->map->makeHidden(['id', 'device_id']),
        ];
    }
}
