<?php
/**
 * Junose.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;

class Junose extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        if (strpos($device->sysDescr, 'olive')) {
            $device->hardware = 'Olive';

            return;
        }

        $junose_hardware = \SnmpQuery::translate($device->sysObjectID, 'Juniper-Products-MIB')->value();
        $device->hardware = $this->rewriteHardware($junose_hardware) ?: null;

        $junose_version = \SnmpQuery::get('Juniper-System-MIB::juniSystemSwVersion.0')->value();
        preg_match('/\((.*)\)/', $junose_version, $matches);
        $device->version = $matches[1] ?? null;
        preg_match('/\[(.*)]/', $junose_version, $matches);
        $device->features = $matches[1] ?? null;
    }

    private function rewriteHardware(string $hardware): string
    {
        $rewrite_junose_hardware = [
            'Juniper-Products-MIB::' => 'Juniper ',
            'juniErx1400' => 'ERX-1400',
            'juniErx700' => 'ERX-700',
            'juniErx1440' => 'ERX-1440',
            'juniErx705' => 'ERX-705',
            'juniErx310' => 'ERX-310',
            'juniE320' => 'E320',
            'juniE120' => 'E120',
            'juniSsx1400' => 'SSX-1400',
            'juniSsx700' => 'SSX-700',
            'juniSsx1440' => 'SSX-1440',
        ];

        $hardware = str_replace(array_keys($rewrite_junose_hardware), array_values($rewrite_junose_hardware), $hardware);

        return $hardware;
    }
}
