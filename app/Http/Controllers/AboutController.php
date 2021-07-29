<?php
/**
 * AboutController.php
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
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers;

use App;
use App\Models\Application;
use App\Models\Callback;
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
use App\Models\Port;
use App\Models\PrinterSupply;
use App\Models\Processor;
use App\Models\Pseudowire;
use App\Models\Sensor;
use App\Models\Service;
use App\Models\Sla;
use App\Models\Storage;
use App\Models\Syslog;
use App\Models\Vlan;
use App\Models\Vrf;
use App\Models\WirelessSensor;
use DB;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Version;

class AboutController extends Controller
{
    public function index(Request $request)
    {
        $callback_status = Callback::get('enabled') === '1';
        $version = Version::get();

        return view('about.index', [
            'callback_status' => $callback_status,
            'callback_uuid'   => $callback_status ? Callback::get('uuid') : null,

            'db_schema' => vsprintf('%s (%s)', $version->database()),
            'git_log'   => $version->gitChangelog(),
            'git_date'  => $version->gitDate(),
            'project_name' => Config::get('project_name'),

            'version_local'     => $version->local(),
            'version_mysql'     => current(DB::selectOne('select version()')),
            'version_php'       => phpversion(),
            'version_laravel'   => App::VERSION(),
            'version_python'    => Version::python(),
            'version_webserver' => $request->server('SERVER_SOFTWARE'),
            'version_rrdtool'   => str_replace('1.7.01.7.0', '1.7.0', implode(' ', array_slice(explode(' ', shell_exec(
                Config::get('rrdtool', 'rrdtool') . ' --version | head -n1'
            )), 1, 1))),
            'version_netsnmp'   => str_replace('version: ', '', rtrim(shell_exec(Config::get('snmpget', 'snmpget') . ' -V 2>&1'))),

            'stat_apps'       => Application::count(),
            'stat_devices'    => Device::count(),
            'stat_diskio'     => DiskIo::count(),
            'stat_entphys'    => EntPhysical::count(),
            'stat_events'     => Eventlog::count(),
            'stat_hrdev'      => HrDevice::count(),
            'stat_ipv4_addy'  => Ipv4Address::count(),
            'stat_ipv4_nets'  => Ipv4Network::count(),
            'stat_ipv6_addy'  => Ipv6Address::count(),
            'stat_ipv6_nets'  => Ipv6Network::count(),
            'stat_memory'     => Mempool::count(),
            'stat_ports'      => Port::count(),
            'stat_processors' => Processor::count(),
            'stat_pw'         => Pseudowire::count(),
            'stat_sensors'    => Sensor::count(),
            'stat_services'   => Service::count(),
            'stat_slas'       => Sla::count(),
            'stat_storage'    => Storage::count(),
            'stat_syslog'     => Syslog::count(),
            'stat_toner'      => PrinterSupply::count(),
            'stat_vlans'      => Vlan::count(),
            'stat_vrf'        => Vrf::count(),
            'stat_wireless'   => WirelessSensor::count(),
        ]);
    }
}
