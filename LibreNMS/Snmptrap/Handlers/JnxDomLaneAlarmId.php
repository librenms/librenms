<?php
/**
 * JnxLaneDomAlarmId.php
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
 *
 * Used covert alarm ID in the JnxDomLaneAlarm traps from Hex to a
 * descriptive string.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

class JnxDomLaneAlarmId
{
    public static function getLaneAlarms($currentAlarm)
    {
        $alarmBin = preg_split(
            '//',
            sprintf('%024s', decbin(hexdec(str_replace(' ', '', $currentAlarm)))),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $alarmDescr = [
            'input signal high',
            'input signal low',
            'output bias high',
            'output bias low',
            'output signal high',
            'output signal low',
            'lane laser temp high',
            'lane laster temp low',
        ];

        $descr = [];
        $index = 0;
        foreach ($alarmBin as $syntax) {
            if ($syntax == '1') {
                $descr[$index] = $alarmDescr[$index];
            }
            $index++;
        }
        $message = implode(', ', $descr);

        return $message;
    }
}
