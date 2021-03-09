<?php
/*
 * Windows.php
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

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS\Traits\ServerHardware;

class Windows extends \LibreNMS\OS
{
    use ServerHardware;

    public function discoverOS(Device $device): void
    {
        if (preg_match('/Hardware: (?<hardware>.*) +- Software: .* Version (?<nt>\S+) +\(Build( Number:)? (?<build>\S+) (?<smp>\S+)/', $device->sysDescr, $matches)) {
            $device->hardware = $this->parseHardware($matches['hardware']);
            $device->features = $matches['smp'];

            if ($device->sysObjectID == '.1.3.6.1.4.1.311.1.1.3.1.1') {
                $device->version = $this->getClientVersion($matches['build'], $matches['version']);
            } elseif ($device->sysObjectID == '.1.3.6.1.4.1.311.1.1.3.1.2') {
                $device->version = $this->getServerVersion($matches['build']);
            } elseif ($device->sysObjectID == '.1.3.6.1.4.1.311.1.1.3.1.3') {
                $device->version = $this->getDatacenterVersion($matches['build']);
            }
        }

        $this->discoverServerHardware();
    }

    private function parseHardware($processor)
    {
        preg_match('/(?<generic>\S+) Family (?<family>\d+) Model (?<model>\d+) Stepping (?<stepping>\d+)/', $processor, $matches);

        $generic = [
            'AMD64' => 'AMD x64',
            'Intel64' => 'Intel x64',
            'EM64T' => 'Intel x64',
            'x86' => 'Generic x86',
            'ia64' => 'Intel Itanium IA64',
        ];

        return $generic[$matches['generic']] ?? null;
    }

    private function getClientVersion($build, $version)
    {
        $default = $build > 10000 ? '10 (NT 6.3)' : null;

        $builds = [
            '19041' => '10 2004 (NT 6.3)',
            '18363' => '10 1909 (NT 6.3)',
            '18362' => '10 1903 (NT 6.3)',
            '17763' => '10 1809 (NT 6.3)',
            '17134' => '10 1803 (NT 6.3)',
            '16299' => '10 1709 (NT 6.3)',
            '15063' => '10 1703 (NT 6.3)',
            '14393' => '10 1607 (NT 6.3)',
            '10586' => '10 1511 (NT 6.3)',
            '10240' => '10 1507 (NT 6.3)',
            '9600' => '8.1 U1 (NT 6.3)',
            '9200' => $version == '6.3' ? '8.1 (NT 6.3)' : '8 (NT 6.2)',
            '7601' => '7 SP1 (NT 6.1)',
            '7600' => '7 (NT 6.1)',
            '6002' => 'Vista SP2 (NT 6.0)',
            '6001' => 'Vista SP1 (NT 6.0)',
            '6000' => 'Vista (NT 6.0)',
            '3790' => 'XP x64 (NT 5.2)',
            '2600' => 'XP (NT 5.1)',
            '2195' => '2000 (NT 5.0)',
            '1381' => 'NT 4.0 Workstation',
            '1057' => 'NT 3.51 Workstation',
        ];

        return $builds[$build] ?? $default;
    }

    private function getServerVersion($build)
    {
        $builds = [
            '17763' => 'Server 2019 1809 (NT 6.3)',
            '16299' => 'Server 2016 1709 (NT 6.3)',
            '14393' => 'Server 2016 (NT 6.3)',
            '9600' => 'Server 2012 R2 (NT 6.3)',
            '9200' => 'Server 2012 (NT 6.2)',
            '7601' => 'Server 2008 R2 SP1 (NT 6.1)',
            '7600' => 'Server 2008 R2 (NT 6.1)',
            '6003' => 'Server 2008 SP2 (NT 6.0)',
            '6002' => 'Server 2008 SP2 (NT 6.0)',
            '6001' => 'Server 2008 (NT 6.0)',
            '3790' => 'Server 2003 (NT 5.2)',
            '2195' => '2000 Server (NT 5.0)',
            '1381' => 'NT Server 4.0',
            '1057' => 'NT Server 3.51',
        ];

        return $builds[$build] ?? null;
    }

    private function getDatacenterVersion($build)
    {
        $builds = [
            '17763' => 'Server 2019 1809 Datacenter (NT 6.3)',
            '16299' => 'Server 2016 1709 Datacenter (NT 6.3)',
            '14393' => 'Server 2016 1607 Datacenter (NT 6.3)',
            '9600' => 'Server 2012 R2 Datacenter (NT 6.3)',
            '9200' => 'Server 2012 Datacenter (NT 6.2)',
            '7601' => 'Server 2008 Datacenter R2 SP1 (NT 6.1)',
            '7600' => 'Server 2008 Datacenter R2 (NT 6.1)',
            '6002' => 'Server 2008 Datacenter SP2 (NT 6.0)',
            '6001' => 'Server 2008 Datacenter (NT 6.0)',
            '3790' => 'Server 2003 Datacenter (NT 5.2)',
            '2195' => '2000 Datacenter Server (NT 5.0)',
            '1381' => 'NT Datacenter 4.0',
            '1057' => 'NT Datacenter 3.51',
        ];

        return $builds[$build] ?? null;
    }
}
