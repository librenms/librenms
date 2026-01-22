<?php

/**
 * Bdcom.php
 *
 * BDCOM OS
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
 * @copyright  2025 Frederik Kriewitz
 * @copyright  2025 Peca Nesovanovic
 * @author     Frederik Kriewitz <frederik@kriewitz.eu>
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Link;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Interfaces\Discovery\LinkDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Bdcom extends OS implements TransceiverDiscovery, LinkDiscovery
{
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::cache()->walk('NMS-IF-MIB::ifSfpParameterTable')->mapTable(function ($data, $index) {
            $ifIndex = $data['NMS-IF-MIB::ifSfpIndex'];
            if ($data['NMS-IF-MIB::sfpPresentStatus'] != 1) {
                return null;
            }

            $types = [
                1 => 'SFP',
                2 => 'DAC',
                3 => null,
            ];

            $type = $types[$data['NMS-IF-MIB::type']] ?? 'unknown type ' . $data['NMS-IF-MIB::type'];

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $type,
                'vendor' => $data['NMS-IF-MIB::vendname'] ?? null,
                'model' => $data['NMS-IF-MIB::vendorPN'] ?? null,
                'serial' => $data['NMS-IF-MIB::sfpSeqNum'] ?? null,
                'distance' => $data['NMS-IF-MIB::transDist'] ?? null,
                'wavelength' => $data['NMS-IF-MIB::waveLen'] ?? null,
            ]);
        })->filter();
    }

    public function discoverLinks(): Collection
    {
        $links = new Collection;

        Log::info('NMS-LLDP-MIB:');
        $lldp_array = SnmpQuery::hideMib()->walk('NMS-LLDP-MIB::lldpRemoteSystemsData')->table(2);
        foreach ($lldp_array as $lldp_array_inner) {
            foreach ($lldp_array_inner as $lldp) {
                $interface = PortCache::getByIfIndex($lldp['lldpRemLocalPortNum'] ?? null, $this->getDeviceId());
                $remote_device_id = find_device_id($lldp['lldpRemSysName'] ?? null);
                if ($interface['port_id'] && $lldp['lldpRemSysName'] && $lldp['lldpRemPortId']) {
                    $remote_port_id = find_port_id($lldp['lldpRemPortDesc'], $lldp['lldpRemPortId'], $remote_device_id);
                    $links->push(new Link([
                        'local_port_id' => $interface['port_id'],
                        'remote_hostname' => $lldp['lldpRemSysName'],
                        'remote_device_id' => $remote_device_id,
                        'remote_port_id' => $remote_port_id,
                        'active' => 1,
                        'protocol' => 'lldp',
                        'remote_port' => $lldp['lldpRemPortId'] ?? '',
                        'remote_platform' => null,
                        'remote_version' => $lldp['lldpRemSysDesc'] ?? '',
                    ]));
                }
            }
        }

        return $links->filter();
    }
}
