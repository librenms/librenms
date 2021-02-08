<?php
/**
 * RuckusSzSeverity.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Sets the LibreNMS alert level based on ruckusSZEventSeverity in the
 * trap.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

class RuckusSzSeverity
{
    public static function getSeverity($severity)
    {
        switch ($severity) {
            case 'Critical':
                $severityNum = 5;
                break;
            case 'Major':
                $severityNum = 4;
                break;
            case 'Minor':
                $severityNum = 4;
                break;
            case 'Warning':
                $severityNum = 3;
                break;
            case 'Informational':
                $severityNum = 2;
                break;
            default:
                $severityNum = 2;
                break;
        }

        return $severityNum;
    }
}
