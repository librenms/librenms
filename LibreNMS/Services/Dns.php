<?php
/**
 * Dns.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services;

class Dns extends DefaultServiceCheck
{
    public function hasDefaults(): array
    {
        return [
            '-H' => trans('service.check_params.dns.hostname'),
            '--server' => trans('service.check_params.dns.server'),
        ];
    }

    public function getDefault(string $flag): string
    {
        switch ($flag) {
            case '-H':
                return 'localhost';
            case '--server':
                return $this->service->service_ip ?? $this->service->device->overwrite_ip ?: $this->service->device->hostname;
            default:
                return parent::getDefault($flag);
        }
    }
}
