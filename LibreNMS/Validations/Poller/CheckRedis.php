<?php
/**
 * CheckRedis.php
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

namespace LibreNMS\Validations\Poller;

use Illuminate\Support\Facades\Redis;
use LibreNMS\ValidationResult;

class CheckRedis implements \LibreNMS\Interfaces\Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        if ($this->redisIsAvailable()) {
            $driver = config('cache.default');
            if ($driver != 'redis') {
                return ValidationResult::warn(trans('validation.validations.poller.CheckRedis.bad_driver', ['driver' => $driver]));
            }

            return ValidationResult::ok(trans('validation.validations.poller.CheckRedis.ok'));
        }

        if (\LibreNMS\Config::get('distributed_poller') && \App\Models\PollerCluster::isActive()->count() > 2) {
            return ValidationResult::fail(trans('validation.validations.poller.CheckRedis.unavailable'));
        }

        return ValidationResult::ok(trans('validation.validations.poller.CheckRedis.unavailable'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return true;
    }

    private function redisIsAvailable(): bool
    {
        try {
            Redis::command('ping');

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
