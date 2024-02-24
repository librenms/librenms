<?php
/**
 * FdbTablesController.php
 *
 * FDB tables data for bootgrid display
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Ipv4Mac;
use App\Models\Port;
use App\Models\PortsFdb;
use App\Models\Vlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\IP;
use LibreNMS\Util\Mac;
use LibreNMS\Util\Url;

class FdbTablesController extends TableController
{
    protected $macCountCache = [];

    protected function rules()
    {
        return [
            'port_id' => 'nullable|integer',
            'device_id' => 'nullable|integer',
            'searchby' => 'in:mac,vlan,dnsname,ip,description,first_seen,last_seen,vendor,',
            'dns' => 'nullable|in:true,false',
        ];
    }

    protected function filterFields($request)
    {
        return [
            'ports_fdb.device_id' => 'device_id',
            'ports_fdb.port_id' => 'port_id',
        ];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return PortsFdb::hasAccess($request->user())
            ->with(['device', 'port', 'vlan', 'ipv4Addresses']);
    }

    /**
     * @param  string  $search
     * @param  Builder  $query
     * @param  array  $fields
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    protected function search($search, $query, $fields = [])
    {
        if ($search = trim(\Request::get('searchPhrase') ?? '')) {
            $mac_search = '%' . str_replace([':', ' ', '-', '.', '0x'], '', $search) . '%';
            switch (\Request::get('searchby') ?? '') {
                case 'mac':
                    return $query->where('ports_fdb.mac_address', 'like', $mac_search);
                case 'vlan':
                    return $query->whereIntegerInRaw('ports_fdb.vlan_id', $this->findVlans($search));
                case 'dnsname':
                    $search = gethostbyname($search);
                    // no break
                case 'ip':
                    return $query->whereIn('ports_fdb.mac_address', $this->findMacs($search));
                case 'description':
                    return $query->whereIntegerInRaw('ports_fdb.port_id', $this->findPorts($search));
                case 'vendor':
                    $vendor_ouis = $this->ouisFromVendor($search);

                    return $this->findPortsByOui($vendor_ouis, $query);
                default:
                    return $query->where(function ($query) use ($search, $mac_search) {
                        $query->where('ports_fdb.mac_address', 'like', $mac_search)
                            ->orWhereIntegerInRaw('ports_fdb.port_id', $this->findPorts($search))
                            ->orWhereIntegerInRaw('ports_fdb.vlan_id', $this->findVlans($search))
                            ->orWhereIn('ports_fdb.mac_address', $this->findMacs($search));
                    });
            }
        }

        return $query;
    }

    /**
     * @param  Request  $request
     * @param  Builder  $query
     * @return Builder
     */
    public function sort($request, $query)
    {
        $sort = $request->get('sort');

        if (isset($sort['mac_address'])) {
            $query->orderBy('mac_address', $sort['mac_address'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['device'])) {
            $query->leftJoin('devices', 'ports_fdb.device_id', 'devices.device_id')
                ->orderBy('hostname', $sort['device'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['vlan'])) {
            $query->leftJoin('vlans', 'ports_fdb.vlan_id', 'vlans.vlan_id')
                ->orderBy('vlan_vlan', $sort['vlan'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['interface'])) {
            $query->leftJoin('ports', 'ports_fdb.port_id', 'ports.port_id')
                ->orderBy('ports.ifDescr', $sort['interface'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['description'])) {
            $query->leftJoin('ports', 'ports_fdb.port_id', 'ports.port_id')
                ->orderBy('ports.ifDescr', $sort['description'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['last_seen'])) {
            $query->orderBy('updated_at', $sort['last_seen'] == 'desc' ? 'desc' : 'asc');
        }

        if (isset($sort['first_seen'])) {
            $query->orderBy('created_at', $sort['first_seen'] == 'desc' ? 'desc' : 'asc');
        }

        return $query;
    }

    /**
     * @param  PortsFdb  $fdb_entry
     */
    public function formatItem($fdb_entry)
    {
        $mac = Mac::parse($fdb_entry->mac_address);
        $ips = $fdb_entry->ipv4Addresses->pluck('ipv4_address');

        $item = [
            'device' => $fdb_entry->device ? Url::deviceLink($fdb_entry->device) : '',
            'mac_address' => $mac->readable(),
            'mac_oui' => $mac->vendor(),
            'ipv4_address' => $ips->implode(', '),
            'interface' => '',
            'vlan' => $fdb_entry->vlan ? $fdb_entry->vlan->vlan_vlan : '',
            'description' => '',
            'dnsname' => $this->resolveDns($ips),
            'first_seen' => 'unknown',
            'last_seen' => 'unknown',
        ];

        // diffForHumans and doDateTimeString are not safe
        if ($fdb_entry->updated_at) {
            $item['last_seen'] = $fdb_entry->updated_at->diffForHumans();
        }
        if ($fdb_entry->created_at) {
            $item['first_seen'] = $fdb_entry->created_at->toDateTimeString();
        }

        if ($fdb_entry->port) {
            $item['interface'] = Url::portLink($fdb_entry->port, $fdb_entry->port->getShortLabel());
            $item['description'] = $fdb_entry->port->ifAlias;
            if ($fdb_entry->port->ifInErrors > 0 || $fdb_entry->port->ifOutErrors > 0) {
                $item['interface'] .= ' ' . Url::portLink($fdb_entry->port, '<i class="fa fa-flag fa-lg" style="color:red" aria-hidden="true"></i>');
            }
            if ($this->getMacCount($fdb_entry->port) == 1) {
                // only one mac on this port, likely the endpoint
                $item['interface'] .= ' <i class="fa fa-star fa-lg" style="color:green" aria-hidden="true" title="' . __('This indicates the most likely endpoint switchport') . '"></i>';
            }
        }

        return $item;
    }

    /**
     * @param  string  $ip
     * @return Collection
     */
    protected function findMacs($ip): Collection
    {
        $port_id = \Request::get('port_id');
        $device_id = \Request::get('device_id');

        return Ipv4Mac::where('ipv4_address', 'like', "%$ip%")
            ->when($device_id, function ($query) use ($device_id) {
                return $query->where('device_id', $device_id);
            })
            ->when($port_id, function ($query) use ($port_id) {
                return $query->where('port_id', $port_id);
            })
            ->pluck('mac_address');
    }

    /**
     * @param  string  $vlan
     * @return Collection
     */
    protected function findVlans($vlan): Collection
    {
        $port_id = \Request::get('port_id');
        $device_id = \Request::get('device_id');

        return Vlan::where('vlan_vlan', $vlan)
            ->when($device_id, function ($query) use ($device_id) {
                return $query->where('device_id', $device_id);
            })
            ->when($port_id, function ($query) use ($port_id) {
                return $query->whereIn('device_id', function ($query) use ($port_id) {
                    $query->select('device_id')->from('ports')->where('port_id', $port_id);
                });
            })
            ->pluck('vlan_id');
    }

    /**
     * @param  string  $ifAlias
     * @return Collection
     */
    protected function findPorts($ifAlias): Collection
    {
        $port_id = \Request::get('port_id');
        $device_id = \Request::get('device_id');

        return Port::where('ifAlias', 'like', "%$ifAlias%")
            ->when($device_id, function ($query) use ($device_id) {
                return $query->where('device_id', $device_id);
            })
            ->when($port_id, function ($query) use ($port_id) {
                return $query->where('port_id', $port_id);
            })
            ->pluck('port_id');
    }

    private function resolveDns(Collection $ips): string
    {
        $dns = 'N/A';

        // only fetch DNS if the column is visible
        if (\Request::get('dns') == 'true') {
            // don't try too many dns queries, this is the slowest part
            foreach ($ips->take(3) as $ip) {
                $hostname = gethostbyaddr($ip);
                if (! IP::isValid($hostname)) {
                    return $hostname;
                }
            }
        }

        return $dns;
    }

    /**
     * @param  Port  $port
     * @return int
     */
    protected function getMacCount($port)
    {
        if (! isset($this->macCountCache[$port->port_id])) {
            $this->macCountCache[$port->port_id] = $port->fdbEntries()->count();
        }

        return $this->macCountCache[$port->port_id];
    }

    /**
     * Get the OUI list for a specific vendor
     *
     * @param  string  $vendor
     * @return array
     */
    protected function ouisFromVendor(string $vendor): array
    {
        return DB::table('vendor_ouis')
            ->where('vendor', 'LIKE', '%' . $vendor . '%')
            ->pluck('oui')
            ->toArray();
    }

    /**
     * Get all port ids from vendor OUIs
     */
    protected function findPortsByOui(array $vendor_ouis, Builder $query): Builder
    {
        $query->where(function (Builder $query) use ($vendor_ouis) {
            foreach ($vendor_ouis as $oui) {
                $query->orWhere('ports_fdb.mac_address', 'LIKE', "$oui%");
            }
        });

        return $query; // Return the query builder instance
    }
}
