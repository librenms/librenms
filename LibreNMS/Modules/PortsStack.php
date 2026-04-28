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
use Illuminate\Support\Facades\Log;
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

        if ($data->isValid()) {
            $portStacks = $data->mapTable(function ($data, $lowIfIndex, $highIfIndex = null) use ($os) {
                if ($highIfIndex === null) {
                    Log::debug('Skipping ' . $lowIfIndex . ' due to bad table index from the device');

                    return null;
                }

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
        } else {
            // IF-MIB::ifStackTable is the standard mechanism for discovering port-channel
            // (link-aggregation) topology, but some platforms - notably Cisco NX-OS - do not
            // expose it. For those, fall back to the IEEE 802.3ad LAG-MIB, which NX-OS and
            // most other modern switches do implement. dot3adAggPortSelectedAggID returns the
            // ifIndex of the aggregator each member port is configured to join (0 = not a
            // member), which gives us the same parent/child relationship ifStackTable would.
            $portStacks = $this->discoverFromLagMib($os);

            if ($portStacks === null) {
                return;
            }
        }

        ModuleModelObserver::observe(PortStack::class);
        $this->syncModels($os->getDevice(), 'portsStack', $portStacks->filter());
    }

    /**
     * Build PortStack models from IEEE8023-LAG-MIB::dot3adAggPortTable.
     *
     * Used as a fallback for devices that do not implement IF-MIB::ifStackTable.
     * Each entry's index is the member port ifIndex; the value is the ifIndex of
     * the aggregator (port-channel) the member is configured to join. Returns
     * null when the device exposes neither MIB so the caller can leave any
     * existing ports_stack rows untouched.
     *
     * @return \Illuminate\Support\Collection<int, PortStack>|null
     */
    private function discoverFromLagMib(OS $os): ?\Illuminate\Support\Collection
    {
        $data = \SnmpQuery::walk('IEEE8023-LAG-MIB::dot3adAggPortSelectedAggID');

        if (! $data->isValid()) {
            return null;
        }

        return $data->mapTable(function ($row, $memberIfIndex) use ($os) {
            $aggregatorIfIndex = (int) ($row['IEEE8023-LAG-MIB::dot3adAggPortSelectedAggID'] ?? 0);

            if ($aggregatorIfIndex === 0) {
                return null;  // port is not configured as a member of any aggregator
            }

            return new PortStack([
                'high_ifIndex' => $aggregatorIfIndex,
                'high_port_id' => PortCache::getIdFromIfIndex($aggregatorIfIndex, $os->getDevice()),
                'low_ifIndex' => $memberIfIndex,
                'low_port_id' => PortCache::getIdFromIfIndex($memberIfIndex, $os->getDevice()),
                'ifStackStatus' => 'active',
            ]);
        });
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
