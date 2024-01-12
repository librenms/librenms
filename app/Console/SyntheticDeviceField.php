<?php
/*
 * SyntheticDeviceField.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Console;

use App\Models\Device;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SyntheticDeviceField
{
    public function __construct(
    public readonly string $name,
    public readonly array $columns = [],
    public readonly ?Closure $displayFunction = null,
    public readonly ?Closure $modifyQuery = null,
    public readonly ?string $headerName = null,
) {
    }

    public function headerName(): string
    {
        return $this->headerName ?? $this->name;
    }

    public function modifyQuery(Builder $query): Builder
    {
        if ($this->modifyQuery) {
            return call_user_func($this->modifyQuery, $query);
        }

        return $query;
    }

    public function toString(Device $device): string
    {
        if ($this->displayFunction) {
            return (string) call_user_func($this->displayFunction, $device);
        }

        return (string) $device->getAttributeValue($this->name);
    }
}
