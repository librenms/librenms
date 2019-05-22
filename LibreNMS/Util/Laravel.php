<?php
/**
 * Laravel.php
 *
 * Utility class to gather code to do
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

namespace LibreNMS\Util;

use App;
use Illuminate\Database\Events\QueryExecuted;
use LibreNMS\DB\Eloquent;
use Log;

class Laravel
{
    public static function bootCli()
    {
        // make sure Laravel isn't already booted
        if (self::isBooted()) {
            return;
        }

        define('LARAVEL_START', microtime(true));
        $install_dir = realpath(__DIR__ . '/../..');
        $app = require_once $install_dir . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
    }

    public static function bootWeb()
    {
        // this is not a substitute for the normal Laravel boot, just a way to make auth work for external php
        if (self::isBooted()) {
            return;
        }

        define('LARAVEL_START', microtime(true));
        $install_dir = realpath(__DIR__ . '/../..');
        $app = require_once $install_dir . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        $request = \Illuminate\Http\Request::capture();
        // strip .php to make the url helper in non-laravel pages
        $request->server->set('REQUEST_URI', str_replace('.php', '', $_SERVER['REQUEST_URI']));
        $response = $kernel->handle($request);

//        $response->send(); // don't send response, legacy code will
    }

    public static function isBooted()
    {
        return !empty(app()->isAlias('Illuminate\Foundation\Application')) && app()->isBooted();
    }

    public static function enableQueryDebug()
    {
        $db = Eloquent::DB();

        if ($db && !$db->getEventDispatcher()->hasListeners('Illuminate\Database\Events\QueryExecuted')) {
            $db->listen(function (QueryExecuted $query) {
                // collect bindings and make them a little more readable
                $bindings = collect($query->bindings)->map(function ($item) {
                    if ($item instanceof \Carbon\Carbon) {
                        return $item->toDateTimeString();
                    }

                    return $item;
                })->toJson();

                if (self::isBooted()) {
                    Log::debug("SQL[%Y{$query->sql} %y$bindings%n {$query->time}ms] \n", ['color' => true]);
                } else {
                    c_echo("SQL[%Y{$query->sql} %y$bindings%n {$query->time}ms] \n");
                }
            });
        }
    }

    public static function disableQueryDebug()
    {
        $db = Eloquent::DB();

        if ($db) {
            // remove all query executed event handlers
            $db->getEventDispatcher()->flush('Illuminate\Database\Events\QueryExecuted');
        }
    }

    public static function enableCliDebugOutput()
    {
        if (self::isBooted() && App::runningInConsole()) {
            Log::setDefaultDriver('console');
        }
    }

    public static function disableCliDebugOutput()
    {
        if (self::isBooted()) {
            Log::setDefaultDriver('logfile');
        }
    }
}
