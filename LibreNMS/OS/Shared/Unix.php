<?php
/*
 * Unix.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Shared;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\OS\Traits\ServerHardware;
use LibreNMS\OS\Traits\YamlOSDiscovery;

class Unix extends \LibreNMS\OS implements MempoolsDiscovery
{
    use ServerHardware;
    use YamlOSDiscovery {
        YamlOSDiscovery::discoverOS as discoverYamlOS;
    }

    public function discoverOS(Device $device): void
    {
        // yaml discovery overrides this
        if ($this->hasYamlDiscovery('os')) {
            $this->discoverYamlOS($device);
            $this->discoverServerHardware();
            $this->discoverExtends($device);

            return;
        }

        preg_match('/ (\d+\.\d\S*) /', $device->sysDescr, $matches);
        $device->version = $matches[1] ?? $device->version;
        if (preg_match('/i[3-6]86/', $device->sysDescr)) {
            $device->hardware = 'Generic x86';
        } elseif (Str::contains($device->sysDescr, 'x86_64')) {
            $device->hardware = 'Generic x86 64-bit';
        } elseif (Str::contains($device->sysDescr, 'sparc32')) {
            $device->hardware = 'Generic SPARC 32-bit';
        } elseif (Str::contains($device->sysDescr, 'sparc64')) {
            $device->hardware = 'Generic SPARC 64-bit';
        } elseif (Str::contains($device->sysDescr, 'mips')) {
            $device->hardware = 'Generic MIPS';
        } elseif (Str::contains($device->sysDescr, 'armv5')) {
            $device->hardware = 'Generic ARMv5';
        } elseif (Str::contains($device->sysDescr, 'armv6')) {
            $device->hardware = 'Generic ARMv6';
        } elseif (Str::contains($device->sysDescr, 'armv7')) {
            $device->hardware = 'Generic ARMv7';
        } elseif (Str::contains($device->sysDescr, 'aarch64')) {
            $device->hardware = 'Generic ARMv8 64-bit';
        } elseif (Str::contains($device->sysDescr, 'armv')) {
            $device->hardware = 'Generic ARM';
        }

        $this->discoverServerHardware();
        $this->discoverExtends($device);
    }

    protected function discoverExtends(Device $device)
    {
        // Distro "extend" support
        $features_extend = snmp_get_multi_oid($this->getDeviceArray(), [
            '.1.3.6.1.4.1.8072.1.3.2.3.1.1.6.100.105.115.116.114.111', // NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"distro\"
            '.1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111', // UCD-MIB shell
            '.1.3.6.1.4.1.2021.7890.1.101.1', // exec
        ], '-OUQn', 'NET-SNMP-EXTEND-MIB');
        $features = reset($features_extend);
        $device->features = $features ?: $device->features;

        // Try detect using the extended option (dmidecode)
        $hardware_extend = snmp_get_multi_oid($this->getDeviceArray(), [
            '.1.3.6.1.4.1.8072.1.3.2.3.1.1.8.104.97.114.100.119.97.114.101', // NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"hardware\"
            '.1.3.6.1.4.1.2021.7890.3.4.1.2.12.109.97.110.117.102.97.99.116.117.114.101.114.1', // UCD-MIB shell
            '.1.3.6.1.4.1.2021.7890.3.101.1', // UCD-MIB exec
        ], '-OUQn', 'NET-SNMP-EXTEND-MIB');
        $hardware = reset($hardware_extend);

        if ($hardware) {
            //  NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"manufacturer\"
            $manufacturer = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.8072.1.3.2.3.1.1.12.109.97.110.117.102.97.99.116.117.114.101.114', '-Oqv', 'NET-SNMP-EXTEND-MIB');
            if ($manufacturer) {
                $hardware = Str::start($hardware, $manufacturer . ' ');
            }

            $version_extend = snmp_get_multi_oid($this->getDeviceArray(), [
                '.1.3.6.1.4.1.8072.1.3.2.3.1.1.7.118.101.114.115.105.111.110', // NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"version\"
                '.1.3.6.1.4.1.2021.7890.2.4.1.2.8.104.97.114.100.119.97.114.101.1', // UCD-MIB shell
                '.1.3.6.1.4.1.2021.7890.2.101.1', // UCD-MIB exec
            ], '-OUQn', 'NET-SNMP-EXTEND-MIB');
            $version = reset($version_extend);
            if ($version) {
                $hardware .= " [$version]";
            }
            $device->hardware = $hardware;
        }

        $serial_extend = snmp_get_multi_oid($this->getDeviceArray(), [
            '.1.3.6.1.4.1.674.10892.1.300.10.1.11.1', // Dell
            '.1.3.6.1.4.1.8072.1.3.2.3.1.1.6.115.101.114.105.97.108', // NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"serial\"
            '.1.3.6.1.4.1.2021.7890.4.4.1.2.6.115.101.114.105.97.108.1', // UCD-MIB shell
        ], '-OUQn', 'NET-SNMP-EXTEND-MIB:MIB-Dell-10892');
        $serial = reset($serial_extend);
        $device->serial = $serial ?: $device->serial;
    }
}
