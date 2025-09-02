<?php

/*
 * MaintenanceAlertBehavior.php
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Enum;

enum MaintenanceBehavior: int
{
    case SKIP_ALERTS = 1;
    case MUTE_ALERTS = 2;
    case RUN_ALERTS = 3;

    public function descr(): string
    {
        return match ($this) {
            self::SKIP_ALERTS => __('alerting.maintenance.behavior.options.skip_alerts') ,
            self::MUTE_ALERTS => __('alerting.maintenance.behavior.options.mute_alerts'),
            self::RUN_ALERTS => __('alerting.maintenance.behavior.options.run_alerts'),
        };
    }
}
