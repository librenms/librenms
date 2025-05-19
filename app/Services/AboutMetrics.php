<?php

/*
 * AboutMetrics.php
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
 * @copyright  2025 Peter Childs
 * @author     Peter Childs <pjchilds@gmail.com>
 */

namespace App\Services;

use App\Models\Application;
use App\Models\Device;
use App\Models\DiskIo;
use App\Models\EntPhysical;
use App\Models\Eventlog;
use App\Models\HrDevice;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use App\Models\Mempool;
use App\Models\Qos;
use App\Models\Port;
use App\Models\Processor;
use App\Models\Pseudowire;
use App\Models\Sensor;
use App\Models\Service;
use App\Models\Sla;
use App\Models\Storage;
use App\Models\Syslog;
use App\Models\PrinterSupply;
use App\Models\Vlan;
use App\Models\Vrf;
use App\Models\WirelessSensor;

/**
 * Service to collect all "stat_" metrics for the About page.
 */
class AboutMetrics
{
    /**
     * Gather all dynamic counts used on the About page.
     *
     * @return array<string,int>
     */
    public function collect(): array
    {
        return [
            'stat_apps' => Application::count(),
            'stat_devices' => Device::count(),
            'stat_diskio' => DiskIo::count(),
            'stat_entphys' => EntPhysical::count(),
            'stat_events' => Eventlog::count(),
            'stat_hrdev' => HrDevice::count(),
            'stat_ipv4_addy' => Ipv4Address::count(),
            'stat_ipv4_nets' => Ipv4Network::count(),
            'stat_ipv6_addy' => Ipv6Address::count(),
            'stat_ipv6_nets' => Ipv6Network::count(),
            'stat_memory' => Mempool::count(),
            'stat_qos' => Qos::count(),
            'stat_ports' => Port::count(),
            'stat_processors' => Processor::count(),
            'stat_pw' => Pseudowire::count(),
            'stat_sensors' => Sensor::count(),
            'stat_services' => Service::count(),
            'stat_slas' => Sla::count(),
            'stat_storage' => Storage::count(),
            'stat_syslog' => Syslog::count(),
            'stat_toner' => PrinterSupply::count(),
            'stat_vlans' => Vlan::count(),
            'stat_vrf' => Vrf::count(),
            'stat_wireless' => WirelessSensor::count(),
        ];
    }
}

