<?php
/**
 * SensorModel.php
 *
 * Common interfaces for sensor tables that share the same structore
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

namespace App\Models;

use App\Models\Traits\HasThresholds;
use LibreNMS\Enum\WirelessSensorType;

/**
 * @template T
 * @property int $sensor_id
 * @property int $sensor_deleted
 * @property string|WirelessSensorType $sensor_class
 * @property int $device_id
 * @property string|null $sensor_index
 * @property string $sensor_type
 * @property string|null $sensor_descr
 * @property int $sensor_divisor
 * @property int $sensor_multiplier
 * @property float|null $sensor_current
 * @property float|null $sensor_limit
 * @property float|null $sensor_limit_warn
 * @property float|null $sensor_limit_low
 * @property float|null $sensor_limit_low_warn
 * @property bool|int $sensor_alert
 * @property string $sensor_custom
 * @property string|null $entPhysicalIndex
 * @property string|null $entPhysicalIndex_measured
 * @property string $lastupdate
 * @property float|null $sensor_prev
 * @property string $rrd_type
 */
abstract class SensorModel extends DeviceRelatedModel
{
    use HasThresholds;

    abstract public function formatValue($field = 'sensor_current'): string;
    abstract public function getGraphType(): string;
    abstract public function unit(): string;
    abstract public function unitLong(): string;
    abstract public function icon(): string;
    abstract public function classDescr(): string;
    abstract public function classDescrLong(): string;
}
