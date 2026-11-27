<?php

/**
 * MikrotikTransceiver.php
 *
 * Shared MikroTik transceiver discovery and data normalization.
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
 * @copyright  2026 LibreNMS
 */

namespace LibreNMS\OS\Traits;

use App\Facades\PortCache;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Util\Number;
use SnmpQuery;

trait MikrotikTransceiver
{
    /**
     * @return Collection<int, Transceiver>
     */
    public function discoverTransceivers(): Collection
    {
        return SnmpQuery::enumStrings()->walk('MIKROTIK-MIB::mtxrOpticalTable')->mapTable(function ($data, $ifIndex) {
            $transceiver = $this->parseMikrotikTransceiver($data);
            if ($transceiver === null) {
                return null;
            }

            return new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $ifIndex,
                'entity_physical_index' => $ifIndex,
                ...$transceiver,
            ]);
        })->filter();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{type: ?string, vendor: ?string, model: ?string, serial: ?string, distance: ?int, wavelength: float|int|null, connector: ?string}|null
     */
    protected function parseMikrotikTransceiver(array $data): ?array
    {
        $modulePresent = $data['MIKROTIK-MIB::mtxrOpticalModulePresent'] ?? null;
        if (in_array($modulePresent, [false, 0, '0', 'false'], true)) {
            return null;
        }

        $rawWavelength = $data['MIKROTIK-MIB::mtxrOpticalWavelength'] ?? null;
        $wavelength = $rawWavelength !== null
            ? Number::cast($rawWavelength)
            : null;
        // GDiv100 expresses the value in nanometers; real optics are roughly 600-2000 nm.
        // Values below 600 (e.g. DAC garbage 256) or above 2000 (e.g. 16654) are invalid.
        if ($wavelength !== null && ($wavelength < 600 || $wavelength > 2000)) {
            $wavelength = null;
        }

        $distance = isset($data['MIKROTIK-MIB::mtxrOpticalLinkLengthCopperOM4'])
            ? (int) Number::cast($data['MIKROTIK-MIB::mtxrOpticalLinkLengthCopperOM4'])
            : null;
        if ($distance !== null && $distance <= 0) {
            $distance = null;
        }

        $transceiver = [
            'type' => $this->mikrotikTransceiverEnum($data['MIKROTIK-MIB::mtxrOpticalType'] ?? null),
            'vendor' => $this->mikrotikTransceiverString($data['MIKROTIK-MIB::mtxrOpticalVendorName'] ?? null),
            'model' => $this->mikrotikTransceiverString($data['MIKROTIK-MIB::mtxrOpticalVendorPartNumber'] ?? null),
            'serial' => $this->mikrotikTransceiverString($data['MIKROTIK-MIB::mtxrOpticalVendorSerial'] ?? null),
            'distance' => $distance,
            'wavelength' => $wavelength,
            'connector' => $this->mikrotikTransceiverEnum($data['MIKROTIK-MIB::mtxrOpticalConnectorType'] ?? null),
        ];

        // Older RouterOS/SwOS agents do not expose modulePresent and report every cage.
        // Keep any legacy row with an indication of occupancy, even when its wavelength
        // is invalid (for example, a DAC reported as 16654 or 256).
        $hasLegacyWavelength = $rawWavelength !== null && ! in_array(Number::cast($rawWavelength), [0, 65535, 42949671.68], true);
        $temperature = (string) ($data['MIKROTIK-MIB::mtxrOpticalTemperature'] ?? '4294967168');
        $hasLegacyDiagnostics = ($data['MIKROTIK-MIB::mtxrOpticalTxBiasCurrent'] ?? '0') != '0' ||
            ($data['MIKROTIK-MIB::mtxrOpticalSupplyVoltage'] ?? '.000') != '.000' ||
            ! in_array($temperature, ['4294967168', '65408', '0', '0.00', '.00', ''], true);

        if ($modulePresent === null && $transceiver['vendor'] === null && $transceiver['serial'] === null &&
            ! $hasLegacyWavelength && ! $hasLegacyDiagnostics) {
            return null;
        }

        return $transceiver;
    }

    /**
     * Keep MIB enum strings from enumStrings(); treat unknown/empty/numeric as null.
     */
    private function mikrotikTransceiverEnum(mixed $value): ?string
    {
        $value = trim((string) $value);

        // enumStrings() yields names like sfp/lc; bare integers are unresolved → null
        if ($value === '' || $value === 'unknown' || $value === '0' || ctype_digit($value)) {
            return null;
        }

        return $value;
    }

    private function mikrotikTransceiverString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
