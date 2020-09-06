<?php
/*
 * GrandstreamHt.php
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

namespace LibreNMS\OS;

class GrandstreamHt extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        $oids = [
            'serial' => '.1.3.6.1.4.1.42397.1.2.1.0.0',
            'versionCore' => '.1.3.6.1.4.1.42397.1.2.3.2.0.0',
            'versionBase' => '.1.3.6.1.4.1.42397.1.2.3.3.0.0'
        ];
        $data = snmp_get_multi_oid($this->getDevice(), $oids);

        $device = $this->getDeviceModel();
        $device->serial = $data[$oids['serial']] ?? null;
        if (isset($data[$oids['versionCore']], $data[$oids['versionBase']])) {
            $device->version = 'Core: ' . $data[$oids['versionCore']] . ', Base: ' . $data[$oids['versionBase']];
        }

    }
}
