<?php
/*
 * Search.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Ajax;

use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use LibreNMS\Config;
use LibreNMS\Util\Color;

class DeviceSearchController extends SearchController
{
    public function buildQuery(string $search, Request $request): Builder
    {
        $baseQuery = Device::hasAccess($request->user())
            ->leftJoin('locations', 'locations.id', '=', 'devices.location_id')
            ->select(['devices.*', 'locations.location'])
            ->distinct()
            ->orderBy('devices.hostname');

        return $baseQuery
            ->where(function (Builder $query) use ($search, $baseQuery) {
                // search filter
                $like_search = "%$search%";
                $query->orWhere('hostname', 'LIKE', $like_search)
                    ->orWhere('sysName', 'LIKE', $like_search)
                    ->orWhere('display', 'LIKE', $like_search)
                    ->orWhere('location', 'LIKE', $like_search)
                    ->orWhere('purpose', 'LIKE', $like_search)
                    ->orWhere('serial', 'LIKE', $like_search)
                    ->orWhere('notes', 'LIKE', $like_search);

                if (\LibreNMS\Util\IPv4::isValid($search, false)) {
                    $baseQuery->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id')
                        ->leftJoin('ipv4_addresses', 'ipv4_addresses.port_id', 'ports.port_id');

                    $query->orWhere('ipv4_address', '=', $search)
                        ->orWhere('overwrite_ip', '=', $search)
                        ->orWhere('ip', '=', inet_pton($search));
                } elseif (\LibreNMS\Util\IPv6::isValid($search, false)) {
                    $baseQuery->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id')
                        ->leftJoin('ipv6_addresses', 'ipv6_addresses.port_id', 'ports.port_id');

                    $query->orWhere('ipv6_address', '=', $search)
                        ->orWhere('overwrite_ip', '=', $search)
                        ->orWhere('ip', '=', inet_pton($search));
                } elseif (ctype_xdigit($mac_search = str_replace([':', '-', '.'], '', $search))) {
                    $baseQuery->leftJoin('ports', 'ports.device_id', '=', 'devices.device_id');

                    $query->orWhere('ifPhysAddress', 'LIKE', "%$mac_search%");
                }

                return $query;
            });
    }

    /**
     * @param  Device  $device
     * @return array
     */
    public function formatItem($device): array
    {
        $name = $device->displayName();
        if (! request()->get('map') && $name !== $device->sysName) {
            $name .= " ($device->sysName)";
        }

        return [
            'name' => $name,
            'device_id' => $device->device_id,
            'url' => \LibreNMS\Util\Url::deviceUrl($device),
            'colours' => Color::forDeviceStatus($device),
            'device_ports' => $device->ports()->count(),
            'device_image' => $device->icon,
            'device_hardware' => $device->hardware,
            'device_os' => Config::getOsSetting($device->os, 'text'),
            'version' => $device->version,
            'location' => $device->location,
        ];
    }
}
