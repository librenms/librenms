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
            $this->device['dynamic_discovery'] = $this->buildMergedDiscovery();
        }

        if ($module) {
            return $this->device['dynamic_discovery']['modules'][$module] ?? [];
        }

        return $this->device['dynamic_discovery'] ?? [];
    }

    /**
     * ProCurve YAML matches port QoS/STP/VLAN behaviour; Instant On lacks ProCurve memory/CPU/PoE SNMP trees.
     *
     * @return array<string, mixed>
     */
    private function buildMergedDiscovery(): array
    {
        $base = \Symfony\Component\Yaml\Yaml::parseFile(resource_path('definitions/os_discovery/procurve.yaml'));
        $instantOn = \Symfony\Component\Yaml\Yaml::parseFile(resource_path('definitions/os_discovery/aruba-instant-on.yaml'));

        $base['modules']['mempools'] = ['data' => []];
        $base['modules']['processors'] = $instantOn['modules']['processors'];
        if (isset($instantOn['modules']['sensors']['power'])) {
            $base['modules']['sensors']['power'] = $instantOn['modules']['sensors']['power'];
        }

        return $base;
    }
}
