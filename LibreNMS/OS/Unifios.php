<?php
/**
 * Unifios.php
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
 * @copyright  2024 Roman Yudin
 * @author     Roman Yudin <romans.judins@ui.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;

class Unifios extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        // Ubiquiti UnifiOS UDM 4.1.13 Linux 4.19.152 al324
        [$ubi, $os, $prod, $ver, $linux, $linver, $proc] = explode(' ', $device->sysDescr);
        $device->version = $ver;
        $device->hardware = $prod;
    }
}
