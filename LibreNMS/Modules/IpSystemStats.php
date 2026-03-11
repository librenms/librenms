<?php

/**
 * IpSystemStats.php
 *
 * Poll IP-MIB::ipSystemStats
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
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;

class IpSystemStats implements Module
{
    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        // no discovery
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool
    {
        return $status->isEnabledAndDeviceUp($os->getDevice());
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $device = $os->getDeviceArray();
        $data = SnmpQuery::device($os->getDevice())
            ->enumStrings()
            ->walk('IP-MIB::ipSystemStatsTable')
            ->table(1);

        if (empty($data)) {
            return;
        }

        $oids = [
            'IP-MIB::ipSystemStatsInReceives',
            'IP-MIB::ipSystemStatsInHdrErrors',
            'IP-MIB::ipSystemStatsInAddrErrors',
            'IP-MIB::ipSystemStatsInUnknownProtos',
            'IP-MIB::ipSystemStatsInForwDatagrams',
            'IP-MIB::ipSystemStatsReasmReqds',
            'IP-MIB::ipSystemStatsReasmOKs',
            'IP-MIB::ipSystemStatsReasmFails',
            'IP-MIB::ipSystemStatsInDiscards',
            'IP-MIB::ipSystemStatsInDelivers',
            'IP-MIB::ipSystemStatsOutRequests',
            'IP-MIB::ipSystemStatsOutNoRoutes',
            'IP-MIB::ipSystemStatsOutDiscards',
            'IP-MIB::ipSystemStatsOutFragFails',
            'IP-MIB::ipSystemStatsOutFragCreates',
            'IP-MIB::ipSystemStatsOutForwDatagrams',
        ];

        $hcSubstitutions = [
            'IP-MIB::ipSystemStatsHCInReceives' => 'IP-MIB::ipSystemStatsInReceives',
            'IP-MIB::ipSystemStatsHCInForwDatagrams' => 'IP-MIB::ipSystemStatsInForwDatagrams',
            'IP-MIB::ipSystemStatsHCInDelivers' => 'IP-MIB::ipSystemStatsInDelivers',
            'IP-MIB::ipSystemStatsHCOutRequests' => 'IP-MIB::ipSystemStatsOutRequests',
            'IP-MIB::ipSystemStatsHCOutForwDatagrams' => 'IP-MIB::ipSystemStatsOutForwDatagrams',
        ];

        foreach ($data as $af => $stats) {
            Log::info("$af ");

            // Prefer HC (64-bit) counters over their 32-bit equivalents when available.
            foreach ($hcSubstitutions as $hc => $standard) {
                if (isset($stats[$hc])) {
                    $stats[$standard] = $stats[$hc];
                }
            }

            $rrd_def = new RrdDefinition();
            $fields = [];

            foreach ($oids as $oid) {
                $oid_ds = str_replace('IP-MIB::ipSystemStats', '', $oid);
                $value = $stats[$oid] ?? '0';

                // Treat invalid/missing enum strings as zero.
                if (str_contains($value, 'No') || str_contains($value, 'd') || str_contains($value, 's')) {
                    $value = '0';
                }

                $rrd_def->addDataset($oid_ds, 'COUNTER');
                $fields[$oid_ds] = $value;
            }

            $tags = ['af' => $af, 'rrd_name' => ['ipSystemStats', $af], 'rrd_def' => $rrd_def];
            $datastore->put($device, 'ipSystemStats', $tags, $fields);

            $os->enableGraph("ipsystemstats_$af");
            $os->enableGraph("ipsystemstats_{$af}_frag");
        }
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return false; // no database data
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return 0; // no cleanup
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return null; // no database data to dump (may add rrd later)
    }
}
