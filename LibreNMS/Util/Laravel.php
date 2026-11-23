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
