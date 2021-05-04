<?php
/*
 * Zxdsl.php
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

class Zxdsl extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        if (preg_match('/^\.1\.3\.6\.1\.4\.1\.3902\.(1004|1015)\.(?<model>[^.]+)\.(?<variant>.*\.)1\.1\.1/', $device->sysObjectID, $matches)) {
            $device->hardware = 'ZXDSL ' . $matches['model'] . $this->parseVariant($matches['variant']);
        }
    }

    protected function parseVariant($oid)
    {
        $variant = ' ';
        $parts = explode('.', trim($oid, '.'));
        foreach ($parts as $part) {
            $variant .= chr(64 + (int) $part);
        }

        return trim($variant);
    }
}
