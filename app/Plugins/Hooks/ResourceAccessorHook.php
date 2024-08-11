<?php
/*
 * ResourceAccessorHook.php
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
 * @copyright  2024 PipoCanaja
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

namespace App\Plugins\Hooks;

use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

abstract class ResourceAccessorHook
{
    public string $view = ''; // We don't use

    public function authorize(User $user): bool
    {
        return true;
    }

    public function processRequest(Request $request, string $path, array $settings)
    {
        return ['action' => 'abort', 'abort_type' => 404];
    }

    final public function handle(string $pluginName, Request $request, string $path, array $settings, Application $app)
    {
        return $app->call([$this, 'processRequest'], [
            'request' => $request,
            'path' => $path,
            'settings' => $settings,
        ]);
    }
}
