<?php

/**
 * ifotec.inc.php
 *
 * LibreNMS os poller module for Ifotec devices
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
 * @copyright  LibreNMS contributors
 * @author     Cedric MARMONIER
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Device;
use App\Models\EntPhysical;
use App\Models\Port;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Ifotec extends OS implements OSDiscovery, TransceiverDiscovery
{
    // IfotecSFPConnectorType (IFOTEC-SMI)
    private const SFP_CONNECTORS = [1 => 'undefined', 2 => 'SC', 3 => 'LC', 4 => 'RJ45', 5 => 'DAC'];

    // -10000000 (-1000000.0 after the IfotecTenthDbm display hint) means minus infinity: no optical signal
    public const OPTICAL_POWER_MIN_VALID = -1000.0;

    private const OPTICAL_POWER_FLOOR = -40.0;

    // the device reports minus infinity when the power is 3 dB under the low alarm threshold
    private const OPTICAL_POWER_INFINITY_MARGIN = 3.0;

    // IfotecYesNoType and IfotecDataStatus (IFOTEC-SMI): value => [name, generic state]
    private const YES_NO = [1 => ['yes', 0], 2 => ['no', 3]];

    private const DATA_STATUS = [
        1 => ['noData', 3], 2 => ['ok', 0], 3 => ['warning', 1], 4 => ['warningLowThreshold', 1],
        5 => ['warningHighThreshold', 1], 6 => ['error', 2], 7 => ['errorLowThreshold', 2],
        8 => ['errorHighThreshold', 2], 9 => ['errorWrongData', 2], 10 => ['errorLowDataOverflow', 2],
        11 => ['errorHighDataOverflow', 2],
    ];

    private ?array $portEntities = null;

    private ?array $portIfIndexes = null;

    public static function minusInfinityPower(float|string|null $lowLimit): float
    {
        return is_numeric($lowLimit) ? (float) $lowLimit - self::OPTICAL_POWER_INFINITY_MARGIN : self::OPTICAL_POWER_FLOOR;
    }

    public function discoverOS(Device $device): void
    {
        if (Str::startsWith($device->sysObjectID, '.1.3.6.1.4.1.21362.100.')) {
            $ifoSysProductIndex = snmp_get($this->getDeviceArray(), 'ifoSysProductIndex.0', '-Oqv', 'IFOTEC-SMI');

            if ($ifoSysProductIndex !== false) {
                $oids = [
                    'ifoSysSerialNumber.' . $ifoSysProductIndex,
                    'ifoSysFirmware.' . $ifoSysProductIndex,
                    'ifoSysBootloader.' . $ifoSysProductIndex,
                ];
                $data = snmp_get_multi($this->getDeviceArray(), $oids, ['-OQUs'], 'IFOTEC-SMI');

                $device->version = $data[1]['ifoSysFirmware'] . ' (Bootloader ' . $data[1]['ifoSysBootloader'] . ')';
                $device->serial = $data[1]['ifoSysSerialNumber'];
            }
        }

        // sysDecr struct = (<product_reference> . ' : ' . <product_description>) OR (<product_reference>)
        [$device->hardware] = explode(' : ', $device->sysDescr, 2);
    }

    /**
     * @return Collection<Transceiver>
     */
    public function discoverTransceivers(): Collection
    {
        $transceivers = new Collection;

        foreach ($this->sfpRows() as $sfpIndex => $sfp) {
            if (! $this->isYes($sfp['ifoSFPisPlugged'] ?? null)) {
                continue;
            }

            $ifIndex = $this->sfpIfIndex($sfp);
            if ($ifIndex === null) {
                continue;
            }

            $transceivers->push(new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'index' => $sfpIndex,
                'entity_physical_index' => $this->portEntityIndex($ifIndex),
                'connector' => $this->sfpConnector($sfp),
            ]));
        }

        return $transceivers;
    }

    public function discoverTransceiverSensors($types = ['dbm', 'state']): void
    {
        if (in_array('dbm', $types)) {
            $this->discoverSfpPowerSensors();
        }

        if (in_array('state', $types)) {
            $this->discoverSfpStateSensors();
        }
    }

    private function discoverSfpPowerSensors(): void
    {
        $sysIndex = $this->sysProductIndex();
        $directions = ['rx' => ['Receive', 10], 'tx' => ['Transmit', 16]];

        foreach ($this->sfpRows() as $sfpIndex => $sfp) {
            if (! $this->isYes($sfp['ifoSFPisPlugged'] ?? null) || ! in_array($this->sfpConnector($sfp), ['SC', 'LC'], true)) {
                continue;
            }

            $ifIndex = $this->sfpIfIndex($sfp);
            if ($ifIndex === null) {
                continue;
            }

            $ifName = PortCache::getNameFromIfIndex($ifIndex, $this->getDevice());

            foreach ($directions as $prefix => [$label, $valueColumn]) {
                $value = $sfp['ifoSFP' . $prefix . 'PowerVal'] ?? null;
                if (! is_numeric($value) || $this->isNoData($sfp['ifoSFP' . $prefix . 'PowerStatus'] ?? null)) {
                    continue;
                }

                // minus infinity is an alarm on the received power, but only "not measured" on the transmit power
                $minusInfinity = (float) $value < self::OPTICAL_POWER_MIN_VALID;
                if ($minusInfinity && $prefix === 'tx') {
                    continue;
                }

                [$limitLow, $limitLowWarn, $limitWarn, $limitHigh] = $this->sfpThresholds($sfp, $prefix);

                // values read through the MIB are in dBm (DISPLAY-HINT d-1), the numeric OID polled is in tenths of dBm
                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'dbm',
                    'sensor_oid' => ".1.3.6.1.4.1.21362.101.5.1.1.$valueColumn.$sysIndex.$sfpIndex",
                    'sensor_index' => "ifoSFP{$prefix}PowerVal.$sfpIndex",
                    'sensor_type' => 'ifotecSfp',
                    'sensor_descr' => "$ifName Transceiver $label Power",
                    'sensor_divisor' => 10,
                    'sensor_multiplier' => 1,
                    'sensor_limit_low' => $limitLow,
                    'sensor_limit_low_warn' => $limitLowWarn,
                    'sensor_limit_warn' => $limitWarn,
                    'sensor_limit' => $limitHigh,
                    'sensor_current' => $minusInfinity ? self::minusInfinityPower($limitLow) : (float) $value,
                    'user_func' => 'ifotecOpticalPower',
                    'entPhysicalIndex' => $this->portEntityIndex($ifIndex),
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                ]));
            }
        }
    }

    private function discoverSfpStateSensors(): void
    {
        $sysIndex = $this->sysProductIndex();
        $sensors = [
            ['ifoSFPisPlugged', 4, 'ifoSFPisPlugged', 'Presence', self::YES_NO],
            ['ifoSFPrxSignalDetect', 7, 'ifoSFPrxSignalDetect', 'Receive Signal', self::YES_NO],
            ['ifoSFPAlarmeOptStatus', 9, 'ifoDataStatus', 'Optical Alarm', self::DATA_STATUS],
            ['ifoSFPrxPowerStatus', 15, 'ifoDataStatus', 'Receive Power Status', self::DATA_STATUS],
            ['ifoSFPtxPowerStatus', 21, 'ifoDataStatus', 'Transmit Power Status', self::DATA_STATUS],
        ];

        foreach ($sensors as [, , $stateName, , $map]) {
            create_state_index($stateName, states: $this->stateDefinitions($map));
        }

        foreach ($this->sfpRows() as $sfpIndex => $sfp) {
            $ifIndex = $this->sfpIfIndex($sfp);
            if ($ifIndex === null) {
                continue;
            }

            $ifName = PortCache::getNameFromIfIndex($ifIndex, $this->getDevice());

            foreach ($sensors as [$field, $column, $stateName, $label, $map]) {
                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'state',
                    'sensor_oid' => ".1.3.6.1.4.1.21362.101.5.1.1.$column.$sysIndex.$sfpIndex",
                    'sensor_index' => "$field.$sysIndex.$sfpIndex",
                    'sensor_type' => $stateName,
                    'sensor_descr' => "$ifName Transceiver $label",
                    'sensor_current' => $this->stateValue($sfp[$field] ?? null, $map),
                    'entPhysicalIndex' => $this->portEntityIndex($ifIndex),
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                ]));
            }
        }
    }

    private function sfpRows(): array
    {
        $sysIndex = $this->sysProductIndex();
        if ($sysIndex === null) {
            return [];
        }

        $table = SnmpQuery::cache()->hideMib()->walk('IFOTEC-SMI::ifoSFPTable')->table(2);

        return $table[$sysIndex] ?? [];
    }

    /**
     * entPhysicalIndex of a port is not its ifIndex: look it up from the ifIndex of the entity.
     */
    private function portEntityIndex(int $ifIndex): ?int
    {
        $this->portEntities ??= EntPhysical::where('device_id', $this->getDevice()->device_id)
            ->whereNotNull('ifIndex')
            ->pluck('entPhysicalIndex', 'ifIndex')
            ->all();

        return isset($this->portEntities[$ifIndex]) ? (int) $this->portEntities[$ifIndex] : null;
    }

    private function sysProductIndex(): ?string
    {
        $index = SnmpQuery::cache()->get('IFOTEC-SMI::ifoSysProductIndex.0')->value();

        return is_numeric($index) ? (string) $index : null;
    }

    /**
     * @param  array<int, array{string, int}>  $map
     * @return array<int, array{value: int, generic: int, graph: int, descr: string}>
     */
    private function stateDefinitions(array $map): array
    {
        $states = [];
        foreach ($map as $value => [$name, $generic]) {
            $states[] = ['value' => $value, 'generic' => $generic, 'graph' => 0, 'descr' => $name];
        }

        return $states;
    }

    /**
     * @param  array<int, array{string, int}>  $map
     */
    private function stateValue(mixed $raw, array $map): int
    {
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        foreach ($map as $value => [$name]) {
            if ($name === $raw) {
                return $value;
            }
        }

        return 0;
    }

    private function isNoData(mixed $status): bool
    {
        return $status === 'noData' || (is_numeric($status) && (int) $status === 1);
    }

    private function isYes(mixed $value): bool
    {
        return $value === 'yes' || (is_numeric($value) && (int) $value === 1);
    }

    /**
     * ifoSFPportName is the number of the port, that is its ifIndex; any other name is looked up as an ifName.
     */
    private function sfpIfIndex(array $sfp): ?int
    {
        $name = $sfp['ifoSFPportName'] ?? null;
        if (is_numeric($name)) {
            return (int) $name;
        }

        $this->portIfIndexes ??= Port::where('device_id', $this->getDevice()->device_id)->pluck('ifIndex', 'ifName')->all();

        return isset($this->portIfIndexes[$name]) ? (int) $this->portIfIndexes[$name] : null;
    }

    private function sfpConnector(array $sfp): ?string
    {
        $connector = $sfp['ifoSFPConnectorType'] ?? null;

        if (is_numeric($connector)) {
            return self::SFP_CONNECTORS[(int) $connector] ?? null;
        }

        foreach (self::SFP_CONNECTORS as $name) {
            if (is_string($connector) && strcasecmp($name, $connector) === 0) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Thresholds come from the SFP and several modules report wrong ones: all null when they are not trusted.
     *
     * @return array<int, float|null>
     */
    private function sfpThresholds(array $sfp, string $direction): array
    {
        $none = [null, null, null, null];
        $thresholds = [];

        foreach (['ErrThsdLow', 'WrngThsdLow', 'WrngThsdHigh', 'ErrThsdHigh'] as $name) {
            $value = $sfp['ifoSFP' . $direction . 'Power' . $name] ?? null;
            if (! is_numeric($value) || (float) $value < self::OPTICAL_POWER_MIN_VALID) {
                return $none;
            }
            $thresholds[] = (float) $value;
        }

        if (count(array_filter($thresholds)) === 0) {
            return $none; // the agent reports 0 for absent thresholds
        }

        if ($thresholds !== collect($thresholds)->sort()->values()->all()) {
            return $none; // not in increasing order
        }

        return $thresholds;
    }
}
