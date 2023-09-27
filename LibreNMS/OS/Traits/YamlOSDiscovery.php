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
use App\View\SimpleTemplate;
use Illuminate\Support\Arr;
use LibreNMS\Util\Oid;
use LibreNMS\Util\StringHelpers;
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
        $numeric = Oid::hasNumeric($fetch_oids);
        $data = $this->fetch($fetch_oids, $numeric);

        Log::debug('Yaml OS data:', $data);

        $template_data = array_merge($this->getDevice()->only($this->osFields), $data);
        foreach ($oids as $field => $oid_list) {
            if ($value = $this->findFirst($data, $oid_list, $numeric)) {
                // extract via regex if requested
                if (isset($os_yaml["{$field}_regex"])) {
                    $this->parseRegex($os_yaml["{$field}_regex"], $value);
                    $value = $device->$field;
                }

                $device->$field = isset($os_yaml["{$field}_template"])
                    ? trim(SimpleTemplate::parse($os_yaml["{$field}_template"], $template_data))
                    : $value;
            }
        }

        $this->replaceStringsInFields($device, $os_yaml);
    }

    public function fetchLocation(): Location
    {
        $os_yaml = $this->getDiscovery('os');
        $name = $os_yaml['location'] ?? null;
        $lat = $os_yaml['lat'] ?? null;
        $lng = $os_yaml['long'] ?? null;

        $oids = array_filter([$name, $lat, $lng]);
        $numeric = Oid::hasNumeric($oids);
        $data = $this->fetch($oids, $numeric);

        Log::debug('Yaml location data:', $data);

        $location = $this->findFirst($data, $name, $numeric) ?? snmp_get($this->getDeviceArray(), 'SNMPv2-MIB::sysLocation.0', '-Oqv');

        return new Location([
            'location' => StringHelpers::inferEncoding($location),
            'lat' => $this->findFirst($data, $lat, $numeric),
            'lng' => $this->findFirst($data, $lng, $numeric),
        ]);
    }

    private function findFirst($data, $oids, $numeric = false)
    {
        foreach (Arr::wrap($oids) as $oid) {
            // translate all to numeric to make it easier to match
            $oid = ($numeric && ! Oid::isNumeric($oid)) ? snmp_translate($oid, 'ALL', null, null, $this->getDeviceArray()) : $oid;
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

    private function replaceStringsInFields(Device $device, array $os_yaml): void
    {
        foreach ($this->osFields as $field) {
            foreach ($os_yaml["{$field}_replace"] ?? [] as $replacements) {
                $search = $replacements;
                $replacement = '';

                // check for a given replacement string (otherwise, remove)
                if (is_array($replacements) && count($replacements) == 2) {
                    $search = $replacements[0];
                    $replacement = $replacements[1];
                }

                if (isset($device->$field)) {
                    $device->$field = preg_replace($search, $replacement, $device->$field);
                }
            }
        }
    }
}
