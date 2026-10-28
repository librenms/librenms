<?php

namespace App\Models\Traits;

use App\Models\DeviceGroup;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Filter bar fields shared by the faults (AlertFault) and alert history (AlertLog) pages
 */
trait AlertFilters
{
    use Filterable;

    /** alert_rules.severity enum values in order, so the position + 1 is the legacy numeric severity */
    public const SEVERITIES = ['ok', 'warning', 'critical'];

    /**
     * States offered by the state filter, keyed by name. String keys keep the options a JSON object,
     * the filter bar treats a list (sequential int keys starting at 0) as value = label.
     *
     * @return array<string, array{int, string}> name => [state value, label]
     */
    abstract protected static function filterStates(): array;

    /**
     * @param  int|null  $deviceId  page is limited to this device, leave out the device, location and group filters
     * @return array<array{key: string, label: string, type: string, endpoint?: string, options?: array<string, string>, search?: bool}>
     */
    protected static function alertFilterFields(?int $deviceId = null): array
    {
        $user = auth()->user();

        $fields = [
            [
                'key' => 'search',
                'label' => __('Search'),
                'type' => 'text',
                'search' => true,
            ],
            [
                'key' => 'state',
                'label' => __('State'),
                'type' => 'select',
                'options' => array_map(fn ($state) => $state[1], static::filterStates()),
            ],
            [
                'key' => 'rule.severity',
                'label' => __('Severity'),
                'type' => 'select',
                'options' => array_combine(self::SEVERITIES, array_map(fn ($s) => __(ucfirst($s)), self::SEVERITIES)),
            ],
            [
                'key' => 'rule_id',
                'label' => __('Rule'),
                'type' => 'select',
                'endpoint' => route('ajax.select.alert-rule'),
            ],
        ];

        if ($deviceId !== null) {
            return $fields;
        }

        $fields[] = [
            'key' => 'device_id',
            'label' => __('Device'),
            'type' => 'select',
            'endpoint' => route('ajax.select.device'),
        ];

        if ($user?->can('viewAny', Location::class)) {
            $fields[] = [
                'key' => 'device.location_id',
                'label' => __('Location'),
                'type' => 'select',
                'endpoint' => route('ajax.select.location'),
            ];
        }

        if ($user?->can('viewAny', DeviceGroup::class)) {
            $fields[] = [
                'key' => 'device.groups.id',
                'label' => __('device.device_group'),
                'type' => 'select',
                'endpoint' => route('ajax.select.device-group'),
            ];
        }

        return $fields;
    }

    /**
     * Map a state filter value (name, or numeric for older urls) to the state value
     */
    public static function filterStateValue(mixed $state): ?int
    {
        if (is_numeric($state)) {
            return (int) $state;
        }

        return static::filterStates()[strtolower((string) $state)][0] ?? null;
    }

    /**
     * Name used by the state filter for a state value
     */
    public static function filterStateName(int $value): ?string
    {
        foreach (static::filterStates() as $name => [$stateValue]) {
            if ($stateValue === $value) {
                return $name;
            }
        }

        return null;
    }

    public function filterState(Builder $query, mixed $value, array $config): void
    {
        // state always has a value: "None" (is_empty) matches nothing, "Any" (is_not_empty) everything
        if ($config['null'] ?? false) {
            if (! ($config['not'] ?? false)) {
                $query->whereRaw('0 = 1');
            }

            return;
        }

        $column = $this->qualifyColumn('state');
        // unknown states match nothing instead of being cast to 0 (Clear) by the database
        $this->applyMappedFilter($query, $value, $config, fn (Builder $q, $state) => $q->where($column, static::filterStateValue($state) ?? -1));
    }

    /**
     * Uncorrelated device_id subqueries instead of the nested correlated EXISTS per row a relation filter
     * would generate (alert_log is large)
     */
    public function filterDeviceGroupsId(Builder $query, mixed $value, array $config): void
    {
        $this->filterByDeviceIds($query, DB::table('device_group_device'), 'device_group_id', $value, $config);
    }

    public function filterDeviceLocationId(Builder $query, mixed $value, array $config): void
    {
        $this->filterByDeviceIds($query, DB::table('devices')->whereNotNull('location_id'), 'location_id', $value, $config);
    }

    /**
     * Filter on the device ids from $devices matching the filter values in $column.
     * "None" (is_empty) means the device has no match at all, "Any" (is_not_empty) that it has one.
     */
    private function filterByDeviceIds(Builder $query, QueryBuilder $devices, string $column, mixed $value, array $config): void
    {
        $exclude = $config['not'] ?? false;

        if ($config['null'] ?? false) {
            $exclude = ! $exclude;
        } else {
            $devices->whereIn($column, is_array($value) ? $value : explode(',', (string) $value));
        }

        $query->whereIn($this->qualifyColumn('device_id'), $devices->select('device_id'), not: $exclude);
    }

    public function filterSearch(Builder $query, mixed $value, array $config): void
    {
        $this->applyFilterSearch(['rule.name', 'device.hostname', 'device.sysName', 'device.display'], $query, $value, $config);
    }
}
