<?php
/**
 * CheckInstalled.php
 *
 * Check if LibreNMS install has been completed (config.php exists) and redirect to install.php as needed.
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

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use LibreNMS\Util\EnvHelper;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $installed = ! config('librenms.install') && file_exists(base_path('.env'));
        $is_install_route = $request->is('install*');

        // further middleware will fail without an app key, init one
        if (empty(config('app.key'))) {
            config(['app.key' => EnvHelper::init()]);
        }

        if (! $installed && ! $is_install_route) {
            // redirect to install if not installed
            return redirect()->route('install');
        } elseif ($installed && $is_install_route) {
            // in case someone refreshes on the finish step
            if ($request->routeIs('install.finish')) {
                return redirect()->route('home');
            }
            throw new AuthorizationException('This should only be called during install');
        }

        return $next($request);
    }
}
