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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App;
use Symfony\Component\HttpFoundation\HeaderBag;

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

    /**
     * Boot Laravel in a non-Laravel web script
     *
     * @param bool $authenticate Use session+db to authenticate user (does not authorize)
     */
    public static function bootWeb($authenticate = false)
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

        self::rewriteDummyHeaders($request, $authenticate);

        $response = $kernel->handle($request);

//        $response->send(); // don't send response, legacy code will
    }

    public static function isBooted()
    {
        return function_exists('app') && ! empty(app()->isAlias('Illuminate\Foundation\Application')) && app()->isBooted();
    }

    /**
     * Check if running in the command line.
     * Safe for code without Laravel running and in Laravel console application.
     * @return bool
     */
    public static function isCli(): bool
    {
        return Laravel::isBooted()
            ? App::runningInConsole()
            : (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
    }

    /**
     * Add prefix and strip .php to make the url helper work in non-laravel php scripts
     *
     * @param \Illuminate\Http\Request $request
     * @param bool $auth
     */
    private static function rewriteDummyHeaders($request, $auth)
    {
        // set dummy path allows url helper to work and prevents full init again
        $new_uri = ($auth ? '/dummy_legacy_auth' : '/dummy_legacy_unauth');
        $request->server->set('REQUEST_URI', $new_uri);

        // tests fail without this
        if ($request->server->get('REMOTE_ADDR') === null) {
            $request->server->set('REMOTE_ADDR', '127.0.0.1');
        }

        // set json type to prevent redirects in the dummy page
        $request->server->set('HTTP_ACCEPT', 'dummy/json');

        $request->headers = new HeaderBag($request->server->getHeaders());
    }
}
