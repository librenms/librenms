<?php
/**
 * Alert.php
 *
 * Enumerates alarming Level
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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace LibreNMS\Enum;

abstract class Alert
{
    const UNKNOWN = 0;
    const OK = 1;
    const INFO = 2;
    const NOTICE = 3;
    const WARNING = 4;
    const ERROR = 5;

    const SEVERITIES = ['unknown' => self::UNKNOWN,
        'ok'      => self::OK,
        'info'    => self::INFO,
        'notice'  => self::NOTICE,
        'warning' => self::WARNING,
        'error'   => self::ERROR,
    ];
}
