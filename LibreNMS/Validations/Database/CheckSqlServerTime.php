<?php
/*
 * CheckSqlServerTime.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations\Database;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\ValidationResult;

class CheckSqlServerTime implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        $raw_time = Eloquent::DB()->selectOne('SELECT NOW() as time')->time;
        $db_time = new Carbon($raw_time);
        $php_time = Carbon::now();

        $diff = $db_time->diffAsCarbonInterval($php_time);

        if ($diff->compare(CarbonInterval::minute(1)) > 0) {
            $message = "Time between this server and the mysql database is off\n Mysql time :mysql_time\n PHP time :php_time";
            $message .= ' Mysql time ' . $db_time->toDateTimeString() . PHP_EOL;
            $message .= ' PHP time ' . $php_time->toDateTimeString() . PHP_EOL;

            return ValidationResult::fail(trans('validation.validations.database.CheckSqlServerTime.fail', [
                'mysql_time' => $db_time->toDateTimeString(),
                'php_time' => $php_time->toDateTimeString(),
            ]));
        }

        return ValidationResult::ok(trans('validation.validations.database.CheckSqlServerTime.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected();
    }
}
