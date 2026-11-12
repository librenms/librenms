<?php

/**
 * Package.php
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
use App\Observers\PackageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LibreNMS\Interfaces\Models\Keyable;

#[ObservedBy([PackageObserver::class])]
class Package extends DeviceRelatedModel implements Keyable
{
    use Filterable;
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'pkg_id';
    protected $fillable = [
        'name',
        'manager',
        'status',
        'version',
        'build',
        'arch',
        'size',
    ];

    /** @var list<string> */
    protected array $filterable = [
        'name',
        'version',
        'arch',
        'manager',
        'size',
        'device_id',
    ];

    /**
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: string[], search?: bool}>
     */
    public static function filterFieldDefinitions(User $user, ?int $deviceId = null): array
    {
        $fields = [
            [
                'key' => 'name',
                'label' => __('Package'),
                'type' => 'text',
                'search' => true,
            ],
            [
                'key' => 'version',
                'label' => __('Version'),
                'type' => 'version',
            ],
            [
                'key' => 'arch',
                'label' => __('Arch'),
                'type' => 'select',
                'options' => self::availableValues('arch', $user, $deviceId),
            ],
            [
                'key' => 'manager',
                'label' => __('Manager'),
                'type' => 'select',
                'options' => self::availableValues('manager', $user, $deviceId),
            ],
        ];

        $fields[] = $deviceId === null
            ? [
                'key' => 'device_id',
                'label' => __('Device'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device'),
            ]
            : [
                'key' => 'size',
                'label' => __('Size (bytes)'),
                'type' => 'number',
            ];

        return $fields;
    }

    /**
     * @return string[]
     */
    private static function availableValues(string $column, User $user, ?int $deviceId): array
    {
        return self::hasAccess($user)
            ->when($deviceId, fn (Builder $q) => $q->where('device_id', $deviceId))
            ->distinct()->orderBy($column)->pluck($column)->all();
    }

    /**
     * Link to a single package on the packages page
     */
    public static function urlForName(string $name): string
    {
        return route('packages.index', ['filter' => ['name' => ['eq' => $name]]]);
    }

    /**
     * version_compare can't run in SQL, resolve the matching versions so pagination only sees matches
     *
     * @param  Builder<static>  $query
     * @param  scalar|scalar[]  $value
     * @param  array{operator?: string, wildcard?: string, not?: bool, null?: bool, set?: bool}  $config
     */
    protected function filterVersion(Builder $query, mixed $value, array $config): void
    {
        $operator = $config['operator'] ?? null;

        if (! in_array($operator, ['>', '>=', '<', '<='], true) || ! is_scalar($value)) {
            $this->applyQueryLogic($query, $this->qualifyColumn('version'), $value, $config);

            return;
        }

        [$compareEpoch, $compareVersion] = $this->splitEpoch((string) $value);

        $query->whereIn($this->qualifyColumn('version'), $query->clone()->distinct()->pluck('version')
            ->filter(function (string $version) use ($compareEpoch, $compareVersion, $operator) {
                [$epoch, $version] = $this->splitEpoch($version);

                return $epoch === $compareEpoch
                    ? version_compare($version, $compareVersion, $operator)
                    : version_compare((string) $epoch, (string) $compareEpoch, $operator);
            }));
    }

    /**
     * Split the epoch from a version, 1:2.3 becomes [1, '2.3'] and a missing epoch is 0
     *
     * @return array{int, string}
     */
    private function splitEpoch(string $version): array
    {
        return preg_match('/^(\d+):(.+)$/', $version, $matches) ? [(int) $matches[1], $matches[2]] : [0, $version];
    }

    public function getCompositeKey(): string
    {
        return "$this->manager-$this->name-$this->arch";
    }

    public function __toString(): string
    {
        return $this->name . ' (' . $this->arch . ') version ' . $this->displayVersion();
    }

    public function displayVersion(): string
    {
        return $this->version . ($this->build ? "-$this->build" : '');
    }

    public function isValid(): bool
    {
        return $this->name && $this->manager && $this->arch && $this->version;
    }
}
