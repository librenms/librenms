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
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

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
        Log::setDefaultDriver('console');
    }

    /**
     * Boot Laravel with session and auth for standalone v1 plugin scripts (such as the Weathermap editor).
     * The request is routed to an empty endpoint, so the script can call Auth::check() and output its own response.
     */
    public static function bootWeb(): void
    {
        if (self::isBooted()) {
            return;
        }

        define('LARAVEL_START', microtime(true));
        $install_dir = realpath(__DIR__ . '/../..');
        $app = require_once $install_dir . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        // json accept prevents a login redirect back to the boot url
        $request = Request::create('/legacy-plugin-boot', 'GET', [], $_COOKIE, [], array_replace($_SERVER, ['HTTP_ACCEPT' => 'application/json']));
        $response = $kernel->handle($request);

        // stop the plugin if middleware refused the request (guest, disabled user, 2fa, etc)
        if ($response->getStatusCode() !== 204) {
            $response = $response->getStatusCode() === 401 ? redirect()->route('login') : $response;
            $response->send();
            $kernel->terminate($request, $response);
            exit;
        }
    }

    public static function isBooted()
    {
        return function_exists('app') && ! empty(app()->isAlias(\Illuminate\Foundation\Application::class)) && app()->isBooted();
    }

    /**
     * Check if running in the command line.
     * Safe for code without Laravel running and in Laravel console application.
     *
     * @return bool
     */
    public static function isCli(): bool
    {
        return Laravel::isBooted()
            ? App::runningInConsole()
            : (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
    }
}
