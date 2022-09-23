<?php
/*
 * CheckRrdcachedConnectivity.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Rrd;

use LibreNMS\Config;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckRrdcachedConnectivity implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        [$host,$port] = explode(':', Config::get('rrdcached'));
        if ($host == 'unix') {
            // Using socket, check that file exists
            if (! file_exists($port)) {
                return ValidationResult::fail(trans('validation.validations.rrd.CheckRrdcachedConnectivity.fail_socket', ['socket' => $port]));
            }
        } else {
            $connection = @fsockopen($host, (int) $port);
            if (is_resource($connection)) {
                fclose($connection);
            } else {
                return ValidationResult::fail(trans('validation.validations.rrd.CheckRrdcachedConnectivity.fail_port', ['port' => $port]));
            }
        }

        return ValidationResult::ok(trans('validation.validations.rrd.CheckRrdcachedConnectivity.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return (bool) Config::get('rrdcached');
    }
}
