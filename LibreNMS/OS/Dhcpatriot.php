<?php
/*
 * Dhcpatriot.php
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
use DateTime;
use Illuminate\Support\Str;

class Dhcpatriot extends Shared\Unix
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $license = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.2021.51.12.4.1.2.7.76.73.67.69.78.83.69.1', '-Oqv');

        if (! empty($license)) {
            if ($license === 'FULL:0') {
                $device->features = 'Non-Expiry License';
            } elseif (Str::contains($license, 'LIMITED:')) {
                $ft_epoch = str_replace('LIMITED:', '', $license);
                $ft_dt = new DateTime("@$ft_epoch");
                $device->features = 'License Expires ' . $ft_dt->format('Y-m-d');
            }
        }
    }
}
