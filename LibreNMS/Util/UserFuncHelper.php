<?php
/**
 * UserFuncHelper.php
 *
 * Helper class for "user_func"
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
 */

namespace LibreNMS\Util;

use Illuminate\Support\Str;
use LibreNMS\Exceptions\UserFunctionExistException;

class UserFuncHelper
{
    public function __construct(
        public string|int|float $value,
        public string|int|float|null $value_raw = null,
        public array $sensor = [],
    ) {
    }

    public function __call(string $name, array $arguments): mixed
    {
        throw new UserFunctionExistException("Invalid user function: $name");
    }

    public function dateToRuntime(): int
    {
        return \LibreNMS\Util\Time::dateToMinutes($this->value_raw);
    }

    public function fsParseChannelValue(): float
    {
        $channel = Str::afterLast($this->sensor['sensor_index'], '.');

        return Number::cast(explode(',', $this->value_raw)[$channel] ?? '') * $this->sensor['sensor_multiplier'] / $this->sensor['sensor_divisor'];
    }
}
