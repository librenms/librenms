<?php

/**
 * Cdata.php
 *
 * C-Data / NSCRTV based EPON, GPON and XGS-PON OLTs
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

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\EntPhysical;
use App\Models\Transceiver;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\EntityPhysicalDiscovery;
use LibreNMS\Interfaces\Discovery\TransceiverDiscovery;
use LibreNMS\OS;
use LibreNMS\OS\Traits\EntityMib;

class Cdata extends OS implements EntityPhysicalDiscovery, TransceiverDiscovery
{
    use EntityMib {
        discoverEntityPhysical as discoverEntityMib;
    }

    // entPhysicalIndex offsets for inventory rows that do not come from ENTITY-MIB
    public const ENT_CHASSIS = 1;
    private const ENT_POWER_BASE = 900000000;
    private const ENT_TRANSCEIVER_BASE = 800000000;

    // NSCRTV-FTTX-EPON-MIB::powerPropertyTable; the shipped MIB does not parse, so numeric. Index is device.card
    public const POWER_TABLE = '.1.3.6.1.4.1.17409.2.3.1.4.1.1';
    public const POWER_OPER_STATUS = 3;   // up(1) down(2) testing(3)
    public const POWER_NAME = 6;
    public const POWER_PRESENCE = 7;      // installed(1) notInstalled(2) others(3)
    public const POWER_REDUNDANCY = 8;    // active(1) standby(2) standalone(3) loadSharing(4)

    // NSCRTV-PON-TREE-EXT-MIB::onuRegInfoTable, walked numerically because the MIB's index textual conventions are missing
    public const ONU_REG_TABLE = '.1.3.6.1.4.1.34592.1.3.100.12.3.1.1';
    public const ONU_REG_TIMES = 2;
    public const ONU_DEREG_TIMES = 3;
    public const ONU_ONLINE_DURATION = 6; // seconds
    public const ONU_OFFLINE_REASON = 7;  // string, e.g. losi

    // vendor MAC table, index is 6.<mac octets>.<vlan>, value is an ifIndex for uplinks or an ONU index for subscribers
    public const FDB_PORT_COLUMN = '.1.3.6.1.4.1.17409.2.3.2.4.2.1.4';
    // OLT side PON transceiver readings. Vendor extension, absent from the shipped MIBs.
    // Row index is device.slot.ifIndex, values are hundredths except rx which is whole dBm.
    private const PON_OPTIC_TABLE = '.1.3.6.1.4.1.17409.2.3.3.5.1';
    private const PON_OPTIC_COLUMNS = [
        4 => 'voltage',
        5 => 'bias',
        6 => 'tx',
        7 => 'rx',
        100 => 'temperature',
        101 => 'serial',
        102 => 'model',
    ];

    // Rx power the OLT measures per ONU, hundredths of dBm, indexed by ONU index
    private const ONU_RX_AT_OLT = '.1.3.6.1.4.1.17409.2.3.3.6.1.2';

    /**
     * Board temperature scaling is not consistent across firmware families.
     * V4.x reports tenths of a degree (305 = 30.5 C) while V3.x reports whole
     * degrees (20 = 20 C), and the MIB documents neither. Anything at or above
     * 100 can only be tenths, since the board would not survive those readings.
     */
    public static function scaleTemperature(mixed $value): float
    {
        $value = (float) $value;

        return abs($value) >= 100 ? $value / 10 : $value;
    }

    /**
     * @return array<string, string> ONU index => ONU name (e.g. "gpon 0/0/1 onu 1")
     */
    public function onuNames(): array
    {
        $names = [];
        foreach (\SnmpQuery::cache()->walk('NSCRTV-FTTX-GPON-MIB::onuName')->table(1) as $index => $data) {
            $names[$index] = $data['NSCRTV-FTTX-GPON-MIB::onuName'] ?? "onu $index";
        }

        return $names;
    }

    /**
     * ONU side transceiver readings from NSCRTV-FTTX-GPON-MIB. Units are hundredths (centi-dBm, centi-mA, centi-degree);
     * the MIB labels voltage as centi-mV but the values are clearly hundredths of a volt.
     *
     * @return Collection<int, array{index: string, onu: string, ifIndex: string, name: string, rx: ?string, tx: ?string, bias: ?string, voltage: ?string, temperature: ?string}>
     */
    public function onuOptics(): Collection
    {
        $names = $this->onuNames();

        return \SnmpQuery::cache()->walk('NSCRTV-FTTX-GPON-MIB::gponOnuPonPortOpticalTransmissionPropertyTable')
            ->mapTable(fn ($data, $onu, $slot, $ifIndex) => [
                'index' => "$onu.$slot.$ifIndex",
                'onu' => $onu,
                'ifIndex' => $ifIndex,
                'name' => $names[$onu] ?? "onu $onu",
                'rx' => $data['NSCRTV-FTTX-GPON-MIB::onuReceivedOpticalPower'] ?? null,
                'tx' => $data['NSCRTV-FTTX-GPON-MIB::onuTramsmittedOpticalPower'] ?? null,
                'bias' => $data['NSCRTV-FTTX-GPON-MIB::onuBiasCurrent'] ?? null,
                'voltage' => $data['NSCRTV-FTTX-GPON-MIB::onuWorkingVoltage'] ?? null,
                'temperature' => $data['NSCRTV-FTTX-GPON-MIB::onuWorkingTemperature'] ?? null,
            ]);
    }

    /**
     * @return array<string, string> ONU index => Rx power measured at the OLT, hundredths of dBm
     */
    public function onuRxAtOlt(): array
    {
        $rx = [];
        foreach (\SnmpQuery::cache()->numeric()->walk(self::ONU_RX_AT_OLT)->values() as $oid => $value) {
            $rx[substr($oid, strlen(self::ONU_RX_AT_OLT) + 1)] = $value;
        }

        return $rx;
    }

    /**
     * OLT side PON transceiver rows keyed by table index, with index, ifIndex, descr and the PON_OPTIC_COLUMNS values.
     *
     * @return array<string, array<string, ?string>>
     */
    public function ponOptics(): array
    {
        $rows = [];
        foreach (\SnmpQuery::cache()->numeric()->walk(self::PON_OPTIC_TABLE)->values() as $oid => $value) {
            if (! preg_match('/\.(\d+)\.(\d+\.\d+\.(\d+))$/', substr($oid, strlen(self::PON_OPTIC_TABLE)), $m)) {
                continue;
            }
            [, $column, $index, $ifIndex] = $m;
            if (! isset(self::PON_OPTIC_COLUMNS[$column])) {
                continue;
            }

            $rows[$index] ??= [
                'index' => $index,
                'ifIndex' => $ifIndex,
                'descr' => PortCache::getByIfIndex($ifIndex, $this->getDevice())->ifDescr ?? "pon $ifIndex",
                'voltage' => null,
                'bias' => null,
                'tx' => null,
                'rx' => null,
                'temperature' => null,
                'serial' => null,
                'model' => null,
            ];
            $rows[$index][self::PON_OPTIC_COLUMNS[$column]] = trim((string) $value);
        }

        return $rows;
    }

    /**
     * @return array<string, array<int, ?string>> power card index (device.card) => column number => value
     */
    public function powerCards(): array
    {
        $cards = [];
        foreach (\SnmpQuery::cache()->numeric()->walk(self::POWER_TABLE)->values() as $oid => $value) {
            if (preg_match('/\.(\d+)\.(\d+\.\d+)$/', substr($oid, strlen(self::POWER_TABLE)), $m)) {
                $cards[$m[2]][(int) $m[1]] = trim((string) $value);
            }
        }

        return $cards;
    }

    /**
     * @return array<string, array<int, string>> ONU index => column number => value
     */
    public function onuRegInfo(): array
    {
        $rows = [];
        foreach (\SnmpQuery::cache()->numeric()->walk(self::ONU_REG_TABLE)->values() as $oid => $value) {
            if (preg_match('/^\.(\d+)\.(\d+)$/', substr($oid, strlen(self::ONU_REG_TABLE)), $m)) {
                $rows[$m[2]][(int) $m[1]] = trim((string) $value);
            }
        }

        return $rows;
    }

    /**
     * @return array<string, string> ONU index => ifIndex of the PON port it is registered on
     */
    public function onuPonPorts(): array
    {
        return $this->onuOptics()->pluck('ifIndex', 'onu')->all();
    }

    /**
     * ONU identity from NSCRTV-FTTX-GPON-MIB gponOnuInfoTable and onuInfoSoftwareTable.
     *
     * @return array<string, array{name: string, serial: ?string, vendor: ?string, model: ?string, hardware: ?string, software: ?string, distance: ?string}>
     */
    public function onuInventory(): array
    {
        $software = [];
        foreach (\SnmpQuery::cache()->walk('NSCRTV-FTTX-GPON-MIB::onuInfoSoftwareTable')->table(1) as $index => $data) {
            // two images, report the active one
            $software[$index] = ($data['NSCRTV-FTTX-GPON-MIB::onuSoftware1Active'] ?? 0) == 1
                ? $data['NSCRTV-FTTX-GPON-MIB::onuSoftware1Version'] ?? null
                : $data['NSCRTV-FTTX-GPON-MIB::onuSoftware0Version'] ?? null;
        }

        $onus = [];
        foreach (\SnmpQuery::cache()->walk('NSCRTV-FTTX-GPON-MIB::gponOnuInfoTable')->table(1) as $index => $data) {
            if (! isset($data['NSCRTV-FTTX-GPON-MIB::onuName'])) {
                continue; // columns the MIB does not know are parsed as rows
            }
            $model = trim((string) ($data['NSCRTV-FTTX-GPON-MIB::onuEquipmentID'] ?? ''));

            $onus[$index] = [
                'name' => $data['NSCRTV-FTTX-GPON-MIB::onuName'] ?? "onu $index",
                'serial' => self::formatOnuSerial($data['NSCRTV-FTTX-GPON-MIB::onuSerialNum'] ?? ''),
                'vendor' => $data['NSCRTV-FTTX-GPON-MIB::onuVendorID'] ?? null,
                'model' => trim($model, '0') === '' ? null : $model, // firmware pads unknown models with zeros
                'hardware' => $data['NSCRTV-FTTX-GPON-MIB::onuHardwareVersion'] ?? null,
                'software' => $software[$index] ?? null,
                'distance' => $data['NSCRTV-FTTX-GPON-MIB::onuTestDistance'] ?? null,
            ];
        }

        return $onus;
    }

    /**
     * GPON serials are 4 ASCII vendor characters followed by 4 binary bytes, shown as VVVVXXXXXXXX.
     */
    public static function formatOnuSerial(string $raw): ?string
    {
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^(?:[0-9a-fA-F]{2}\s*){8}$/', $raw)) {
            $bytes = hex2bin(preg_replace('/\s/', '', $raw));
        } elseif (strlen($raw) !== 8 && mb_check_encoding($raw, 'UTF-8')) {
            $bytes = mb_convert_encoding($raw, 'ISO-8859-1', 'UTF-8'); // binary octets got utf-8 encoded on the way
        } else {
            $bytes = $raw;
        }

        return strlen($bytes) === 8 ? substr($bytes, 0, 4) . strtoupper(bin2hex(substr($bytes, 4))) : $raw;
    }

    public function discoverEntityPhysical(): Collection
    {
        $inventory = $this->discoverEntityMib();
        $device = $this->getDevice();

        $inventory->push(new EntPhysical([
            'entPhysicalIndex' => self::ENT_CHASSIS,
            'entPhysicalDescr' => $device->hardware ?: 'OLT',
            'entPhysicalClass' => 'chassis',
            'entPhysicalName' => $device->sysName ?: $device->hostname,
            'entPhysicalSerialNum' => $device->serial,
            'entPhysicalModelName' => $device->hardware,
            'entPhysicalMfgName' => 'C-Data',
            'entPhysicalSoftwareRev' => $device->version,
            'entPhysicalContainedIn' => 0,
            'entPhysicalIsFRU' => 'false',
        ]));

        foreach ($this->powerCards() as $index => $card) {
            [, $slot] = explode('.', $index);
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => self::ENT_POWER_BASE + (int) $slot,
                'entPhysicalDescr' => $card[self::POWER_NAME] ?? 'power card',
                'entPhysicalClass' => 'powerSupply',
                'entPhysicalName' => 'Power ' . $slot,
                'entPhysicalContainedIn' => self::ENT_CHASSIS,
                'entPhysicalParentRelPos' => (int) $slot,
                'entPhysicalIsFRU' => 'true',
            ]));
        }

        $ponEntity = [];
        foreach ($this->ponOptics() as $optic) {
            if (empty($optic['model']) && empty($optic['serial'])) {
                continue;
            }
            $ponEntity[$optic['ifIndex']] = self::ENT_TRANSCEIVER_BASE + (int) $optic['ifIndex'];
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => $ponEntity[$optic['ifIndex']],
                'entPhysicalDescr' => 'PON transceiver',
                'entPhysicalClass' => 'transceiver',
                'entPhysicalName' => $optic['descr'],
                'entPhysicalSerialNum' => $optic['serial'],
                'entPhysicalModelName' => $optic['model'],
                'entPhysicalContainedIn' => self::ENT_CHASSIS,
                'entPhysicalIsFRU' => 'true',
                'ifIndex' => (int) $optic['ifIndex'],
            ]));
        }

        $ponPorts = $this->onuPonPorts();
        foreach ($this->onuInventory() as $index => $onu) {
            $ifIndex = $ponPorts[$index] ?? null;
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => (int) $index,
                'entPhysicalDescr' => 'ONU' . ($onu['distance'] ? ", {$onu['distance']} m" : ''),
                'entPhysicalClass' => 'other',
                'entPhysicalName' => $onu['name'],
                'entPhysicalSerialNum' => $onu['serial'],
                'entPhysicalModelName' => $onu['model'],
                'entPhysicalMfgName' => $onu['vendor'],
                'entPhysicalHardwareRev' => $onu['hardware'],
                'entPhysicalSoftwareRev' => $onu['software'],
                'entPhysicalContainedIn' => $ponEntity[$ifIndex] ?? self::ENT_CHASSIS,
                'entPhysicalIsFRU' => 'true',
                'ifIndex' => $ifIndex === null ? null : (int) $ifIndex,
            ]));
        }

        return $inventory;
    }

    public function discoverTransceivers(): Collection
    {
        return (new Collection($this->ponOptics()))
            ->filter(fn ($optic) => ! empty($optic['model']) || ! empty($optic['serial']))
            ->map(fn ($optic) => new Transceiver([
                'port_id' => (int) PortCache::getIdFromIfIndex($optic['ifIndex'], $this->getDevice()),
                'index' => $optic['index'],
                'entity_physical_index' => self::ENT_TRANSCEIVER_BASE + (int) $optic['ifIndex'],
                'type' => 'SFP+',
                'model' => $optic['model'] ?? null,
                'serial' => $optic['serial'] ?? null,
                'ddm' => true,
            ]))->values();
    }
}
