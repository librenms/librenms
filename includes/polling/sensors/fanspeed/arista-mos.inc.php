<?php
/**
 * arista-mos.inc.php
 *
 * Copyright (C) 2018 Goldman Sachs & Co.
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
 * @author     Nash Kaminski <Nash.Kaminski@gs.com>
 */

// Workaround for Metamako platform fanspeed reporting bug on early (circa 0.16.x) code versions
if ((strpos($sensor['sensor_oid'], '.1.3.6.1.4.1.2021.13.16.3.1.3.') === 0) &&
        ($sensor_value >= 2 ** 31)) {
    // 2's complement negation of the value
    $sensor_value = $sensor_value ^ 0xFFFFFFFF;
    $sensor_value += 1;
}
