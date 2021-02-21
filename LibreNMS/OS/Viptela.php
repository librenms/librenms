<?php
/*
 * Viptela.php
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

namespace LibreNMS\OS;

use App\Models\Device;

class Viptela extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device->hardware = $this->getHardware($device->hardware);
    }

    private function getHardware($id)
    {
        $hardware = [
            '1' => 'Viptela vSmart Controller',
            '2' => 'Viptela vManage NMS',
            '3' => 'Viptela vBond Orchestrator',
            '4' => 'Viptela vEdge-1000',
            '5' => 'Viptela vEdge-2000',
            '6' => 'Viptela vEdge-100',
            '7' => 'Viptela vEdge-100-W2',
            '8' => 'Viptela vEdge-100-WM',
            '9' => 'Viptela vEdge-100-M2',
            '10' => 'Viptela vEdge-100-M',
            '11' => 'Viptela vEdge-100-B',
            '12' => 'Viptela vEdge Cloud',
            '13' => 'Viptela vContainer',
            '14' => 'Viptela vEdge-5000',
        ];

        return $hardware[(string) $id] ?? $id;
    }
}
