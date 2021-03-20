<?php
/*
 * Printer.php
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

namespace LibreNMS\OS\Shared;

use App\Models\Device;

class Printer extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->serial = $device->serial ?? $this->getSerial() ?: null;
    }

    protected function getSerial()
    {
        return snmp_get($this->getDeviceArray(), 'prtGeneralSerialNumber.1', '-Oqv', 'Printer-MIB');
    }

    protected function parseDeviceId($data)
    {
        $vars = [];
        foreach (explode(';', $data) as $pair) {
            [$key, $value] = explode(':', $pair);
            $vars[trim($key)] = $value;
        }

        return $vars;
    }
}
