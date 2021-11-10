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
use App\Plugins\PluginManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class PluginProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PluginManager::class, function ($app) {
            return new PluginManager;
        });
    }

    public function boot(): void
    {
        $this->loadLocalPlugins($this->app->make(PluginManager::class));
    }

    /**
     * Load any local plugins these plugins must implement only one hook.
     */
    protected function loadLocalPlugins(PluginManager $manager): void
    {
        $plugin_view_location_registered = [];

        foreach (glob(base_path('app/Plugins/*/*.php')) as $file) {
            if (preg_match('#^(.*/([^/]+))/([^/.]+)\.php#', $file, $matches)) {
                $plugin_name = $matches[2]; // containing directory name
                if ($plugin_name == 'Hooks') {
                    continue;  // don't load the hooks :D
                }

                $class = $this->className($plugin_name, $matches[3]);
                $hook_type = $this->hookType($class);

                // publish hooks in class
                $hook_published = $manager->publishHook($plugin_name, $hook_type, $class);

                // register view namespace
                if ($hook_published && ! in_array($plugin_name, $plugin_view_location_registered)) {
                    $plugin_view_location_registered[] = $plugin_name;  // don't register twice
                    $this->loadViewsFrom($matches[1], $plugin_name);
                }
            }
        }
    }

    /**
     * Check if a hook is extended by the given class.
     *
     * @param  string  $class
     * @return string
     *
     * @throws \App\Exceptions\PluginDoesNotImplementHookException
     */
    protected function hookType(string $class): string
    {
        foreach (class_parents($class) as $parent) {
            if (Str::startsWith($parent, 'App\Plugins\Hooks\\')) {
                return $parent;
            }
        }

        throw new PluginDoesNotImplementHookException($class);
    }

    protected function className(string $dir, string $name): string
    {
        return 'App\Plugins\\' . $dir . '\\' . $name;
    }
}
