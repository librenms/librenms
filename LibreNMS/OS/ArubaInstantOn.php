<?php

/*
 * ArubaInstantOn.php
 *
 * Wired Instant On switches share HP/ProCurve-style SNMP tables; reuse procurve discovery YAML.
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
 */

namespace LibreNMS\OS;

class ArubaInstantOn extends Procurve
{
    public function getDiscovery($module = null)
    {
        if (! array_key_exists('dynamic_discovery', $this->device)) {
            $file = resource_path('definitions/os_discovery/procurve.yaml');
            if (file_exists($file)) {
                $this->device['dynamic_discovery'] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($file));
            }
        }

        if ($module) {
            return $this->device['dynamic_discovery']['modules'][$module] ?? [];
        }

        return $this->device['dynamic_discovery'] ?? [];
    }
}
