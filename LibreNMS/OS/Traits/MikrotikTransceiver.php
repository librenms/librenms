<?php

/**
 * MikrotikTransceiver.php
 *
 * Shared MikroTik transceiver data normalization.
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

use LibreNMS\Util\Number;

trait MikrotikTransceiver
{
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
        // GDiv100 expresses the value in nanometers; normal optical modules are below 2000 nm.
        if ($wavelength !== null && ($wavelength <= 0 || $wavelength > 2000 || $wavelength >= 65535)) {
            $wavelength = null;
        }

        $distance = isset($data['MIKROTIK-MIB::mtxrOpticalLinkLengthCopperOM4'])
            ? (int) Number::cast($data['MIKROTIK-MIB::mtxrOpticalLinkLengthCopperOM4'])
            : null;
        if ($distance !== null && $distance <= 0) {
            $distance = null;
        }

        $transceiver = [
            'type' => $this->mikrotikTransceiverType($data['MIKROTIK-MIB::mtxrOpticalType'] ?? null),
            'vendor' => $this->mikrotikTransceiverString($data['MIKROTIK-MIB::mtxrOpticalVendorName'] ?? null),
            'model' => $this->mikrotikTransceiverString($data['MIKROTIK-MIB::mtxrOpticalVendorPartNumber'] ?? null),
            'serial' => $this->mikrotikTransceiverString($data['MIKROTIK-MIB::mtxrOpticalVendorSerial'] ?? null),
            'distance' => $distance,
            'wavelength' => $wavelength,
            'connector' => $this->mikrotikTransceiverConnector($data['MIKROTIK-MIB::mtxrOpticalConnectorType'] ?? null),
        ];

        // Older RouterOS/SwOS agents do not expose modulePresent and report every cage.
        // Keep any legacy row with an indication of occupancy, even when its wavelength
        // is invalid (for example, a DAC reported as 16654 or 256).
        $hasLegacyWavelength = $rawWavelength !== null && ! in_array(trim((string) $rawWavelength), [
            '', '.00', '0', '0.00', '65535', '65535.00', '42949671.68',
        ], true);
        $hasLegacyDiagnostics = ($data['MIKROTIK-MIB::mtxrOpticalTxBiasCurrent'] ?? '0') != '0' ||
            ($data['MIKROTIK-MIB::mtxrOpticalSupplyVoltage'] ?? '.000') != '.000' ||
            ! in_array((string) ($data['MIKROTIK-MIB::mtxrOpticalTemperature'] ?? '4294967168'), ['4294967168', '65408'], true);

        if ($modulePresent === null && $transceiver['vendor'] === null && $transceiver['serial'] === null &&
            ! $hasLegacyWavelength && ! $hasLegacyDiagnostics) {
            return null;
        }

        return $transceiver;
    }

    private function mikrotikTransceiverType(mixed $type): ?string
    {
        return match ((string) $type) {
            '1', 'gbic' => 'GBIC',
            '2', 'soldered' => 'Soldered',
            '3', 'sfp' => 'SFP',
            '11', 'dwdmSfp' => 'DWDM-SFP',
            '12', 'qsfp' => 'QSFP',
            '13', 'qsfpPlus' => 'QSFP+',
            '17', 'qsfp28' => 'QSFP28',
            '24', 'qsfpDD' => 'QSFP-DD',
            '30', 'qsfpCmis' => 'QSFP-CMIS',
            default => null,
        };
    }

    private function mikrotikTransceiverConnector(mixed $connector): ?string
    {
        return match ((string) $connector) {
            '1', 'sc' => 'SC',
            '7', 'lc' => 'LC',
            '11', 'opticalPigtail' => 'AOC',
            '12', 'mpo1x12', '39', 'mpo2x12' => 'MPO-12',
            '13', 'mpo2x16', '40', 'mpo1x16' => 'MPO-16',
            '32', 'hssdc2' => 'HSSDC',
            '33', 'copperPigtail' => 'DAC',
            '34', 'rj45' => 'RJ45',
            '35', 'noSeparableConnector' => 'None',
            default => null,
        };
    }

    private function mikrotikTransceiverString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
