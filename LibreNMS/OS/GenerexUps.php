<?php
/*
 * GenerexUps.php
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

class GenerexUps extends \LibreNMS\OS
{
    public function discoverOS(): void
    {
        parent::discoverOS(); // yaml

        $device = $this->getDeviceModel();
        $data = snmp_get_multi($this->getDevice(), ['upsIdentManufacturer.0', 'upsIdentModel.0', 'upsIdentAgentSoftwareVersion.0'], '-OQUs', 'UPS-MIB');

        if (isset($data[0]['upsIdentManufacturer'], $data[0]['upsIdentModel'])) {
            $device->hardware = $data[0]['upsIdentManufacturer'] . ' - ' . $data[0]['upsIdentModel'];
        }

        if (isset($data[0]['upsIdentAgentSoftwareVersion'])) {
            $device->version = $data[0]['upsIdentAgentSoftwareVersion'];
        }
    }
}
