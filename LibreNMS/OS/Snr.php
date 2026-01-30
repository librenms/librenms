<?php

/**
 * Snr.php
 *
 * SNR Switch OS
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2025 hrtrd
 * @author     hrtrd <neoll4ik@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Snr extends OS implements TransceiverDiscovery
{
    public function discoverTransceivers(): Collection
    {
        // Walk entire ddmTranscBasicInfoTable and get raw values
        $rawData = SnmpQuery::cache()->walk('.1.3.6.1.4.1.40418.7.100.30.3.1')->values();

        // Parse OIDs manually - format: .1.3.6.1.4.1.40418.7.100.30.3.1.{subOid}.{ifIndex}
        // subOid: 1=index, 2=name, 3=serial, 4=vendor, 5=model, 6=type, 7=bitrate, 8=wavelength
        $byIfIndex = [];

        foreach ($rawData as $oid => $value) {
            // Extract last two numbers from OID
            if (preg_match('/\.(\d+)\.(\d+)$/', (string) $oid, $matches)) {
                $subOid = (int) $matches[1];
                $ifIndex = (int) $matches[2];
                $byIfIndex[$ifIndex][$subOid] = $value;
            }
        }

        $transceivers = collect();

        foreach ($byIfIndex as $ifIndex => $entry) {
            // 3 = serial
            $serial = $entry[3] ?? null;
            if (empty($serial) || $serial === 'NULL') {
                continue;
            }

            // 4 = vendor
            $vendor = ($entry[4] ?? null) === 'NULL' ? null : ($entry[4] ?? null);
            // 5 = model
            $model = ($entry[5] ?? null) === 'NULL' ? null : ($entry[5] ?? null);
            // 6 = type
            $type = ($entry[6] ?? null) === 'NULL' ? null : ($entry[6] ?? null);

            // 8 = wavelength - extract number from "1270nm"
            $wavelengthStr = $entry[8] ?? null;
            $wavelength = null;
            if ($wavelengthStr && $wavelengthStr !== 'NULL') {
                preg_match('/(\d+)/', (string) $wavelengthStr, $matches);
                $wavelength = isset($matches[1]) ? (int) $matches[1] : null;
            }

            $portId = PortCache::getIdFromIfIndex($ifIndex, $this->getDevice());

            $transceivers->push(new Transceiver([
                'port_id' => (int) $portId,
                'index' => (string) $ifIndex,
                'entity_physical_index' => $ifIndex,
                'type' => $type,
                'vendor' => $vendor,
                'model' => $model,
                'serial' => $serial,
                'wavelength' => $wavelength,
            ]));
        }

        return $transceivers;
    }
}
