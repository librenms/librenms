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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use SnmpQuery;

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

    /**
     * Values are "timestamp:value" strings, load divisors (pool sizes) change.
     */
    public function pollSensors(EloquentCollection $sensors): array
    {
        $values = parent::pollSensors($sensors);

        foreach ($sensors as $sensor) {
            $value = $values[$sensor->sensor_id] ?? null;
            if ($value === null) {
                continue;
            }

            if ($sensor->sensor_class == 'count' && $sensor->sensor_type === 'dhcpatriotLicenseExpiration') {
                // days until the license expires
                $values[$sensor->sensor_id] = round(((int) (explode(':', $value)[1] ?? 0) - time()) / 86400);
            } elseif ($sensor->sensor_class == 'state' && $sensor->sensor_type === 'dhcpatriotServiceStatus') {
                [$timestamp, $status] = array_pad(explode(':', $value), 2, '');
                // stale (over 5 minutes) is 2, unknown is 3
                $values[$sensor->sensor_id] = $status === '999' ? 3 : (abs((int) $timestamp - time()) > 300 ? 2 : (int) $status);
            }
        }

        $load = $sensors->where('sensor_class', 'load');
        if ($load->isNotEmpty()) {
            $sizes = SnmpQuery::numeric()->get($load->map(fn ($sensor) => $this->poolSizeOid($sensor->sensor_oid))->unique()->values()->all())->values();

            foreach ($load as $sensor) {
                $size = $sizes[$this->poolSizeOid($sensor->sensor_oid)] ?? null;
                if (is_numeric($size) && isset($values[$sensor->sensor_id])) {
                    $sensor->sensor_divisor = (int) $size;
                    $sensor->sensor_descr = explode('(', (string) $sensor->sensor_descr)[0] . "({$values[$sensor->sensor_id]}/$size)";
                }
            }
        }

        return $values;
    }

    private function poolSizeOid(string $oid): string
    {
        return str_replace('.1.3.6.1.4.1.2021.50.120', '.1.3.6.1.4.1.2021.50.130', $oid);
    }
}
