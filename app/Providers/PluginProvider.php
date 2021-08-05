<?php
/*
 * PluginProvider.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Providers;

use App\Exceptions\PluginDoesNotImplementHookException;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Plugins\PluginManager;
use View;

class PluginProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager;
        });
    }

    public function boot()
    {
        $manager = app(PluginManager::class);

        $this->loadLocalPlugins($manager);

        if ($manager->hasPlugins()) {
            View::addLocation(base_path('app/Plugins'));
        }
    }

    /**
     * Load any local plugins these plugins must implement only one hook.
     */
    protected function loadLocalPlugins(PluginManager $manager)
    {
        foreach (glob(base_path('app/Plugins/*/*.php')) as $file) {
            if (preg_match('#([^/]+)/([^/.]+)\.php#', $file, $matches)) {
                if ($matches[1] == 'Hooks') {
                    continue;  // don't load the hooks :D
                }

                $class = $this->className($matches[1], $matches[2]);
                $hook = $this->hookName($class);
                $manager->publishHook($hook, $class);
            }
        }
    }

    protected function hookName($class)
    {
        foreach (class_parents($class) as $parent) {
            if (Str::startsWith($parent, 'App\Plugins\Hooks\\')) {
                return $parent;
            }
        }

        throw new PluginDoesNotImplementHookException($class);
    }

    protected function className($dir, $name)
    {
        return 'App\Plugins\\' . $dir . '\\' . $name;
    }
}
