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
use App\Models\Eventlog;
use App\Models\Port;
use App\Models\PortStack;
use App\Observers\ModuleModelObserver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ConnectivityHelper;
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
    public function shouldDiscover(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status, ConnectivityHelper $connectivity): bool
    {
        return $status->isEnabled() && $connectivity->snmpIsAvailable();
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $this->sync($os, preserveMissing: false);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $this->sync($os, preserveMissing: true);
    }

    /**
     * Discovery reconciles membership against what the device reports now.
     * Polling refreshes it the same way, except that LAG-MIB members which have
     * dropped out of their bundle are kept as notInService until the next discovery.
     */
    private function sync(OS $os, bool $preserveMissing): void
    {
        $device = $os->getDevice();
        $data = \SnmpQuery::enumStrings()->walk('IF-MIB::ifStackStatus');
        $existing = null;

        if ($data->isValid()) {
            $portStacks = $data->mapTable(function ($data, $lowIfIndex, $highIfIndex = null) use ($device) {
                if ($highIfIndex === null) {
                    Log::debug('Skipping ' . $lowIfIndex . ' due to bad table index from the device');

                    return null;
                }
                if ($lowIfIndex == '0' || $highIfIndex == '0') {
                    return null;  // we don't care about the default entries for ports that have stacking enabled
                }

                return new PortStack([
                    'high_ifIndex' => $highIfIndex,
                    'high_port_id' => PortCache::getIdFromIfIndex($highIfIndex, $device),
                    'low_ifIndex' => $lowIfIndex,
                    'low_port_id' => PortCache::getIdFromIfIndex($lowIfIndex, $device),
                    'ifStackStatus' => $data['IF-MIB::ifStackStatus'],
                ]);
            });
        } else {
            // Fall back to IEEE 802.3ad LAG-MIB; ifStackTable is not implemented on some platforms (e.g. Cisco NX-OS).
            $data = \SnmpQuery::walk('IEEE8023-LAG-MIB::dot3adAggPortSelectedAggID');
            if (! $data->isValid()) {
                return;
            }
            $portStacks = $data->mapTable(function ($row, $memberIfIndex) use ($device) {
                $aggregator = (int) ($row['IEEE8023-LAG-MIB::dot3adAggPortSelectedAggID'] ?? 0);
                if ($aggregator === 0) {
                    return null;
                }

                return new PortStack([
                    'high_ifIndex' => $aggregator,
                    'high_port_id' => PortCache::getIdFromIfIndex($aggregator, $device),
                    'low_ifIndex' => $memberIfIndex,
                    'low_port_id' => PortCache::getIdFromIfIndex($memberIfIndex, $device),
                    'ifStackStatus' => 'active',
                ]);
            })->filter();

            $existing = $device->portsStack()->get();
            $portStacks = $this->reconcileLagMembers($device, $existing, $portStacks, $preserveMissing);
        }

        ModuleModelObserver::observe(PortStack::class);
        $this->syncModels($device, 'portsStack', $portStacks->filter(), $existing);
    }

    /**
     * The LAG-MIB drops a member's row the moment it leaves the bundle, so a plain sync
     * would delete it and lose the only signal an alert rule can match. While polling,
     * a missing member is kept as notInService. At discovery a member that is still
     * missing is deleted. Every transition is written to the eventlog.
     */
    private function reconcileLagMembers(Device $device, Collection $existing, Collection $current, bool $preserveMissing): Collection
    {
        $seen = $current->keyBy(fn (PortStack $stack) => (int) $stack->low_ifIndex);

        foreach ($existing as $row) {
            $member = (int) $row->low_ifIndex;

            if ($seen->has($member)) {
                if ($row->ifStackStatus == 'notInService') {
                    $this->logMember($device, $seen->get($member), 'LAG member %s rejoined %s', Severity::Info);
                }
                continue;
            }

            if (! $preserveMissing) {
                // not pushed to $current, so syncModels deletes the row
                $this->logMember($device, $row, 'LAG member %s not in %s at discovery, removed', Severity::Notice);
                continue;
            }

            if ($row->ifStackStatus != 'notInService') {
                $this->logMember($device, $row, 'LAG member %s dropped out of %s', Severity::Warning);
            }

            $current->push(new PortStack([
                'high_ifIndex' => $row->high_ifIndex,
                'high_port_id' => PortCache::getIdFromIfIndex($row->high_ifIndex, $device) ?? $row->high_port_id,
                'low_ifIndex' => $row->low_ifIndex,
                'low_port_id' => PortCache::getIdFromIfIndex($row->low_ifIndex, $device) ?? $row->low_port_id,
                'ifStackStatus' => 'notInService',
            ]));
        }

        return $current;
    }

    private function logMember(Device $device, PortStack $stack, string $format, Severity $severity): void
    {
        $message = sprintf(
            $format,
            $this->portLabel($stack->low_port_id, $stack->low_ifIndex),
            $this->portLabel($stack->high_port_id, $stack->high_ifIndex),
        );

        Eventlog::log($message, $device, 'interface', $severity, $stack->low_port_id);
    }

    private function portLabel(?int $port_id, int $ifIndex): string
    {
        return Port::find($port_id)?->getShortLabel() ?? "ifIndex $ifIndex";
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
        return [
            'ports_stack' => $device->portsStack()
                ->orderBy('high_ifIndex')->orderBy('low_ifIndex')
                ->get(['high_ifIndex', 'low_ifIndex', 'ifStackStatus']),
        ];
    }
}
