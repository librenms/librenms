<?php
/**
 * Database.php
 *
 * Checks the database for errors
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Validations;

use LibreNMS\Validations\Database\CheckDatabaseServerVersion;
use LibreNMS\Validations\Database\CheckDatabaseTableNamesCase;
use LibreNMS\Validations\Database\CheckMysqlEngine;
use LibreNMS\Validations\Database\CheckSqlServerTime;
use LibreNMS\Validator;

class Database extends BaseValidation
{
    public const MYSQL_MIN_VERSION = '5.7.7';
    public const MYSQL_MIN_VERSION_DATE = 'March, 2021';
    public const MYSQL_RECOMMENDED_VERSION = '8.0';

    public const MARIADB_MIN_VERSION = '10.2.2';
    public const MARIADB_MIN_VERSION_DATE = 'March, 2021';
    public const MARIADB_RECOMMENDED_VERSION = '10.5';

    protected $directory = 'Database';
    protected $name = 'database';

    /**
     * Tests used by the installer to validate that SQL server doesn't have any known issues (before migrations)
     */
    public function validateSystem(Validator $validator): void
    {
        $validator->result((new CheckDatabaseServerVersion)->validate(), $this->name);
        $validator->result((new CheckMysqlEngine)->validate(), $this->name);
        $validator->result((new CheckSqlServerTime)->validate(), $this->name);
        $validator->result((new CheckDatabaseTableNamesCase)->validate(), $this->name);
    }
}
