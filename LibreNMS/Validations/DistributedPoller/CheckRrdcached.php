<?php
/**
 * CheckRrdcached.php
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

namespace LibreNMS\Validations\DistributedPoller;

use LibreNMS\Config;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Rrd\CheckRrdcachedConnectivity;

class CheckRrdcached implements \LibreNMS\Interfaces\Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if (! Config::get('rrdcached')) {
            return ValidationResult::fail(trans('validation.validations.distributedpoller.CheckRrdcached.fail'), 'lnms config:set rrdcached <rrdcached server ip:port>');
        }

        return (new CheckRrdcachedConnectivity)->validate();
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return (bool) Config::get('distributed_poller');
    }
}
