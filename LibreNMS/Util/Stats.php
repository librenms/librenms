<?php
/*
 * Stats.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Callback;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Stats
{
    public static function submit(): void
    {
        $stats = new static;

        if ($stats->isEnabled()) {
            Http::client()
                ->asForm()
                ->post(\LibreNMS\Config::get('callback_post'), [
                    'data' => json_encode($stats->collectData()),
                ]);
        }
    }

    public function isEnabled(): bool
    {
        $enabled = Callback::get('enabled');

        if ($enabled == 2) {
            $this->clearStats();

            return false;
        }

        return $enabled == 1;
    }

    public function clearStats(): void
    {
        $uuid = Callback::get('uuid');

        $response = Http::client()
            ->asForm()
            ->post(\LibreNMS\Config::get('callback_clear'), ['uuid' => $uuid]);

        if ($response->successful()) {
            Callback::where('name', 'uuid')->delete();
            Callback::set('enabled', 0);
        }
    }

    private function collectData(): array
    {
        return [
            'uuid' => $this->getUuid(),
            'data' => $this->collectStats(),
            'info' => $this->collectDeviceInfo(),
        ];
    }

    private function getUuid(): string
    {
        $uuid = Callback::get('uuid');

        if (! $uuid) {
            $uuid = Str::uuid();
            Callback::set('uuid', $uuid);
        }

        return $uuid;
    }

    private function collectStats(): array
    {
        $version = Version::get();

        return [
            'alert_rules' => $this->selectTotal(DB::table('alert_rules')->where('disabled', 0), ['severity']),
            'alert_templates' => $this->selectTotal('alert_templates'),
            'api_tokens' => $this->selectTotal(DB::table('api_tokens')->where('disabled', 0)),
            'applications' => $this->selectTotal('applications', ['app_type']),
            'bgppeer_state' => $this->selectTotal('bgpPeers', ['bgpPeerState']),
            'bgppeer_status' => $this->selectTotal('bgpPeers', ['bgpPeerAdminStatus']),
            'bills' => $this->selectTotal('bills', ['bill_type']),
            'cef' => $this->selectTotal('cef_switching'),
            'cisco_asa' => $this->selectTotal(DB::table('ciscoASA')->where('disabled', 0), ['oid']),
            'mempool' => $this->selectTotal('mempools', ['mempool_descr']),
            'dbschema' => $this->selectStatic(DB::table('migrations')->count()),
            'snmp_version' => $this->selectTotal('devices', ['snmpver']),
            'os' => $this->selectTotal('devices', ['os']),
            'type' => $this->selectTotal('devices', ['type']),
            'hardware' => $this->selectTotal('devices', ['hardware']),
            'ipsec' => $this->selectTotal('ipsec_tunnels'),
            'ipv4_addresses' => $this->selectTotal('ipv4_addresses'),
            'ipv4_macaddress' => $this->selectTotal('ipv4_mac'),
            'ipv4_networks' => $this->selectTotal('ipv4_networks'),
            'ipv6_addresses' => $this->selectTotal('ipv6_addresses'),
            'ipv6_networks' => $this->selectTotal('ipv6_networks'),
            'xdp' => $this->selectTotal('links', ['protocol']),
            'ospf' => $this->selectTotal('ospf_instances', ['ospfVersionNumber']),
            'ospf_links' => $this->selectTotal('ospf_ports', ['ospfIfType']),
            'arch' => $this->selectTotal('packages', ['arch']),
            'pollers' => $this->selectTotal('pollers'),
            'port_type' => $this->selectTotal('ports', ['ifType']),
            'port_ifspeed' => DB::table('ports')->select([DB::raw('COUNT(*) AS `total`'), DB::raw('ROUND(`ifSpeed`/1000/1000) as ifSpeed')])->groupBy(['ifSpeed'])->get(),
            'port_vlans' => $this->selectTotal('ports_vlans', ['state']),
            'processes' => $this->selectTotal('processes'),
            'processors' => $this->selectTotal('processors', ['processor_type']),
            'pseudowires' => $this->selectTotal('pseudowires'),
            'sensors' => $this->selectTotal('sensors', ['sensor_class']),
            'sla' => $this->selectTotal('slas', ['rtt_type']),
            'wireless' => $this->selectTotal('wireless_sensors', ['sensor_class']),
            'storage' => $this->selectTotal('storage', ['storage_type']),
            'toner' => $this->selectTotal('printer_supplies', ['supply_type']),
            'vlans' => $this->selectTotal('vlans', ['vlan_type']),
            'vminfo' => $this->selectTotal('vminfo', ['vm_type']),
            'vmware' => $this->selectTotal('vminfo'),
            'vrfs' => $this->selectTotal('vrfs'),
            'database_version' => $this->selectStatic($version->databaseServer()),
            'php_version' => $this->selectStatic(phpversion()),
            'python_version' => $this->selectStatic($version->python()),
            'rrdtool_version' => $this->selectStatic($version->rrdtool()),
            'netsnmp_version' => $this->selectStatic($version->netSnmp()),
            'os_version' => $this->selectStatic($version->os()),
            'librenms_release' => $this->selectStatic($version->release(), 'release'),
        ];
    }

    private function collectDeviceInfo(): Collection
    {
        $device_info = DB::table('devices')
            ->select([DB::raw('COUNT(*) AS `count`'), 'os', 'sysDescr', 'sysObjectID'])
            ->whereNotNull(['sysDescr', 'sysObjectID'])
            ->groupBy(['os', 'sysDescr', 'sysObjectID'])
            ->get();

        // sanitize sysDescr
        return $device_info->map(function ($entry) {
            // remove hostnames from linux, macosx, and SunOS
            $entry->sysDescr = preg_replace_callback('/^(Linux |Darwin |FreeBSD |SunOS )[A-Za-z0-9._\-]+ ([0-9.]{3,9})/', function ($matches) {
                return $matches[1] . 'hostname ' . $matches[2];
            }, $entry->sysDescr);

            // wipe serial numbers, preserve the format
            $sn_patterns = ['/[A-Z]/', '/[a-z]/', '/[0-9]/'];
            $sn_replacements = ['A', 'a', '0'];
            $entry->sysDescr = preg_replace_callback(
                '/((s\/?n|serial num(ber)?)[:=]? ?)([a-z0-9.\-]{4,16})/i',
                function ($matches) use ($sn_patterns, $sn_replacements) {
                    return $matches[1] . preg_replace($sn_patterns, $sn_replacements, $matches[4]);
                },
                $entry->sysDescr
            );

            return $entry;
        });
    }

    /**
     * @param  Builder|string  $table
     * @param  array  $groups
     * @return \Illuminate\Support\Collection
     */
    private function selectTotal($table, array $groups = []): Collection
    {
        $query = $table instanceof Builder ? $table : DB::table($table);

        if (! empty($groups)) {
            $query->groupBy($groups);
        }

        return $query
            ->select(array_merge([DB::raw('COUNT(*) AS `total`')], $groups))
            ->get();
    }

    /**
     * @param  string|int|float  $value
     * @param  string  $name
     * @return array[]
     */
    private function selectStatic($value, string $name = 'version'): array
    {
        return [['total' => 1, $name => $value]];
    }
}
