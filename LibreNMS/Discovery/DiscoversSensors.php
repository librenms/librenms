<?php

/**
 * DiscoversSensors.php
 *
 * Marks an OS method that discovers sensors
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

use Attribute;
use LibreNMS\Enum\SensorType;

/**
 * An OS method with this attribute is called by sensor discovery when any of its sensor classes is discovered,
 * the method takes no arguments and returns a Collection of Sensor.
 * Sensors of other classes are dropped, no classes means the method may return any class.
 * An override keeps the classes of the method it overrides, unless it has its own attribute.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class DiscoversSensors
{
    /** @var list<SensorType> */
    public array $types;

    public function __construct(SensorType ...$types)
    {
        $this->types = array_values($types);
    }
}
