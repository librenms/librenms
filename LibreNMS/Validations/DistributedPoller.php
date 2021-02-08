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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
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

use App\Models\PollerCluster;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Validator;

class DistributedPoller extends BaseValidation
{
    public function isDefault()
    {
        // run by default if distributed polling is enabled
        return Config::get('distributed_poller');
    }

    /**
     * Validate this module.
     * To return ValidationResults, call ok, warn, fail, or result methods on the $validator
     *
     * @param Validator $validator
     */
    public function validate(Validator $validator)
    {
        if (! Config::get('distributed_poller')) {
            $validator->fail('You have not enabled distributed_poller', 'lnms config:set distributed_poller true');

            return;
        }

        if (! Config::get('rrdcached')) {
            $validator->fail('You have not configured $config[\'rrdcached\']');
        } elseif (! is_dir(Config::get('rrd_dir'))) {
            $validator->fail('You have not configured $config[\'rrd_dir\']');
        } else {
            Rrd::checkRrdcached($validator);
        }

        if (PollerCluster::exists()) {
            if (PollerCluster::isActive()->exists()) {
                $validator->info('Detected Dispatcher Service');
                $this->checkDispatcherService($validator);

                return;
            }

            $validator->warn('Dispatcher Service has been used in your cluster, but not recently. It may take up to 5 minutes to register.');
        }

        $validator->info('Detected Python Wrapper');
        $this->checkPythonWrapper($validator);
    }

    private function checkDispatcherService(Validator $validator)
    {
        $driver = config('cache.default');
        if ($driver != 'redis') {
            $validator->warn("Using $driver for distributed locking, you should set CACHE_DRIVER=redis");
        }

        try {
            $lock = \Cache::lock('dist_test_validation');
            $lock->get();
            $lock->release();
        } catch (\Exception $e) {
            $validator->fail('Locking server issue: ' . $e->getMessage());
        }

        $node = PollerCluster::firstWhere('node_id', config('librenms.node_id'));
        if (! $node->exists) {
            $validator->fail('Dispatcher service is enabled on your cluster, but not in use on this node');

            return;
        }

        if ($node->last_report->lessThan(Carbon::now()->subSeconds($node->getSettingValue('poller_frequency')))) {
            $validator->fail('Dispatcher service has not reported stats within the last poller window');
        }
    }

    private function checkPythonWrapper(Validator $validator)
    {
        if (! Config::get('distributed_poller_memcached_host')) {
            $validator->fail('You have not configured $config[\'distributed_poller_memcached_host\']');
        } elseif (! Config::get('distributed_poller_memcached_port')) {
            $validator->fail('You have not configured $config[\'distributed_poller_memcached_port\']');
        } else {
            $connection = @fsockopen(Config::get('distributed_poller_memcached_host'), Config::get('distributed_poller_memcached_port'));
            if (! is_resource($connection)) {
                $validator->fail('We could not get memcached stats, it is possible that we cannot connect to your memcached server, please check');
            } else {
                fclose($connection);
                $validator->ok('Connection to memcached is ok');
            }
        }
    }
}
