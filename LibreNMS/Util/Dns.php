<?php
/**
 * Dns.php
 *
 * Get version info about LibreNMS and various components/dependencies
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
 * @link       http://librenms.org
 *
 * @copyright  2021 Thomas Berberich
 * @author     Thomas Berberch <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Util;

use App\Models\Device;
use LibreNMS\Interfaces\Geocoder;

class Dns implements Geocoder
{
    protected $resolver;

    public function __construct()
    {
        $this->resolver = new \Net_DNS2_Resolver();
    }

    public static function lookupIp(Device $device): ?string
    {
        if (IP::isValid($device->hostname)) {
            return $device->hostname;
        }

        try {
            if ($device->transport == 'udp6' || $device->transport == 'tcp6') {
                return dns_get_record($device['hostname'], DNS_AAAA)[0]['ipv6'] ?? null;
            }

            return dns_get_record($device['hostname'], DNS_A)[0]['ip'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param  string  $domain  Domain which has to be parsed
     * @param  string  $record  DNS Record which should be searched
     * @return array List of matching records
     */
    public function getRecord($domain, $record = 'A')
    {
        try {
            $ret = $this->resolver->query($domain, $record);

            return $ret->answer;
        } catch (\Net_DNS2_Exception $e) {
            d_echo('::query() failed: ' . $e->getMessage());

            return [];
        }
    }

    public function getCoordinates($hostname)
    {
        $r = $this->getRecord($hostname, 'LOC');

        foreach ($r as $record) {
            return [
                'lat' => $record->latitude ?? null,
                'lng' => $record->longitude ?? null,
            ];
        }

        return [];
    }
}
