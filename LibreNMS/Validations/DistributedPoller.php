<?php
/**
 * DistributedPoller.php
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

/**
 * Created by IntelliJ IDEA.
 * User: murrant
 * Date: 10/8/17
 * Time: 2:16 AM
 */

namespace LibreNMS\Validations;

use LibreNMS\Config;
use LibreNMS\Validator;

class DistributedPoller extends BaseValidation
{
    protected static $RUN_BY_DEFAULT = false;

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        if (!Config::get('distributed_poller')) {
            $validator->fail('You have not enabled distributed_poller');
            return;
        }


        if (!Config::get('distributed_poller_memcached_host')) {
            $validator->fail('You have not configured $config[\'distributed_poller_memcached_host\']');
        } elseif (!Config::get('distributed_poller_memcached_port')) {
            $validator->fail('You have not configured $config[\'distributed_poller_memcached_port\']');
        } else {
            $connection = @fsockopen(Config::get('distributed_poller_memcached_host'), Config::get('distributed_poller_memcached_port'));
            if (!is_resource($connection)) {
                $validator->fail('We could not get memcached stats, it is possible that we cannot connect to your memcached server, please check');
            } else {
                fclose($connection);
                $validator->ok('Connection to memcached is ok');
            }
        }

        if (!Config::get('rrdcached')) {
            $validator->fail('You have not configured $config[\'rrdcached\']');
        } elseif (!is_dir(Config::get('rrd_dir'))) {
            $validator->fail('You have not configured $config[\'rrd_dir\']');
        } else {
            Rrd::checkRrdcached($validator);
        }
    }
}
