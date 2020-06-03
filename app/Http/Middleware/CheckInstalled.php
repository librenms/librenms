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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Middleware;

use Closure;

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
        if (!file_exists(storage_path('installed'))) {
            // no .env so set app key
            $this->checkEnvFile();

            if (config('database.connections.mysql.password')) {
                // assume if db password is set, app is installed
                touch(storage_path('installed'));
            } elseif (!$request->is('install*')) {
                // redirect to install
                return redirect(url('/install'));
            }
        }

        return $next($request);
    }

    private function checkEnvFile()
    {
        $envFile = base_path('.env');
        if (!file_exists($envFile)) {
            $key = $this->generateRandomKey();
            config(['app.key' => $key]);
            $this->writeNewEnvironmentFileWith($key, $envFile);
        }
    }

    private function generateRandomKey()
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    private function writeNewEnvironmentFileWith($key, $environmentFilePath)
    {
        file_put_contents($environmentFilePath, preg_replace(
            '/^APP_KEY=/m',
            'APP_KEY=' . $key,
            file_exists($environmentFilePath)
                ? file_get_contents($environmentFilePath)
                : file_get_contents(base_path('.env.example'))
        ));
    }
}
