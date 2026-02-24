<?php

/**
 * Vlans.php
 *
 * VLANs discovery module
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
 * @link       http://librenms.org
 *
 * @copyright  2025 Peca Nesovanovic
 * @copyright  2025 Tony Murray
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\PortVlan;
use App\Models\Vlan;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class Vlans implements Module
{
    use SyncsModels;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        if (defined('PHPUNIT_RUNNING')) {
            return false; // FIXME improve test suite to skip polling when polling data is null
        }

        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $vlans = $os->discoverVlans()->filter(fn (?Vlan $data) => ! empty($data->vlan_vlan))->each(function (Vlan $data): void {
            if (empty($data->vlan_name)) {
                $data->vlan_name = 'VLAN ' . $data->vlan_vlan; // default VLAN name
            }
        });

        ModuleModelObserver::observe(Vlan::class, 'VLANs');
        $vlans = $this->syncModels($os->getDevice(), 'vlans', $vlans);
        ModuleModelObserver::done();

        $ports = $os->discoverVlanPorts($vlans)->filter(fn (PortVlan $data) => ! empty($data->vlan) && ! empty($data->port_id))->each(function (PortVlan $data): void {
            $data->priority ??= 0;
            $data->state ??= 'unknown';
            $data->cost ??= 0;
        });

        ModuleModelObserver::observe(PortVlan::class, 'VLAN Ports');
        $this->syncModels($os->getDevice(), 'portsVlan', $ports);
        ModuleModelObserver::done();
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->discover($os);
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->vlans()->exists() || $device->portsVlan()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->vlans()->delete() + $device->portsVlan()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        // skip testing the poller as is the same as discovery.
        if ($type == 'poller') {
            return null;
        }

        return [
            'vlans' => $device->vlans()->orderBy('vlan_vlan')
                ->get()->map->makeHidden(['device_id', 'vlan_id']),
            'ports_vlans' => $device->portsVlan()
                ->orderBy('vlan')->orderBy('baseport')
                ->get()->map->makeHidden(['port_vlan_id', 'created_at', 'updated_at', 'device_id', 'port_id']),
        ];
    }
}
