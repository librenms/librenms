<?php

/*
 * Jetdirect.php
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
use LibreNMS\Interfaces\PrinterSuppliesContext;
use SnmpQuery;

class Jetdirect extends Shared\Printer implements PrinterSuppliesContext
{
    public function getPrinterSuppliesContexts(): array
    {
        return [null, 'Jetdirect'];
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $device = $this->getDevice();

        $jetdirect_id = SnmpQuery::get('HP-LASERJET-COMMON-MIB::gdStatusId.0')->value()
            ?: SnmpQuery::context('Jetdirect')->get('HP-LASERJET-COMMON-MIB::gdStatusId.0')->value();
        $info = $this->parseDeviceId($jetdirect_id);

        $hardware = $info['MDL'] ?? $info['MODEL'] ?? $info['DES'] ?? $info['DESCRIPTION'] ?? null;
        if (! empty($hardware)) {
            $hardware = str_ireplace([
                'HP ',
                'Hewlett-Packard ',
                ' Series',
            ], '', $hardware);
            $device->hardware = ucfirst($hardware);
        }
    }
}
