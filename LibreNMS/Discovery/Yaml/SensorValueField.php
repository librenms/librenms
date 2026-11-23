<?php

/**
 * SensorValueField.php
 *
 * The value of a yaml sensor, state and temperature strings are converted to numbers
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

namespace LibreNMS\Discovery\Yaml;

use LibreNMS\Enum\SensorType;
use LibreNMS\Util\Number;

class SensorValueField extends OidField
{
    /**
     * A user function the value needs, set when a temperature is given in fahrenheit
     */
    public ?string $userFunc = null;

    /**
     * The value as returned by the device
     */
    public mixed $raw = null;

    public function __construct(private readonly SensorType $type)
    {
        parent::__construct('value', 'sensor_current');
    }

    /**
     * @param  array<string, mixed>  $yaml
     * @param  array<int|string, mixed>  $data
     */
    public function calculateValue(array $yaml, array $data, string $index, int $count): void
    {
        $this->raw = self::raw($yaml, $data[$yaml['oid'] ?? ''][$index] ?? []);
        [$value, $this->userFunc] = self::parse($this->raw, $this->type, $yaml);
        $this->value = $value === null ? null : Number::cast($value);
    }

    /**
     * The value of a yaml item in a walked row
     *
     * @param  array<string, mixed>  $yaml
     */
    public static function raw(array $yaml, mixed $row): mixed
    {
        return is_array($row) ? ($row[$yaml['value'] ?? $yaml['oid'] ?? ''] ?? '') : '';
    }

    /**
     * Convert a value to a number: states are translated, temperatures may be strings like "21 C" or "70 F"
     * and the first number is taken from other strings. Null when there is no number.
     *
     * @param  array{states?: list<array{value: int|string, descr: string}>}  $yaml
     * @return array{int|float|string|null, ?string} the value and the user function it needs
     */
    public static function parse(mixed $value, SensorType $type, array $yaml): array
    {
        if (is_numeric($value)) {
            return [$value, null];
        }

        $value = (string) $value;
        $userFunc = null;
        $states = array_column($yaml['states'] ?? [], 'value', 'descr');

        if ($type === SensorType::State && array_key_exists($value, $states)) {
            return [$states[$value], null];
        }

        if ($type === SensorType::Temperature) {
            if (preg_match('/([0-9.-]+)[^0-9]*?[cC]/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/([0-9.-]+)[^0-9]*?[fF]/', $value, $matches)) {
                $value = $matches[1];
                $userFunc = 'fahrenheit_to_celsius';
            }
        }

        if (preg_match('/-?\d*\.?\d+/', $value, $matches)) {
            $value = $matches[0];
        }

        return [is_numeric($value) ? $value : null, $userFunc];
    }
}
