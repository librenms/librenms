<?php
/*
 * Tripplite.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use LibreNMS\Enum\Severity;
use LibreNMS\Snmptrap\Trap;

class Tripplite
{
    protected function getSeverity(Trap $trap): Severity
    {
        return match ($trap->getOidData('TRIPPLITE-PRODUCTS::tlpAlarmType')) {
            'critical' => Severity::Error,
            'warning' => Severity::Warning,
            'info' => Severity::Info,
            'status' => Severity::Notice,
            default => Severity::Warning,
        };
    }

    protected function describe(Trap $trap): string
    {
        return 'Trap Alarm ' . $trap->getOidData('TRIPPLITE-PRODUCTS::tlpAlarmState') . ': ' . $trap->getOidData('TRIPPLITE-PRODUCTS::tlpAlarmDetail');
    }
}
