<?php
/**
 * SyslogSeverity.php
 *
 * Mapping of syslog priorities.  For user translated strings trans('syslog.0') = Emergency.
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Enum;

class SyslogSeverity
{
    const EMERGENCY = 'emerg';
    const ALERT = 'alert';
    const ERROR = 'err';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    const CRITICAL = 'crit';

    const LEVELS = [
        0 => self::EMERGENCY,
        1 => self::ALERT,
        2 => self::CRITICAL,
        3 => self::ERROR,
        4 => self::WARNING,
        5 => self::NOTICE,
        6 => self::INFO,
        7 => self::DEBUG,
    ];

    const STATUS = [
        self::EMERGENCY => CheckStatus::ERROR,
        self::ALERT => CheckStatus::ERROR,
        self::CRITICAL => CheckStatus::ERROR,
        self::ERROR => CheckStatus::ERROR,
        self::WARNING => CheckStatus::WARNING,
        self::NOTICE => CheckStatus::OK,
        self::DEBUG => CheckStatus::UNKNOWN,
        self::INFO => CheckStatus::OK,
    ];
}
