<?php
/*
 * CheckDatabaseServerVersion.php
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

use LibreNMS\DB\Eloquent;
use LibreNMS\Interfaces\Validation;
use LibreNMS\Util\Version;
use LibreNMS\ValidationResult;
use LibreNMS\Validations\Database;

class CheckDatabaseServerVersion implements Validation
{
    /**
     * @inheritDoc
     */
    public function validate(): ValidationResult
    {
        $version = Version::get()->databaseServer();
        $version = explode('-', $version);

        if (isset($version[1]) && $version[1] == 'MariaDB') {
            if (version_compare($version[0], Database::MARIADB_MIN_VERSION, '<=')) {
                return ValidationResult::fail(
                    trans('validation.validations.database.CheckDatabaseServerVersion.fail', ['server' => 'MariaDB', 'min' => Database::MARIADB_MIN_VERSION, 'date' => Database::MARIADB_MIN_VERSION_DATE]),
                    trans('validation.validations.database.CheckDatabaseServerVersion.fix', ['server' => 'MariaDB', 'suggested' => Database::MARIADB_RECOMMENDED_VERSION]),
                );
            }
        } else {
            if (version_compare($version[0], Database::MYSQL_MIN_VERSION, '<=')) {
                return ValidationResult::fail(
                    trans('validation.validations.database.CheckDatabaseServerVersion.fail', ['server' => 'MySQL', 'min' => Database::MYSQL_MIN_VERSION, 'date' => Database::MYSQL_MIN_VERSION_DATE]),
                    trans('validation.validations.database.CheckDatabaseServerVersion.fix', ['server' => 'MySQL', 'suggested' => Database::MYSQL_RECOMMENDED_VERSION]),
                );
            }
        }

        return ValidationResult::ok(trans('validation.validations.database.CheckDatabaseServerVersion.ok'));
    }

    /**
     * @inheritDoc
     */
    public function enabled(): bool
    {
        return Eloquent::isConnected();
    }
}
