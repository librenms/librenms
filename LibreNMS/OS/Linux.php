<?php

/*
 * Linux.php
 *
 * -Description-
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\LibrenmsConfig;
use App\Models\EntPhysical;
use App\Models\Sensor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LibreNMS\Enum\SensorType;
use LibreNMS\Interfaces\Discovery\VminfoDiscovery;
use LibreNMS\OS\Traits\VminfoVmware;
use LibreNMS\OS\Traits\VminfoXcpNg;
use LibreNMS\Polling\Method\DataSources\LibvirtDataSource;
use LibreNMS\Util\Number;
use LibreNMS\Util\StringHelpers;
use SnmpQuery;

class Linux extends Shared\Unix implements VminfoDiscovery
{
    // NOTE: Only Linux specific stuff should go here, most things should be in Unix

    use VminfoVmware, VminfoXcpNg {
        VminfoVmware::discoverVmInfo as discoverVmwareVminfo;
        VminfoXcpNg::discoverVmInfo as discoverXcpNgVminfo;
    }

    /**
     * @return string[]
     */
    public static function vmTypes(): array
    {
        return ['vmware', 'xcp-ng'];
    }

    /**
     * libvirt does not use snmp, only ssh tunnels, so it is a source of its own.
     * It stays a source while the device has its VMs, so they are removed when libvirt is disabled.
     */
    public function dataSources(string $interface): array
    {
        $sources = parent::dataSources($interface);

        if ($interface === VminfoDiscovery::class && ! $this->isXcpNg()
            && (LibrenmsConfig::get('enable_libvirt') || $this->getDevice()->vminfo->contains('vm_type', 'libvirt'))) {
            $sources[] = new LibvirtDataSource($this->getDevice());
        }

        return $sources;
    }

    public function discoverVmInfo(): Collection
    {
        return $this->isXcpNg() ? $this->discoverXcpNgVminfo() : $this->discoverVmwareVminfo();
    }

    private function isXcpNg(): bool
    {
        return (bool) preg_match('/^XCP-ng/', (string) $this->getDevice()->features);
    }

    public function discoverEntityPhysical(): Collection
    {
        return $this->discoverLsiMegaRaidInventory();
    }

    private function discoverLsiMegaRaidInventory(): Collection
    {
        $inventory = new Collection;

        $controller_array = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::adapterInfoTable')->table(1);

        if (empty($controller_array)) {
            return $inventory; // no controllers, skip the rest.
        }

        foreach ($controller_array as $controller) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 200 + $controller['adapterID-AIT'],
                'entPhysicalParentRelPos' => $controller['adapterID-AIT'] ?? -1,
                'entPhysicalDescr' => '/C' . $controller['adapterID-AIT'],
                'entPhysicalClass' => 'port',
                'entPhysicalModelName' => $controller['productName'],
                'entPhysicalSerialNum' => $controller['serialNo'],
                'entPhysicalContainedIn' => '0',
                'entPhysicalVendorType' => $controller['adapterVendorID'],
                'entPhysicalFirmwareRev' => $controller['firmwareVersion'],
            ]));
        }

        $bbus = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::bbuTable')->table(1);
        foreach ($bbus as $bbu) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 1000 + ($bbu['pdIndex'] ?? null),
                'entPhysicalClass' => 'charge',
                'entPhysicalModelName' => $bbu['deviceName'],
                'entPhysicalSerialNum' => $bbu['serialNumber'],
                'entPhysicalContainedIn' => 200 + $bbu['adpID'],
                'entPhysicalIsFRU' => 'true',
                'entPhysicalFirmwareRev' => $bbu['firmwareStatus'],
            ]));
        }

        $enclosures = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::enclosureTable')->table(1);
        foreach ($enclosures as $enclosure) {
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 210 + $enclosure['deviceId'],
                'entPhysicalVendorType' => $enclosure['slotCount'],
                'entPhysicalParentRelPos' => $enclosure['deviceId'] ?? -1,
                'entPhysicalDescr' => '/C' . ($enclosure['adapterID-ET'] ?? '0') . '/E' . $enclosure['deviceId'],
                'entPhysicalClass' => 'chassis',
                'entPhysicalModelName' => $enclosure['productID'],
                'entPhysicalSerialNum' => $enclosure['enclSerialNumber'],
                'entPhysicalContainedIn' => 200 + $enclosure['adapterID-ET'],
                'entPhysicalMfgName' => $enclosure['vendorID'],
                'entPhysicalFirmwareRev' => $this->handleHex($enclosure['enclFirmwareVersion']),
            ]));
        }

        $drives = SnmpQuery::hideMib()->walk('LSI-MegaRAID-SAS-MIB::physicalDriveTable')->table(1);
        foreach ($drives as $drive) {
            $enclDeviceId = $drive['enclDeviceId'] ?? 0;
            $inventory->push(new EntPhysical([
                'entPhysicalIndex' => 500 + $enclDeviceId * 100 + $drive['physDevID'],
                'entPhysicalParentRelPos' => $drive['slotNumber'] ?? -1,
                'entPhysicalDescr' => '/C' . ($drive['adpID-PDT'] ?? '0') . '/E' . $enclDeviceId . '/S' . ($drive['slotNumber'] ?? '0'),
                'entPhysicalClass' => 'drive',
                'entPhysicalModelName' => $drive['pdProductID'] ?? null,
                'entPhysicalSerialNum' => $drive['pdSerialNumber'] ?? null,
                'entPhysicalContainedIn' => 210 + $enclDeviceId,
                'entPhysicalIsFRU' => 'true',
                'entPhysicalFirmwareRev' => $drive['pdFwversion'] ?? null, // missing
            ]));
        }

        return $inventory;
    }

    private function handleHex(string $string): string
    {
        $string = str_replace("\n", '', $string);
        if (StringHelpers::isHex($string, ' ')) {
            $ascii = StringHelpers::hexToAscii($string, ' ');

            return preg_split('/[^ -~]/', $ascii)[0] ?? $ascii;
        }

        return $string;
    }

    protected function discoverOsSensors(): Collection
    {
        return $this->discoverRpiGpioMonitorSensors()
            ->merge(parent::discoverOsSensors())
            ->merge($this->discoverLinuxSensors());
    }

    /**
     * The rpigpiomonitor extend outputs a configuration line for each sensor
     * (name,type,descr,low limit,low warn limit,warn limit,high limit[,group];value,generic,descr;...)
     * followed by a line with its value.
     *
     * @return Collection<int, Sensor>
     */
    private function discoverRpiGpioMonitorSensors(): Collection
    {
        $sensors = new Collection;
        $lines = SnmpQuery::numericIndex()->walk('NET-SNMP-EXTEND-MIB::nsExtendOutLine."rpigpiomonitor"')->valuesByIndex();
        $config = null;
        $sensorIndex = 0;

        foreach ($lines as $index => $line) {
            $line = (string) ($line['NET-SNMP-EXTEND-MIB::nsExtendOutLine'] ?? '');

            if (str_contains($line, ';')) {
                $config = $this->parseRpiGpioMonitorConfig($line);
                continue;
            }

            $valueIndex = $sensorIndex++;
            if ($config === null) {
                Log::error("[rpigpiomonitor] An error occurred while reading a sensor! Please run 'rpigpiomonitor.php -validate' on the target device to verify the configuration.");
                continue;
            }

            $sensor = new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => $config['type'],
                'sensor_oid' => ".1.3.6.1.4.1.8072.1.3.2.4.1.2.$index",
                'sensor_index' => $valueIndex,
                'sensor_type' => $config['name'],
                'sensor_descr' => $config['descr'],
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => Number::extract($line), // the same as the poll conversion
                'sensor_limit_low' => is_numeric($config['low_limit']) ? $config['low_limit'] : null,
                'sensor_limit_low_warn' => is_numeric($config['low_warn_limit']) ? $config['low_warn_limit'] : null,
                'sensor_limit_warn' => is_numeric($config['warn_limit']) ? $config['warn_limit'] : null,
                'sensor_limit' => is_numeric($config['high_limit']) ? $config['high_limit'] : null,
                'group' => $config['group'],
                'rrd_type' => 'GAUGE',
            ]);
            $sensors->push($config['states'] ? $sensor->withStates($config['states']) : $sensor);
            $config = null;
        }

        return $sensors;
    }

    /**
     * @return array{name: string, type: string, descr: string, low_limit: ?string, low_warn_limit: ?string, warn_limit: ?string, high_limit: ?string, group: ?string, states: list<array{value: int, generic: int, descr: string}>}|null
     */
    private function parseRpiGpioMonitorConfig(string $line): ?array
    {
        $parts = explode(';', $line);
        $sensor = explode(',', array_shift($parts));

        if (count($sensor) < 3) {
            return null;
        }

        $states = [];
        foreach ($parts as $state) {
            $state = explode(',', $state);
            if (count($state) >= 3) {
                $states[] = ['value' => (int) $state[0], 'generic' => (int) $state[1], 'descr' => $state[2]];
            }
        }

        return [
            'name' => $sensor[0],
            'type' => $sensor[1],
            'descr' => $sensor[2],
            'low_limit' => $sensor[3] ?? null,
            'low_warn_limit' => $sensor[4] ?? null,
            'warn_limit' => $sensor[5] ?? null,
            'high_limit' => $sensor[6] ?? null,
            'group' => $sensor[7] ?? null,
            'states' => $states,
        ];
    }

    /**
     * Raspberry Pi (raspberry extend), NTC CHIP (power-stat extend) and HP iLO sensors
     *
     * @return Collection<int, Sensor>
     */
    private function discoverLinuxSensors(): Collection
    {
        $sensors = new Collection;
        $sensor = fn (SensorType $type, string $oid, int|string $index, string $sensorType, string $descr, mixed $value, array $extra = []) => new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => $type->value,
            'sensor_oid' => $oid,
            'sensor_index' => $index,
            'sensor_type' => $sensorType,
            'sensor_descr' => $descr,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_current' => $value,
            'rrd_type' => 'GAUGE',
            ...$extra,
        ]);

        // raspberry extend: 1 temperature, 2-5 voltages, 6-7 frequencies, 8-13 codecs
        $raspberry = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121';
        $lines = [];
        foreach (SnmpQuery::numeric()->walk($raspberry)->values() as $oid => $value) {
            $lines[(int) substr((string) $oid, strlen($raspberry) + 1)] = trim((string) $value, '"');
        }

        if (is_numeric($lines[1] ?? null)) {
            $sensors->push($sensor(SensorType::Temperature, "$raspberry.1", 1, 'raspberry_temp', 'CPU Temp', $lines[1]));
        }
        foreach ([
            [SensorType::Voltage, 'raspberry_volts', [2 => 'Core', 3 => 'SDRAMc', 4 => 'SDRAMi', 5 => 'SDRAMp']],
            [SensorType::Frequency, 'raspberry_freq', [6 => 'ARM', 7 => 'Core']],
        ] as [$type, $sensorType, $descriptions]) {
            foreach ($descriptions as $line => $descr) {
                if (! is_numeric($lines[$line] ?? null)) {
                    break;
                }
                $sensors->push($sensor($type, "$raspberry.$line", $line, $sensorType, $descr, $lines[$line]));
            }
        }
        foreach ([8 => 'H264 codec', 9 => 'MPG2 codec', 10 => 'WVC1 codec', 11 => 'MPG4 codec', 12 => 'MJPG codec', 13 => 'WMV9 codec'] as $line => $descr) {
            if (stripos($lines[$line] ?? '', 'abled') === false) {
                break;
            }
            $value = match (strtolower($lines[$line])) {
                'enabled' => 2,
                'disabled' => 3,
                default => null,
            };
            $sensors->push($sensor(SensorType::State, "$raspberry.$line", $line, 'raspberry_codec', $descr, $value, ['entPhysicalIndex' => $line])->withStates([
                ['value' => 2, 'generic' => 0, 'descr' => 'enabled'],
                ['value' => 3, 'generic' => 3, 'descr' => 'disabled'],
            ]));
        }

        if (preg_match('/(Linux).+(ntc)/', (string) $this->getDevice()->sysDescr)) {
            $powerStat = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.116';
            $values = SnmpQuery::numeric()->get(array_map(fn ($line) => "$powerStat.$line", range(1, 8)))->values();
            $chargerLimits = ['sensor_limit_low' => 3.8, 'sensor_limit_low_warn' => 3.8, 'sensor_limit_warn' => 6.3, 'sensor_limit' => 6.3];
            foreach ([
                1 => [SensorType::Temperature, 'chip_axp209_temperature', 'AXP209 Temperature', ['sensor_limit_low' => -40, 'sensor_limit_low_warn' => -35, 'sensor_limit_warn' => 120, 'sensor_limit' => 130]],
                2 => [SensorType::Voltage, 'chip_volts', 'AC IN voltage', $chargerLimits],
                3 => [SensorType::Current, 'chip_currents', 'AC IN current', ['sensor_limit_low' => 0, 'sensor_limit_low_warn' => 0]],
                4 => [SensorType::Voltage, 'chip_volts', 'VBUS voltage', $chargerLimits],
                5 => [SensorType::Current, 'chip_currents', 'VBUS current', ['sensor_limit_low' => 0, 'sensor_limit_low_warn' => 0]],
                6 => [SensorType::Voltage, 'chip_volts', 'Battery voltage', ['sensor_limit_low' => 2.75, 'sensor_limit_low_warn' => 2.8, 'sensor_limit_warn' => 4.2, 'sensor_limit' => 4.2]],
                7 => [SensorType::Current, 'chip_currents', 'Battery current', ['sensor_limit_low' => 0, 'sensor_limit_low_warn' => 0]],
                8 => [SensorType::Charge, 'chip_battery_charge', 'Battery charge', ['sensor_limit_low' => 5, 'sensor_limit_low_warn' => 9]],
            ] as $line => [$type, $sensorType, $descr, $limits]) {
                if (is_numeric($values["$powerStat.$line"] ?? null)) {
                    $sensors->push($sensor($type, "$powerStat.$line", "116.$line", $sensorType, $descr, $values["$powerStat.$line"], $limits));
                }
            }
        }

        if (Str::startsWith((string) $this->getDevice()->sysObjectID, '.1.3.6.1.4.1.232.')) {
            // CPQHLTH-MIB::cpqHeTemperatureTable of chassis 1
            $temperatures = SnmpQuery::numeric()->mibDir('hp')->walk([
                '.1.3.6.1.4.1.232.6.2.6.8.1.2.1',
                '.1.3.6.1.4.1.232.6.2.6.8.1.3.1',
                '.1.3.6.1.4.1.232.6.2.6.8.1.4.1',
                '.1.3.6.1.4.1.232.6.2.6.8.1.5.1',
            ])->values();
            foreach ($temperatures as $oid => $value) {
                if (! str_starts_with((string) $oid, '.1.3.6.1.4.1.232.6.2.6.8.1.2.1.')) {
                    continue;
                }
                $id = '1.' . substr((string) $oid, strrpos((string) $oid, '.') + 1);
                $temperature = $temperatures[".1.3.6.1.4.1.232.6.2.6.8.1.4.$id"] ?? null;
                if (! empty($temperature)) {
                    $sensors->push($sensor(SensorType::Temperature, ".1.3.6.1.4.1.232.6.2.6.8.1.4.$id", $oid, 'hpilo', (string) ($temperatures[".1.3.6.1.4.1.232.6.2.6.8.1.3.$id"] ?? ''), $temperature, [
                        'sensor_limit' => $temperatures[".1.3.6.1.4.1.232.6.2.6.8.1.5.$id"] ?? null,
                    ]));
                }
            }
        }

        return $sensors;
    }
}
