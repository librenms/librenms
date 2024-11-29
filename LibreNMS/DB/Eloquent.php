<?php
/**
 * Eloquent.php
 *
 * Class for managing Eloquent outside of Laravel
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\DB;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use LibreNMS\Util\Laravel;
use PDOException;

class Eloquent
{
    /**
     * Set the strict mode for the current connection (will not persist)
     *
     * @param  bool  $strict
     */
    public static function setStrictMode(bool $strict = true): void
    {
        if (self::isConnected() && self::getDriver() == 'mysql') {
            if ($strict) {
                self::DB()->getPdo()->exec("SET sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            } else {
                self::DB()->getPdo()->exec("SET sql_mode=''");
            }
        }
    }

    public static function isConnected(?string $name = null): bool
    {
        try {
            $conn = self::DB($name);
            if ($conn) {
                $conn->getPdo();

                return true;
            }
        } catch (PDOException $e) {
            return false;
        }

        return false;
    }

    /**
     * Access the Database Manager for Fluent style queries. Like the Laravel DB facade.
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Connection|null
     */
    public static function DB(?string $name = null): ?Connection
    {
        // check if Laravel is booted
        if (Laravel::isBooted()) {
            return DB::connection($name);
        }

        return null;
    }

    public static function getDriver(): ?string
    {
        $connection = config('database.default');

        return config("database.connections.{$connection}.driver");
    }

    /**
     * Set the active connection, used during install
     *
     * @param  string  $name
     * @param  string  $db_host
     * @param  string  $db_user
     * @param  string  $db_pass
     * @param  string  $db_name
     * @param  int|string  $db_port
     * @param  string  $db_socket
     * @return void
     */
    public static function setConnection($name, $db_host = null, $db_user = '', $db_pass = '', $db_name = '', $db_port = null, $db_socket = null): void
    {
        \Config::set("database.connections.$name", [
            'driver' => 'mysql',
            'host' => $db_host,
            'port' => $db_port,
            'database' => $db_name,
            'username' => $db_user,
            'password' => $db_pass,
            'unix_socket' => $db_socket,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        \Config::set('database.default', $name);
    }
}
