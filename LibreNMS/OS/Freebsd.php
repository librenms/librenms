<?php
/*
 * Freebsd.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

class Freebsd extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        parent::discoverOS(); // yaml

        $device = $this->getDeviceModel();

        # Distro "extend" support

        # NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"distro\"
        $features = snmp_get($this->getDevice(), ".1.3.6.1.4.1.8072.1.3.2.3.1.1.6.100.105.115.116.114.111", "-Oqv", "NET-SNMP-EXTEND-MIB");

        if (!$features) { # No "extend" support, try legacy UCD-MIB shell support
            $features = snmp_get($this->getDevice(), ".1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111", "-Oqv", "UCD-SNMP-MIB");
        }

        if (!$features) { # No "extend" support, try "exec" support
            $features = snmp_get($this->getDevice(), ".1.3.6.1.4.1.2021.7890.1.101.1", "-Oqv", "UCD-SNMP-MIB");
        }

        $device->features = $features ?: 'GENERIC';


        # Try detect using the extended option (dmidecode)
        $version_dmide = snmp_get($this->getDevice(), ".1.3.6.1.4.1.2021.7890.2.4.1.2.8.104.97.114.100.119.97.114.101.1", "-Oqv");
        if (!$version_dmide) { # No "extend" support, try "exec" support
            $version_dmide = snmp_get($this->getDevice(), ".1.3.6.1.4.1.2021.7890.2.101.1", "-Oqv");
        }
        $version_dmide = trim($version_dmide);

        $hardware_dmide = snmp_get($this->getDevice(), ".1.3.6.1.4.1.2021.7890.3.4.1.2.12.109.97.110.117.102.97.99.116.117.114.101.114.1", "-Oqv");
        if (!$hardware_dmide) { # No "extend" support, try "exec" support
            $hardware_dmide = snmp_get($this->getDevice(), ".1.3.6.1.4.1.2021.7890.3.101.1", "-Oqv");
        }
        $hardware_dmide = trim($hardware_dmide);
        if ($hardware_dmide) {
            $hardware = $hardware_dmide;
            if ($version_dmide) {
                $hardware = $hardware . " [" . $version_dmide . "]";
            }
        }

        $device->hardware = $hardware ?? null;
    }
}
