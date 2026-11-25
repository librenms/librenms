<?php

/**
 * SensorYamlDiscoveryDefinition.php
 *
 * Discover the sensors of one sensor class from yaml
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery;

use App\Models\Sensor;
use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Discovery\Yaml\LiteralField;
use LibreNMS\Discovery\Yaml\SensorValueField;
use LibreNMS\Discovery\Yaml\TemplateField;
use LibreNMS\Discovery\Yaml\YamlDiscoveryField;
use LibreNMS\Enum\SensorCategory;
use LibreNMS\Enum\SensorType;
use LibreNMS\Util\Number;
use LibreNMS\Util\UserFuncHelper;

/**
 * Yaml items always read from walked data: rows of the item oid, the value defaults to the oid.
 * Limits from templates are scaled like the value, unless skip_limits_calc is set.
 */
class SensorYamlDiscoveryDefinition extends YamlDiscoveryDefinition
{
    private const array LIMITS = [
        'low_limit' => 'sensor_limit_low',
        'low_warn_limit' => 'sensor_limit_low_warn',
        'warn_limit' => 'sensor_limit_warn',
        'high_limit' => 'sensor_limit',
    ];

    private readonly SensorValueField $valueField;

    /**
     * @param  array{divisor?: int|string, multiplier?: int|string}  $options  options of the sensor class
     */
    public function __construct(private readonly SensorType $type, array $options = [])
    {
        parent::__construct(Sensor::class);

        $this->valueField = new SensorValueField($type);

        $this->addField(new TemplateField('index', 'sensor_index'))
            ->addField(new YamlDiscoveryField('descr', 'sensor_descr', '', fn ($value) => trim((string) $value)))
            ->addField(new YamlDiscoveryField('group', 'group', callback: fn ($value) => trim((string) $value) ?: null))
            ->addField(new YamlDiscoveryField('category', 'category', callback: fn ($value) => SensorCategory::tryFrom(trim((string) $value))))
            ->addField(new YamlDiscoveryField('divisor', 'sensor_divisor', $options['divisor'] ?? 1, fn ($value) => (int) $value ?: 1))
            ->addField(new YamlDiscoveryField('multiplier', 'sensor_multiplier', $options['multiplier'] ?? 1, fn ($value) => is_numeric($value) ? (int) $value : 1))
            ->addField(new YamlDiscoveryField('entPhysicalIndex', 'entPhysicalIndex', callback: fn ($value) => $value ?: null))
            ->addField(new LiteralField('entPhysicalIndex_measured', 'entPhysicalIndex_measured'))
            ->addField(new LiteralField('user_func', 'user_func'))
            ->addField(new LiteralField('rrd_type', 'rrd_type', 'GAUGE'));

        foreach (self::LIMITS as $key => $column) {
            $this->addField(new YamlDiscoveryField($key, $column));
        }

        $this->addField($this->valueField)
            ->afterEach($this->completeSensor(...));
    }

    /**
     * Skip rules are checked against the converted value, values without a number are skipped.
     *
     * @param  array<string, mixed>|string  $row
     * @param  array<string, mixed>  $yamlItem
     * @param  array<string, mixed>  $yaml
     * @param  array<int|string, mixed>  $fetchedData
     */
    protected function canSkipItem(array|string $row, int|string $index, array $yamlItem, array $yaml, array $fetchedData): bool
    {
        [$value] = SensorValueField::parse(SensorValueField::raw($yamlItem, $row), $this->type, $yamlItem);

        return $value === null || YamlDiscovery::canSkipItem($value, $index, $yamlItem, $yaml['options'] ?? [], $fetchedData);
    }

    /**
     * @param  array<string, mixed>  $yaml
     */
    private function completeSensor(Sensor $sensor, YamlDiscoveryDefinition $def, array $yaml, int|string $index): void
    {
        $sensor->sensor_index ??= $index;
        $sensor->user_func = $this->valueField->userFunc ?? $sensor->user_func;

        foreach (self::LIMITS as $key => $column) {
            $sensor->$column = $this->limit($sensor, $sensor->$column, ! is_numeric($yaml[$key] ?? null), empty($yaml['skip_limits_calc']));
        }

        $value = $sensor->sensor_current;
        if ($this->type === SensorType::State) {
            $sensor->sensor_type = $yaml['state_name'] ?? $yaml['oid'];
            $sensor->withStates($yaml['states'] ?? []);
        } else {
            $value = $value / $sensor->sensor_divisor * $sensor->sensor_multiplier;
        }

        if ($sensor->user_func) {
            $value = UserFuncHelper::apply($sensor->user_func, $value, $this->valueField->raw, $sensor->getAttributes());
        }

        $sensor->sensor_current = $value === null ? null : Number::cast($value);
    }

    /**
     * Limits given as numbers are used as is, limits from data are converted like the value.
     */
    private function limit(Sensor $sensor, mixed $limit, bool $fromData, bool $scale): int|float|string|null
    {
        if (! is_numeric($limit)) {
            return null;
        }

        if ($fromData) {
            if ($scale) {
                $limit = $limit / $sensor->sensor_divisor * $sensor->sensor_multiplier;
            }

            if ($sensor->user_func) {
                $limit = UserFuncHelper::apply($sensor->user_func, $limit);
            }
        }

        return is_numeric($limit) && is_finite((float) $limit) ? $limit : null;
    }
}
