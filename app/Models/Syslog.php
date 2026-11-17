<?php

/**
 * Syslog.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use App\Models\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Enum\SyslogSeverity;

class Syslog extends DeviceRelatedModel
{
    use Filterable;
    use HasFactory;

    protected $table = 'syslog';
    protected $primaryKey = 'seq';
    public $timestamps = false;

    /** @var string[] */
    protected array $filterable = [
        'device_id',
        'program',
        'priority',
        'search',
    ];

    /**
     * @param  bool  $withDevice  offer the device filter, leave it out on device pages
     * @return array<array{key: string, label: string, type: string, search?: bool, endpoint?: string, options?: string[]}>
     */
    public static function filterFieldDefinitions(bool $withDevice = true): array
    {
        $fields = [
            ['key' => 'search', 'label' => __('Message'), 'type' => 'text', 'search' => true],
            ['key' => 'program', 'label' => __('Program'), 'type' => 'text'],
            ['key' => 'priority', 'label' => __('Priority'), 'type' => 'select', 'options' => SyslogSeverity::LEVELS],
        ];

        if ($withDevice) {
            $fields[] = ['key' => 'device_id', 'label' => __('Device'), 'type' => 'select', 'endpoint' => route('ajax.select.device')];
        }

        return $fields;
    }

    /**
     * @param  Builder<static>  $query
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     */
    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['msg'], $query, $value, $config);
    }
}
