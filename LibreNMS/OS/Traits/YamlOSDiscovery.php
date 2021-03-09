<?php
/*
 * YamlOSDiscovery.php
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

namespace LibreNMS\OS\Traits;

use App\Models\Device;
use App\Models\Location;
use Illuminate\Support\Arr;
use Log;

trait YamlOSDiscovery
{
    private $osDbFields = [
        'version',
        'hardware',
        'features',
        'serial',
    ];

    private $osFields = [
        'version',
        'hardware',
        'features',
        'serial',
    ];

    public function discoverOS(Device $device): void
    {
        $os_yaml = $this->getDiscovery('os');

        if (isset($os_yaml['sysDescr_regex'])) {
            $this->parseRegex($os_yaml['sysDescr_regex'], $device->sysDescr);
        }

        if (isset($os_yaml['hardware_mib'])) {
            $this->translateSysObjectID($os_yaml['hardware_mib'], $os_yaml['hardware_regex'] ?? null);
        }

        $oids = Arr::only($os_yaml, $this->osFields);
        $fetch_oids = array_unique(Arr::flatten($oids));
        $numeric = $this->isNumeric($fetch_oids);
        $data = $this->fetch($fetch_oids, $numeric);

        Log::debug('Yaml OS data:', $data);

        foreach ($oids as $field => $oid_list) {
            if ($value = $this->findFirst($data, $oid_list, $numeric)) {
                // extract via regex if requested
                if (isset($os_yaml["{$field}_regex"])) {
                    $this->parseRegex($os_yaml["{$field}_regex"], $value);
                    $value = $device->$field;
                }

                $device->$field = isset($os_yaml["{$field}_template"])
                    ? $this->parseTemplate($os_yaml["{$field}_template"], $data)
                    : $value;
            }
        }
    }

    public function fetchLocation(): Location
    {
        $os_yaml = $this->getDiscovery('os');
        $name = $os_yaml['location'] ?? null;
        $lat = $os_yaml['lat'] ?? null;
        $lng = $os_yaml['long'] ?? null;

        $oids = array_filter([$name, $lat, $lng]);
        $numeric = $this->isNumeric($oids);
        $data = $this->fetch($oids, $numeric);

        Log::debug('Yaml location data:', $data);

        return new Location([
            'location' => $this->findFirst($data, $name, $numeric) ?? snmp_get($this->getDeviceArray(), 'SNMPv2-MIB::sysLocation.0', '-Oqv'),
            'lat' => $this->findFirst($data, $lat, $numeric),
            'lng' => $this->findFirst($data, $lng, $numeric),
        ]);
    }

    private function findFirst($data, $oids, $numeric = false)
    {
        foreach (Arr::wrap($oids) as $oid) {
            // translate all to numeric to make it easier to match
            $oid = ($numeric && ! oid_is_numeric($oid)) ? snmp_translate($oid, 'ALL', null, null, $this->getDeviceArray()) : $oid;
            if (! empty($data[$oid])) {
                return $data[$oid];
            }
        }

        return null;
    }

    private function parseRegex($regexes, $subject)
    {
        $device = $this->getDevice();

        foreach (Arr::wrap($regexes) as $regex) {
            if (preg_match($regex, $subject, $matches)) {
                foreach ($this->osDbFields as $field) {
                    if (isset($matches[$field])) {
                        $device->$field = $matches[$field];
                    }
                }
            }
        }
    }

    private function parseTemplate($template, $data)
    {
        return trim(preg_replace_callback('/{{ ([^ ]+) }}/', function ($matches) use ($data) {
            return $data[$matches[1]] ?? '';
        }, $template));
    }

    private function translateSysObjectID($mib, $regex)
    {
        $device = $this->getDevice();
        $device->hardware = snmp_translate($device->sysObjectID, $mib, null, '-Os', $this->getDeviceArray());

        if ($regex) {
            $this->parseRegex($regex, $device->hardware);
        }
    }

    private function fetch(array $oids, $numeric)
    {
        return snmp_get_multi_oid($this->getDeviceArray(), $oids, $numeric ? '-OUQn' : '-OUQ');
    }

    private function isNumeric($oids)
    {
        foreach ($oids as $oid) {
            if (oid_is_numeric($oid)) {
                return true;
            }
        }

        return false;
    }
}
