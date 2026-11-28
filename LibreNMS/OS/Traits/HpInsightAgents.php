<?php

/**
 * HpInsightAgents.php
 *
 * Sensors of servers with HP Insight Management Agents
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Sensor;
use Illuminate\Support\Collection;
use SnmpQuery;

trait HpInsightAgents
{
    /**
     * HP Insight Management Agents answer for the server information
     */
    public function hasHpInsightAgents(): bool
    {
        return $this->hpServerInfo('cpqSiProductName') !== '' || $this->hpServerInfo('cpqSiSysSerialNum') !== '';
    }

    /**
     * A value from CPQSINFO-MIB
     */
    protected function hpServerInfo(string $name): string
    {
        return trim((string) SnmpQuery::cache()->mibDir('hp')->get("CPQSINFO-MIB::$name.0")->value());
    }

    /**
     * HP array controller drive states (CPQIDA-MIB)
     *
     * @return Collection<int, Sensor>
     */
    public function discoverHpInsightSensors(): Collection
    {
        $sensors = new Collection;
        $drives = SnmpQuery::mibDir('hp')->walk([
            'CPQIDA-MIB::cpqDaPhyDrvBay',
            'CPQIDA-MIB::cpqDaPhyDrvStatus',
            'CPQIDA-MIB::cpqDaPhyDrvSmartStatus',
        ])->valuesByIndex();

        foreach ([
            ['cpqDaPhyDrvStatus', '.1.3.6.1.4.1.232.3.2.5.1.1.6', 'Status', [
                ['value' => 0, 'generic' => 3, 'descr' => 'noDisk'],
                ['value' => 1, 'generic' => 3, 'descr' => 'other'],
                ['value' => 2, 'generic' => 0, 'descr' => 'ok'],
                ['value' => 3, 'generic' => 2, 'descr' => 'failed'],
                ['value' => 4, 'generic' => 2, 'descr' => 'predictiveFailure'],
                ['value' => 5, 'generic' => 1, 'descr' => 'erasing'],
                ['value' => 6, 'generic' => 1, 'descr' => 'eraseDone'],
                ['value' => 7, 'generic' => 1, 'descr' => 'eraseQueued'],
                ['value' => 8, 'generic' => 2, 'descr' => 'ssdWearOut'],
                ['value' => 9, 'generic' => 3, 'descr' => 'notAuthenticated'],
                ['value' => 10, 'generic' => 0, 'descr' => 'hotSpare'],
            ]],
            ['cpqDaPhyDrvSmartStatus', '.1.3.6.1.4.1.232.3.2.5.1.1.57', 'S.M.A.R.T.', [
                ['value' => 1, 'generic' => 3, 'descr' => 'other'],
                ['value' => 2, 'generic' => 0, 'descr' => 'ok'],
                ['value' => 3, 'generic' => 1, 'descr' => 'replaceDrive'],
                ['value' => 4, 'generic' => 1, 'descr' => 'replaceDriveSSDWearOut'],
            ]],
        ] as [$name, $oid, $descr, $states]) {
            foreach ($drives as $index => $drive) {
                if (! isset($drive["CPQIDA-MIB::$name"])) {
                    continue;
                }

                $sensors->push(new Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'state',
                    'sensor_oid' => "$oid.$index",
                    'sensor_index' => $index,
                    'sensor_type' => $name,
                    'sensor_descr' => 'Drive  ' . ($drive['CPQIDA-MIB::cpqDaPhyDrvBay'] ?? '') . " $descr",
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => 1,
                    'sensor_current' => $drive["CPQIDA-MIB::$name"],
                    'entPhysicalIndex' => $index,
                    'rrd_type' => 'GAUGE',
                ])->withStates($states));
            }
        }

        return $sensors;
    }
}
