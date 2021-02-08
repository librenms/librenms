<?php
/**
 * Exa.php
 *
 * Calix EXA OS
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
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Exa extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        $info = snmp_getnext_multi($this->getDeviceArray(), ['e7CardSoftwareVersion', 'e7CardSerialNumber'], '-OQUs', 'E7-Calix-MIB');
        $device->version = $info['e7CardSoftwareVersion'];
        $device->serial = $info['e7CardSerialNumber'];
        $device->hardware = 'Calix ' . $device->sysDescr;

        $cards = explode("\n", snmp_walk($this->getDeviceArray(), 'e7CardProvType', '-OQv', 'E7-Calix-MIB'));
        $card_count = [];
        foreach ($cards as $card) {
            $card_count[$card] = ($card_count[$card] ?? 0) + 1;
        }
        $device->features = implode(', ', array_map(function ($card) use ($card_count) {
            return ($card_count[$card] > 1 ? $card_count[$card] . 'x ' : '') . $card;
        }, array_keys($card_count)));
    }
}
