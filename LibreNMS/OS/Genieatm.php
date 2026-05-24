<?php

/**
 * GenieATM.php
 *
 * Genie Networks ATM
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
 * @copyright  2026 Rob J. Epping
 * @author     Rob J. Epping <librenms@renf.us>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS;

class Genieatm extends OS
{
    protected function getHardware(): ?string
    {
        $hardware = [
            '.1.3.6.1.4.1.9926.1.1.21.1111' => 'GenieATM 1111',
            '.1.3.6.1.4.1.9926.1.1.21.2325' => 'GenieATM 2325',
            '.1.3.6.1.4.1.9926.1.1.21.2625' => 'GenieATM 2625',
            '.1.3.6.1.4.1.9926.1.1.21.5312' => 'GenieATM 5312',
            '.1.3.6.1.4.1.9926.1.1.21.5333' => 'GenieATM 5333',
            '.1.3.6.1.4.1.9926.1.1.21.6101' => 'GenieATM 6101',
            '.1.3.6.1.4.1.9926.1.1.21.6102' => 'GenieATM 6102',
            '.1.3.6.1.4.1.9926.1.1.21.6103' => 'GenieATM 6103',
            '.1.3.6.1.4.1.9926.1.1.21.6105' => 'GenieATM 6105',
            '.1.3.6.1.4.1.9926.1.1.21.6110' => 'GenieATM 6110',
            '.1.3.6.1.4.1.9926.1.1.21.6111' => 'GenieATM 6111',
            '.1.3.6.1.4.1.9926.1.1.21.6113' => 'GenieATM 6113',
            '.1.3.6.1.4.1.9926.1.1.21.6115' => 'GenieATM 6115',
            '.1.3.6.1.4.1.9926.1.1.21.6117' => 'GenieATM 6117',
            '.1.3.6.1.4.1.9926.1.1.21.6118' => 'GenieATM 6118',
            '.1.3.6.1.4.1.9926.1.1.21.6123' => 'GenieATM 6123',
            '.1.3.6.1.4.1.9926.1.1.21.6125' => 'GenieATM 6125',
            '.1.3.6.1.4.1.9926.1.1.21.6130' => 'GenieATM 6130',
            '.1.3.6.1.4.1.9926.1.1.21.6133' => 'GenieATM 6133',
            '.1.3.6.1.4.1.9926.1.1.21.6135' => 'GenieATM 6135',
            '.1.3.6.1.4.1.9926.1.1.21.6160' => 'GenieATM 6160',
            '.1.3.6.1.4.1.9926.1.1.21.6170' => 'GenieATM 6170',
            '.1.3.6.1.4.1.9926.1.1.21.6180' => 'GenieATM 6180',
            '.1.3.6.1.4.1.9926.1.1.21.6190' => 'GenieATM 6190',
            '.1.3.6.1.4.1.9926.1.1.21.6165' => 'GenieATM 6165',
            '.1.3.6.1.4.1.9926.1.1.21.6167' => 'GenieATM 6167',
            '.1.3.6.1.4.1.9926.1.1.21.6169' => 'GenieATM 6169',
            '.1.3.6.1.4.1.9926.1.1.21.6171' => 'GenieATM 6171',
            '.1.3.6.1.4.1.9926.1.1.21.6173' => 'GenieATM 6173',
            '.1.3.6.1.4.1.9926.1.1.21.6212' => 'GenieATM 6212',
            '.1.3.6.1.4.1.9926.1.1.21.6232' => 'GenieATM 6232',
            '.1.3.6.1.4.1.9926.1.1.21.6233' => 'GenieATM 6233',
            '.1.3.6.1.4.1.9926.1.1.21.6265' => 'GenieATM 6265',
            '.1.3.6.1.4.1.9926.1.1.21.6203' => 'GenieATM 6203',
            '.1.3.6.1.4.1.9926.1.1.21.6213' => 'GenieATM 6213',
            '.1.3.6.1.4.1.9926.1.1.21.6260' => 'GenieATM 6260',
            '.1.3.6.1.4.1.9926.1.1.21.6270' => 'GenieATM 6270',
            '.1.3.6.1.4.1.9926.1.1.21.6280' => 'GenieATM 6280',
            '.1.3.6.1.4.1.9926.1.1.21.6305' => 'GenieATM 6305',
            '.1.3.6.1.4.1.9926.1.1.21.6311' => 'GenieATM 6311',
            '.1.3.6.1.4.1.9926.1.1.21.6312' => 'GenieATM 6312',
            '.1.3.6.1.4.1.9926.1.1.21.6313' => 'GenieATM 6313',
            '.1.3.6.1.4.1.9926.1.1.21.6315' => 'GenieATM 6315',
            '.1.3.6.1.4.1.9926.1.1.21.6323' => 'GenieATM 6323',
            '.1.3.6.1.4.1.9926.1.1.21.6325' => 'GenieATM 6325',
            '.1.3.6.1.4.1.9926.1.1.21.6333' => 'GenieATM 6333',
            '.1.3.6.1.4.1.9926.1.1.21.6335' => 'GenieATM 6335',
            '.1.3.6.1.4.1.9926.1.1.21.6365' => 'GenieATM 6365',
            '.1.3.6.1.4.1.9926.1.1.21.6367' => 'GenieATM 6367',
            '.1.3.6.1.4.1.9926.1.1.21.6369' => 'GenieATM 6369',
            '.1.3.6.1.4.1.9926.1.1.21.6371' => 'GenieATM 6371',
            '.1.3.6.1.4.1.9926.1.1.21.6373' => 'GenieATM 6373',
            '.1.3.6.1.4.1.9926.1.1.21.6331' => 'GenieATM 6331',
            '.1.3.6.1.4.1.9926.1.1.21.6363' => 'GenieATM 6363',
            '.1.3.6.1.4.1.9926.1.1.21.6370' => 'GenieATM 6370',
            '.1.3.6.1.4.1.9926.1.1.21.6402' => 'GenieATM 6402',
            '.1.3.6.1.4.1.9926.1.1.21.6410' => 'GenieATM 6410',
            '.1.3.6.1.4.1.9926.1.1.21.6411' => 'GenieATM 6411',
            '.1.3.6.1.4.1.9926.1.1.21.6503' => 'GenieATM 6503',
            '.1.3.6.1.4.1.9926.1.1.21.6505' => 'GenieATM 6505',
            '.1.3.6.1.4.1.9926.1.1.21.6507' => 'GenieATM 6507',
            '.1.3.6.1.4.1.9926.1.1.21.6509' => 'GenieATM 6509',
            '.1.3.6.1.4.1.9926.1.1.21.6511' => 'GenieATM 6511',
            '.1.3.6.1.4.1.9926.1.1.21.6611' => 'GenieATM 6611',
            '.1.3.6.1.4.1.9926.1.1.21.6703' => 'GenieATM 6703',
            '.1.3.6.1.4.1.9926.1.1.21.6705' => 'GenieATM 6705',
            '.1.3.6.1.4.1.9926.1.1.21.6707' => 'GenieATM 6707',
            '.1.3.6.1.4.1.9926.1.1.21.6709' => 'GenieATM 6709',
            '.1.3.6.1.4.1.9926.1.1.21.6711' => 'GenieATM 6711',
        ];

        return $hardware[$this->getDevice()->sysObjectID] ?? null;
    }

    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml baseline

        $device->hardware = $this->getHardware();
    }
}
