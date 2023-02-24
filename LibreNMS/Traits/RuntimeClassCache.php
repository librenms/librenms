<?php
/**
 * RuntimeClassCache.php
 *
 * Adds the ability to cache the output of functions either on the instance
 * or in the global cache.  Set $runtimeCacheExternalTTL to enable global cache.
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

namespace LibreNMS\Traits;

use Illuminate\Support\Facades\Cache;
use LibreNMS\Util\Laravel;

trait RuntimeClassCache
{
    /** @var array */
    private $runtimeCache = [];

    /** @var int Setting this installs the data in the external cache to be shared across instances */
    protected $runtimeCacheExternalTTL = 0;

    /**
     * We want these each runtime, so don't use the global cache
     *
     * @return mixed
     */
    protected function cacheGet(string $name, callable $actual)
    {
        if (! array_key_exists($name, $this->runtimeCache)) {
            $this->runtimeCache[$name] = $this->runtimeCacheExternalTTL && Laravel::isBooted()
                ? Cache::remember('runtimeCache' . __CLASS__ . $name, $this->runtimeCacheExternalTTL, $actual)
                : $actual();
        }

        return $this->runtimeCache[$name];
    }
}
