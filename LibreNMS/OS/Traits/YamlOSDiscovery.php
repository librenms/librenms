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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use Illuminate\Support\Arr;
use Log;

trait YamlOSDiscovery
{
    private $fields = [
        'version',
        'hardware',
        'features',
        'serial',
    ];

    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        $yaml = $this->getDiscovery();
        $os_yaml = $yaml['modules']['os'] ?? [];

        if (isset($os_yaml['sysDescr_regex'])) {
            $this->parseRegex($os_yaml['sysDescr_regex'], $device->sysDescr);
        }

        $oids = Arr::only($os_yaml, $this->fields);
        $mib = $os_yaml['mib'] ?? $yaml['mib'] ?? null;
        $data = snmp_get_multi_oid($this->getDevice(), $oids, '-OUQ', $mib);

        Log::debug("Yaml OS data:", $data);

        foreach($oids as $field => $oid) {
            $device->$field = $data[$oid] ?? $device->$field;
        }
    }

    private function parseRegex($regex, $subject)
    {
        if (preg_match($regex, $subject, $matches)) {
            $device = $this->getDeviceModel();
            foreach ($this->fields as $field) {
                $device->$field = $matches[$field] ?? null;
            }
        }
    }
}
