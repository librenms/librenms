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

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;

class Eloquent
{
    /** @var Capsule static reference to capsule */
    private static $capsule;

    public static function boot()
    {
        // boot Eloquent outside of Laravel
        if (!defined('LARAVEL_START') && class_exists(Capsule::class)) {
            $install_dir = realpath(__DIR__ . '/../../');

            self::$capsule = new Capsule;
            $db_config = include($install_dir . '/config/database.php');
            self::$capsule->addConnection($db_config['connections'][$db_config['default']]);
            self::$capsule->setEventDispatcher(new Dispatcher());
            self::$capsule->setAsGlobal();
            self::$capsule->bootEloquent();
        }
    }

    public static function isConnected()
    {
        $conn = self::DB();
        if ($conn) {
            return (bool)$conn->getDatabaseName();
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
        if (class_exists('DB')) {
            return \DB::connection();
        }

        if (is_null(self::$capsule)) {
            return null;
        }

        return self::$capsule->getDatabaseManager()->connection();
    }
}
