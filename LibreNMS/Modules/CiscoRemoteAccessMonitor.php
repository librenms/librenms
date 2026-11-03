<?php

/*
 * CiscoRemoteAccessMonitor.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2026 Kevin Zink
 * @author     Kevin Zink <kevin.zink@mpi-hd.mpg.de>
 */

namespace LibreNMS\Modules;

use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\OS\Shared\Cisco;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use SnmpQuery;

class CiscoRemoteAccessMonitor implements Module
{
    use RequiresPollingMethod;

    /**
     * @inheritDoc
     */
    public function dependencies(): array
    {
        return [];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    public function shouldDiscover(OS $os): bool
    {
        return false;
    }

    public function discover(OS $os): void
    {
        // not implemented
    }

    public function shouldPoll(OS $os): bool
    {
        return $this->hasAvailableMethod($os) && $os instanceof Cisco;
    }

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  OS  $os
     * @param  DataStorageInterface  $datastore
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $oids = [
            'email' => 'CISCO-REMOTE-ACCESS-MONITOR-MIB::crasEmailNumSessions.0',
            'ipsec' => 'CISCO-REMOTE-ACCESS-MONITOR-MIB::crasIPSecNumSessions.0',
            'l2l' => 'CISCO-REMOTE-ACCESS-MONITOR-MIB::crasL2LNumSessions.0',
            'lb' => 'CISCO-REMOTE-ACCESS-MONITOR-MIB::crasLBNumSessions.0',
            'svc' => 'CISCO-REMOTE-ACCESS-MONITOR-MIB::crasSVCNumSessions.0',
            'webvpn' => 'CISCO-REMOTE-ACCESS-MONITOR-MIB::crasWebvpnNumSessions.0',
        ];

        $data = SnmpQuery::make()->get(array_values($oids))->values();

        $fields = [];
        foreach ($oids as $field => $oid) {
            $value = $data[$oid] ?? null;

            // Some ASAs return 'No Such Object available on this agent at this OID'
            // for crasEmailNumSessions.0. Clamp this to 0.
            if ($field == 'email' && ! is_numeric($value)) {
                $value = 0;
            }

            if (! is_numeric($value)) {
                return;
            }

            $fields[$field] = $value;
        }

        $datastore->put($os->getDeviceArray(), 'cras_sessions', [
            'rrd_def' => RrdDefinition::make()
                ->addDataset('email', 'GAUGE', 0)
                ->addDataset('ipsec', 'GAUGE', 0)
                ->addDataset('l2l', 'GAUGE', 0)
                ->addDataset('lb', 'GAUGE', 0)
                ->addDataset('svc', 'GAUGE', 0)
                ->addDataset('webvpn', 'GAUGE', 0),
        ], $fields);

        $os->enableGraph('cras_sessions');
    }

    public function dataExists(Device $device): bool
    {
        return false;
    }

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     */
    public function cleanup(Device $device): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function dump(Device $device, string $type): ?array
    {
        return null;
    }
}
