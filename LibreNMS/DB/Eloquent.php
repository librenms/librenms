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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\DB;

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Events\StatementPrepared;
use Illuminate\Events\Dispatcher;
use LibreNMS\Util\Laravel;

class Eloquent
{
    /** @var Capsule static reference to capsule */
    private static $capsule;

    public static function boot()
    {
        // boot Eloquent outside of Laravel
        if (!Laravel::isBooted() && is_null(self::$capsule)) {
            $install_dir = realpath(__DIR__ . '/../../');

            Dotenv::create($install_dir)->load();

            $db_config = include($install_dir . '/config/database.php');
            $settings = $db_config['connections'][$db_config['default']];

            self::$capsule = new Capsule;
            self::$capsule->addConnection($settings);
            self::$capsule->setEventDispatcher(new Dispatcher());
            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }

        self::initLegacyListeners();
        self::setStrictMode(false); // set non-strict mode if for legacy code
    }

    public static function initLegacyListeners()
    {
        if (self::isConnected()) {
            // set FETCH_ASSOC for queries that required by setting the global variable $PDO_FETCH_ASSOC (for dbFacile)
            self::DB()->getEventDispatcher()->listen(StatementPrepared::class, function ($event) {
                global $PDO_FETCH_ASSOC;
                if ($PDO_FETCH_ASSOC) {
                    $event->statement->setFetchMode(\PDO::FETCH_ASSOC);
                }
            });
        }
    }

    /**
     * Set the strict mode for the current connection (will not persist)
     * @param bool $strict
     */
    public static function setStrictMode($strict = true)
    {
        if (self::isConnected()) {
            if ($strict) {
                self::DB()->getPdo()->exec("SET sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            } else {
                self::DB()->getPdo()->exec("SET sql_mode=''");
            }
        }
    }

    public static function isConnected()
    {
        try {
            $conn = self::DB();
            if ($conn) {
                return !is_null($conn->getPdo());
            }
        } catch (\PDOException $e) {
            return false;
        }

        return false;
    }

    /**
     * Access the Database Manager for Fluent style queries. Like the Laravel DB facade.
     *
     * @return \Illuminate\Database\Connection
     */
    public static function DB()
    {
        // check if Laravel is booted
        if (Laravel::isBooted()) {
            return \DB::connection();
        }

        if (is_null(self::$capsule)) {
            return null;
        }

        return self::$capsule->getDatabaseManager()->connection();
    }
}
