<?php

/**
 * Liebert.php
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
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

namespace LibreNMS\OS;

use App\Models\Device;
use Illuminate\Support\Str;
use LibreNMS\OS;
use SnmpQuery;

class Liebert extends OS
{
    // The same agent card serves UPSes, power distribution and cooling units, and
    // the registration branch of the device it manages says which one this is:
    // lgpAcProducts and lgpAcPACCProducts are the environmental families.
    private const ENVIRONMENTAL_PRODUCTS = [
        '.1.3.6.1.4.1.476.1.42.4.3.',
        '.1.3.6.1.4.1.476.1.42.4.12.',
    ];

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device);

        if ($device->getAttrib('override_device_type')) {
            return;
        }

        $product = SnmpQuery::numeric()->get('LIEBERT-GP-AGENT-MIB::lgpAgentDeviceId.1')->value();
        if (Str::startsWith($product, self::ENVIRONMENTAL_PRODUCTS)) {
            $device->type = 'environment';
        }
    }
}
