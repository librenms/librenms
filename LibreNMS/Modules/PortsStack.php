<?php
/**
 * PortsStack.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\PortStack;
use App\Observers\ModuleModelObserver;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

class PortsStack implements Module
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
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $data = \SnmpQuery::enumStrings()->walk('IF-MIB::ifStackStatus');

        if (! $data->isValid()) {
            return;
        }

        $portStacks = $data->mapTable(function ($data, $lowIfIndex, $highIfIndex) use ($os) {
            if ($lowIfIndex == '0' || $highIfIndex == '0') {
                return null;  // we don't care about the default entries for ports that have stacking enabled
            }

            return new PortStack([
                'high_ifIndex' => $highIfIndex,
                'high_port_id' => PortCache::getIdFromIfIndex($highIfIndex, $os->getDevice()),
                'low_ifIndex' => $lowIfIndex,
                'low_port_id' => PortCache::getIdFromIfIndex($lowIfIndex, $os->getDevice()),
                'ifStackStatus' => $data['IF-MIB::ifStackStatus'],
            ]);
        });

        ModuleModelObserver::observe(PortStack::class);
        $this->syncModels($os->getDevice(), 'portsStack', $portStacks->filter());
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
    }

    public function dataExists(Device $device): bool
    {
        return $device->portsStack()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->portsStack()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'poller') {
            return null;
        }

        return [
            'ports_stack' => $device->portsStack()
                ->orderBy('high_ifIndex')->orderBy('low_ifIndex')
                ->get(['high_ifIndex', 'low_ifIndex', 'ifStackStatus']),
        ];
    }
}
