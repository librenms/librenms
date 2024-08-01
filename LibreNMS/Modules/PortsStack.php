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

        $ifIndexToPortIdMap = $os->getDevice()->ports()->pluck('port_id', 'ifIndex')->toArray();

        $portStacks = $data->mapTable(function ($data, $lowIfIndex, $highIfIndex) use ($ifIndexToPortIdMap) {
            if ($lowIfIndex == '0' || $highIfIndex == '0') {
                return null;  // we don't care about the default entries for ports that have stacking enabled
            }

            return new PortStack([
                'high_ifIndex' => $highIfIndex,
                'high_port_id' => $ifIndexToPortIdMap[$highIfIndex] ?? null,
                'low_ifIndex' => $lowIfIndex,
                'low_port_id' => $ifIndexToPortIdMap[$lowIfIndex] ?? null,
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

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): void
    {
        $device->portsStack()->delete();
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device)
    {
        return [
            'ports_stack' => $device->portsStack()
                ->orderBy('high_ifIndex')->orderBy('low_ifIndex')
                ->get(['high_ifIndex', 'low_ifIndex', 'ifStackStatus']),
        ];
    }
}
