<?php
/**
 * JnxDomAlarmId.php
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
 * Used covert alarm ID in the JnxDomAlarm traps from Hex to a
 * descriptive string.
 *
 * @link       https://www.librenms.org
 * @copyright  2019 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

class JnxDomAlarmId
{
    public static function getAlarms($currentAlarm)
    {
        $alarmBin = preg_split(
            '//',
            sprintf('%024s', decbin(hexdec(str_replace(' ', '', $currentAlarm)))),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        $alarmDescr = [
            'input loss of signal',
            'input loss of lock',
            'input rx path not ready',
            'input laser power high',
            'input laser power low',
            'output laser bias current high',
            'output laser bias current low',
            'output laser power high',
            'output laser power low',
            'output data not ready',
            'output tx path not ready',
            'output laser fault',
            'output loss of lock',
            'module temperature high',
            'module temperature low',
            'module not ready',
            'module power down',
            'wire unplugged or down',
            'module unplugged or down',
            'module voltage high',
            'module voltage low',
        ];

        $index = 0;
        $descr = [];
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
