<?php
/**
 * PowerState.php
 *
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
 */

namespace LibreNMS\Enum;

abstract class PowerState
{
    const OFF = 0;
    const ON = 1;
    const SUSPENDED = 2;
    const UNKNOWN = 3;

    const STATES = [
        'powered off' => self::OFF,
        'shut off' => self::OFF,

        'powered on' => self::ON,
        'running' => self::ON,

        'suspended' => self::SUSPENDED,
        'paused' => self::SUSPENDED,
    ];

    /**
     * @param int|string $state
     */
    public static function stateLabel($state): array
    {
        $state = is_string($state) ? self::STATES[$state] : $state;

        switch ($state) {
            case self::OFF:
                return ['OFF', 'label-default'];
            case self::ON:
                return ['ON', 'label-success'];
            case self::SUSPENDED:
                return ['SUSPENDED', 'label-warning'];
            default:
                return ['UNKNOWN', 'label-default'];
        }
    }
}
