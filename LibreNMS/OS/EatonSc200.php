<?php

/**
 * EatonSc200.php
 *
 * Eaton SC200 helpers for YAML discovery.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Bernhard Steinert
 * @author     Bernhard Steinert <bernhard@steinert.net>
 */

namespace LibreNMS\OS;

use LibreNMS\OS;

class EatonSc200 extends OS
{
    /**
     * Not-a-number sentinel (2^32 - 100) the SC200 returns for unavailable TimeTicks values, compared as string to stay 32-bit safe.
     */
    private const TIMETICKS_NAN = '4294967196';

    /**
     * notAvailable value of RPS-SC200-MIB::Percentile8NAN.
     */
    private const PERCENT_NAN = 255;

    /**
     * Convert RPS-SC200-MIB::btr-Time-Remaining (TimeTicks) to minutes.
     *
     * The controller only estimates the remaining time during a discharge of a
     * characterized battery. At all other times it returns the NaN sentinel,
     * which must not be recorded as a value (yaml user_func).
     */
    public static function btrTimeRemaining(int|float|string $value): ?float
    {
        if ((string) $value === self::TIMETICKS_NAN) {
            return null;
        }

        return round((float) $value / 6000, 2);
    }

    /**
     * RPS-SC200-MIB::btr-State-Of-Health in percent.
     *
     * The controller reports notAvailable(255), the DEFVAL, until the first
     * discharge deep enough to estimate the capacity; that is not a value and
     * must not be drawn as 255 % (yaml user_func).
     */
    public static function stateOfHealth(int|float|string $value): ?float
    {
        if ((int) $value === self::PERCENT_NAN) {
            return null;
        }

        return (float) $value;
    }
}
