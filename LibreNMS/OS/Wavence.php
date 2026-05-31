<?php

/**
 * LibreNMS
 *
 * Copyright (C) 2026 LibreNMS Contributors
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * See COPYING for more details.
 */

namespace LibreNMS\OS;

use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Support\Collection;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Interfaces\Discovery\EntityPhysicalDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessMseDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessPowerDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRateDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessXpiDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Wavence extends OS implements
    OSDiscovery,
    EntityPhysicalDiscovery,
    WirelessMseDiscovery,
    WirelessPowerDiscovery,
    WirelessRateDiscovery,
    WirelessXpiDiscovery
{
    private const RADIO_MIB = 'OPTICSIM-RADIO-TRS-COMMON-MIB::';

    private const EQUIPMENT_ACTUAL_OID = '.1.3.6.1.4.1.637.54.1.1.8.1.1.1.4';
    private const REMOTE_INVENTORY_MNEMONIC_OID = '.1.3.6.1.4.1.637.54.1.1.8.1.4.1.3';
    private const REMOTE_INVENTORY_PART_NUMBER_OID = '.1.3.6.1.4.1.637.54.1.1.8.1.4.1.5';
    private const REMOTE_INVENTORY_SERIAL_NUMBER_OID = '.1.3.6.1.4.1.637.54.1.1.8.1.4.1.8';

    private const OPERATIVE_AVAILABLE_BANDWIDTH_OID = '.1.3.6.1.4.1.637.54.1.10.3.1.1.1.23';

    private const LEGACY_HARDWARE_OID = 'TSDIM-SNMPNE-MIB::tsdimNeInstallationType.0';
    private const LEGACY_FEATURES_OID = 'TSDIM-SNMPNE-MIB::tsdimSdhNeLabel.0';

    public function discoverOS(Device $device): void
    {
        $version = $this->cleanSnmpValue(SnmpQuery::get('SNMPv2-MIB::sysDescr.0')->value());
        $main_radio = $this->discoverMainRadioInventory();

        /*
         * Newer Wavence devices expose the main radio type and serial in
         * OPTICSIM-EQPT-MIB. Show those on the Overview page.
         */
        if ($main_radio['hardware'] !== '') {
            $device->hardware = $main_radio['hardware'];
        }

        if ($main_radio['serial'] !== '') {
            $device->serial = $main_radio['serial'];
        }

        /*
         * Keep compatibility with older Wavence test data and devices that do
         * not expose OPTICSIM-EQPT-MIB remote inventory.
         */
        if ($main_radio['hardware'] === '') {
            $hardware = $this->cleanSnmpValue(SnmpQuery::get(self::LEGACY_HARDWARE_OID)->value());

            if ($hardware !== '') {
                $device->hardware = $hardware;
            }

            $features = $this->cleanSnmpValue(SnmpQuery::get(self::LEGACY_FEATURES_OID)->value());

            if ($features !== '') {
                $device->features = $features;
            }
        }

        if ($version !== '') {
            $device->version = $version;
        }
    }

    /**
     * Discover Wavence physical inventory.
     *
     * Wavence inventory uses binary/non-printable indexes. Use numeric OIDs
     * and groupByIndex(4) so the rows do not collapse into one row.
     *
     * @return Collection<int, EntPhysical>
     */
    public function discoverEntityPhysical(): Collection
    {
        $inventory = new Collection;

        $chassis_name = $this->discoverChassisName();

        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => 1,
            'entPhysicalDescr' => $chassis_name,
            'entPhysicalClass' => 'chassis',
            'entPhysicalName' => $chassis_name,
            'entPhysicalContainedIn' => 0,
            'entPhysicalMfgName' => 'Nokia',
            'entPhysicalParentRelPos' => 1,
            'entPhysicalIsFRU' => 'false',
        ]));

        $rows = SnmpQuery::numeric()->walk([
            self::REMOTE_INVENTORY_MNEMONIC_OID,
            self::REMOTE_INVENTORY_PART_NUMBER_OID,
            self::REMOTE_INVENTORY_SERIAL_NUMBER_OID,
        ])->groupByIndex(4);

        $position = 1;

        foreach ($rows as $index => $row) {
            $name = $this->cleanSnmpValue(
                $this->getNumericRowValue($row, self::REMOTE_INVENTORY_MNEMONIC_OID, $index)
            );

            $model = $this->cleanSnmpValue(
                $this->getNumericRowValue($row, self::REMOTE_INVENTORY_PART_NUMBER_OID, $index)
            );

            $serial = $this->cleanSnmpValue(
                $this->getNumericRowValue($row, self::REMOTE_INVENTORY_SERIAL_NUMBER_OID, $index)
            );

            if ($name === '') {
                continue;
            }

            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 100 + $position,
                'entPhysicalDescr' => $name,
                'entPhysicalClass' => $this->inventoryClass($name),
                'entPhysicalName' => $name,
                'entPhysicalModelName' => $model !== '' ? $model : null,
                'entPhysicalSerialNum' => $serial !== '' ? $serial : null,
                'entPhysicalContainedIn' => 1,
                'entPhysicalMfgName' => 'Nokia',
                'entPhysicalParentRelPos' => $position,
                'entPhysicalIsFRU' => 'true',
            ]));

            $position++;
        }

        return $inventory;
    }

    /**
     * Discover wireless MSE.
     *
     * Wavence MSE values are reported in tenths of dB.
     * Diversity MSE values of zero are skipped because Wavence reports zero
     * when the diversity receiver path is not active/available.
     *
     * @return array<WirelessSensor>
     */
    public function discoverWirelessMse(): array
    {
        $sensors = [];

        $oids = [
            'analogueMeasuresLocalMSE' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.13.',
                'type' => 'wavence-local-mse',
                'descr' => 'Local MSE',
                'skip_zero' => false,
            ],
            'analogueMeasuresRemoteMSE' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.14.',
                'type' => 'wavence-remote-mse',
                'descr' => 'Remote MSE',
                'skip_zero' => false,
            ],
            'analogueMeasuresLocalDivMSE' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.21.',
                'type' => 'wavence-local-div-mse',
                'descr' => 'Local Div MSE',
                'skip_zero' => true,
            ],
            'analogueMeasuresRemoteDivMSE' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.22.',
                'type' => 'wavence-remote-div-mse',
                'descr' => 'Remote Div MSE',
                'skip_zero' => true,
            ],
        ];

        foreach ($oids as $oid => $sensor) {
            $data = SnmpQuery::walk(self::RADIO_MIB . $oid)->valuesByIndex();

            foreach ($data as $index => $entry) {
                $value = $this->getTableValue($entry, self::RADIO_MIB . $oid);

                if (! is_numeric($value)) {
                    continue;
                }

                if ($sensor['skip_zero'] && (float) $value === 0.0) {
                    continue;
                }

                $divisor = 10;

                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Mse,
                    $this->getDeviceId(),
                    $sensor['num_oid'] . $index,
                    $sensor['type'],
                    $index,
                    'Radio ' . $index . ' ' . $sensor['descr'],
                    $value / $divisor,
                    1,
                    $divisor
                );
            }
        }

        return $sensors;
    }

    /**
     * Discover wireless tx/rx power.
     *
     * Wavence radio power values are reported in tenths of dBm.
     *
     * @return array<WirelessSensor>
     */
    public function discoverWirelessPower(): array
    {
        $sensors = [];

        $oids = [
            'analogueMeasuresLocalTxPower' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.2.',
                'type' => 'wavence-local-tx',
                'descr' => 'Local Tx Power',
            ],
            'analogueMeasuresLocalRxMainPower' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.3.',
                'type' => 'wavence-local-rx-main',
                'descr' => 'Local Rx Main Power',
            ],
            'analogueMeasuresLocalRxDivPower' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.4.',
                'type' => 'wavence-local-rx-div',
                'descr' => 'Local Rx Div Power',
                'skip_value' => -997,
            ],
            'analogueMeasuresRemoteTxPower' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.6.',
                'type' => 'wavence-remote-tx',
                'descr' => 'Remote Tx Power',
            ],
            'analogueMeasuresRemoteRxMainPower' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.7.',
                'type' => 'wavence-remote-rx-main',
                'descr' => 'Remote Rx Main Power',
            ],
            'analogueMeasuresRemoteRxDivPower' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.8.',
                'type' => 'wavence-remote-rx-div',
                'descr' => 'Remote Rx Div Power',
                'skip_value' => -997,
            ],
        ];

        foreach ($oids as $oid => $sensor) {
            $data = SnmpQuery::walk(self::RADIO_MIB . $oid)->valuesByIndex();

            foreach ($data as $index => $entry) {
                $value = $this->getTableValue($entry, self::RADIO_MIB . $oid);

                if (! is_numeric($value)) {
                    continue;
                }

                if (isset($sensor['skip_value']) && (float) $value === (float) $sensor['skip_value']) {
                    continue;
                }

                $divisor = 10;

                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Power,
                    $this->getDeviceId(),
                    $sensor['num_oid'] . $index,
                    $sensor['type'],
                    $index,
                    'Radio ' . $index . ' ' . $sensor['descr'],
                    $value / $divisor,
                    1,
                    $divisor
                );
            }
        }

        return $sensors;
    }

    /**
     * Discover wireless XPD/XPI.
     *
     * Wavence reports XPD values in tenths of dB. LibreNMS stores this under
     * the wireless XPI sensor class.
     *
     * @return array<WirelessSensor>
     */
    public function discoverWirelessXpi(): array
    {
        $sensors = [];

        $oids = [
            'analogueMeasuresLocalXpd' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.9.',
                'type' => 'wavence-local-xpd',
                'descr' => 'Local XPD',
                'skip_zero' => false,
            ],
            'analogueMeasuresRemoteXpd' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.18.',
                'type' => 'wavence-remote-xpd',
                'descr' => 'Remote XPD',
                'skip_zero' => false,
            ],
            'analogueMeasuresLocalDivXpd' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.19.',
                'type' => 'wavence-local-div-xpd',
                'descr' => 'Local Div XPD',
                'skip_zero' => true,
            ],
            'analogueMeasuresRemoteDivXpd' => [
                'num_oid' => '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.20.',
                'type' => 'wavence-remote-div-xpd',
                'descr' => 'Remote Div XPD',
                'skip_zero' => true,
            ],
        ];

        foreach ($oids as $oid => $sensor) {
            $data = SnmpQuery::walk(self::RADIO_MIB . $oid)->valuesByIndex();

            foreach ($data as $index => $entry) {
                $value = $this->getTableValue($entry, self::RADIO_MIB . $oid);

                if (! is_numeric($value)) {
                    continue;
                }

                if ($sensor['skip_zero'] && (float) $value === 0.0) {
                    continue;
                }

                $divisor = 10;

                $sensors[] = new WirelessSensor(
                    WirelessSensorType::Xpi,
                    $this->getDeviceId(),
                    $sensor['num_oid'] . $index,
                    $sensor['type'],
                    $index,
                    'Radio ' . $index . ' ' . $sensor['descr'],
                    $value / $divisor,
                    1,
                    $divisor
                );
            }
        }

        return $sensors;
    }

    /**
     * Discover wireless rate.
     *
     * Wavence reports radioPDHTTPBidOperativeAvailableBandwidth in kbit/s.
     * This value is useful for the Twin Aggregated Radio interface because it
     * reflects the currently operative radio bandwidth. Zero values are skipped.
     *
     * @return array<WirelessSensor>
     */
    public function discoverWirelessRate(): array
    {
        $sensors = [];

        $data = SnmpQuery::numeric()
            ->walk(self::OPERATIVE_AVAILABLE_BANDWIDTH_OID)
            ->groupByIndex(1);

        foreach ($data as $index => $entry) {
            $value = $this->getNumericRowValue($entry, self::OPERATIVE_AVAILABLE_BANDWIDTH_OID, $index);

            if (! is_numeric($value) || (float) $value <= 0) {
                continue;
            }

            $multiplier = 1000;

            $sensors[] = new WirelessSensor(
                WirelessSensorType::Rate,
                $this->getDeviceId(),
                self::OPERATIVE_AVAILABLE_BANDWIDTH_OID . '.' . $index,
                'wavence-operative-bandwidth',
                $index,
                'Radio ' . $index . ' Operative Available Bandwidth',
                $value * $multiplier,
                $multiplier,
                1
            );
        }

        return $sensors;
    }

    /**
     * @return array{hardware: string, serial: string}
     */
    private function discoverMainRadioInventory(): array
    {
        $rows = SnmpQuery::numeric()->walk([
            self::REMOTE_INVENTORY_MNEMONIC_OID,
            self::REMOTE_INVENTORY_SERIAL_NUMBER_OID,
        ])->groupByIndex(4);

        foreach ($rows as $index => $row) {
            $name = $this->cleanSnmpValue(
                $this->getNumericRowValue($row, self::REMOTE_INVENTORY_MNEMONIC_OID, $index)
            );

            $serial = $this->cleanSnmpValue(
                $this->getNumericRowValue($row, self::REMOTE_INVENTORY_SERIAL_NUMBER_OID, $index)
            );

            if (preg_match('/^UBT[-_][A-Z0-9]/i', $name)) {
                return [
                    'hardware' => $name,
                    'serial' => $serial,
                ];
            }
        }

        return [
            'hardware' => '',
            'serial' => '',
        ];
    }

    private function discoverChassisName(): string
    {
        $equipment = SnmpQuery::numeric()
            ->walk(self::EQUIPMENT_ACTUAL_OID)
            ->groupByIndex(4);

        foreach ($equipment as $index => $row) {
            $name = $this->cleanSnmpValue(
                $this->getNumericRowValue($row, self::EQUIPMENT_ACTUAL_OID, $index)
            );

            if (preg_match('/^(NE\s+)?(UBT|Wavence|MSS|NIM)/i', $name)) {
                return $name;
            }
        }

        return 'Nokia Wavence';
    }

    private function inventoryClass(string $name): string
    {
        return match (true) {
            str_starts_with($name, 'SFP') => 'module',
            str_starts_with($name, 'UBT') => 'module',
            str_starts_with($name, 'DPLX') => 'module',
            str_starts_with($name, 'AIM') => 'module',
            default => 'other',
        };
    }

    private function cleanSnmpValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        $value = trim((string) $value);

        if ($value === '' || strtoupper($value) === 'NULL') {
            return '';
        }

        if (preg_match('/^(No Such (Object|Instance)|No more variables)/i', $value)) {
            return '';
        }

        return $value;
    }

    private function getNumericRowValue(mixed $row, string $base_oid, string|int $index): mixed
    {
        if (! is_array($row)) {
            return $row;
        }

        $full_oid = $base_oid . '.' . $index;

        if (array_key_exists($full_oid, $row)) {
            return $row[$full_oid];
        }

        if (array_key_exists($base_oid, $row)) {
            return $row[$base_oid];
        }

        foreach ($row as $oid => $value) {
            if (is_string($oid) && str_starts_with($oid, $base_oid . '.')) {
                return $value;
            }
        }

        return null;
    }

    private function getTableValue(mixed $entry, string $oid): mixed
    {
        if (! is_array($entry)) {
            return $entry;
        }

        if (array_key_exists($oid, $entry)) {
            return $entry[$oid];
        }

        if (str_contains($oid, '::')) {
            $short_oid = substr($oid, strpos($oid, '::') + 2);

            if (array_key_exists($short_oid, $entry)) {
                return $entry[$short_oid];
            }
        }

        foreach ($entry as $value) {
            return $value;
        }

        return null;
    }
}
