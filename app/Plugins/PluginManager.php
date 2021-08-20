<?php
/*
 * PluginManager.php
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

namespace App\Plugins;

use App\Models\Plugin;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Log;
use ReflectionClass;
use ReflectionException;

class PluginManager
{
    /** @var array */
    private $hooks = [];
    /** @var Collection */
    private $plugins;

    public function publishHook(string $hook_type, string $implementation_class): void
    {
        try {
            if ($this->pluginEnabled($implementation_class)) {
                $this->hooks[$hook_type][] = new $implementation_class;
            }
        } catch (Exception $e) {
            Log::error("Error when loading hook $hook_type for $implementation_class: " . $e->getMessage());
        }

        // plugin disabled, log?
    }

    public function hooksFor(string $hook): Collection
    {
        return collect($this->hooks[$hook] ?? []);
    }

    public function hasPlugins(): bool
    {
        return ! empty($this->hooks);
    }

    public function hasHooks(string $hook, array $args = []): bool
    {
        return $this->hooksFor($hook)
            ->filter(function ($plugin) use ($args) {
                return app()->call([$plugin, 'authorize'], $args);
            })->isNotEmpty();
    }

    public function call(string $hook, array $args = []): Collection
    {
        try {
            return $this->hooksFor($hook)
                ->filter(function ($hookInstance) use ($args) {
                    $settings = ['settings' => $this->getSettings($hookInstance)];

                    return app()->call([$hookInstance, 'authorize'], $args + $settings);
                })
                ->map(function ($hookInstance) use ($args) {
                    $settings = ['settings' => $this->getSettings($hookInstance)];

                    return app()->call([$hookInstance, 'handle'], $args + $settings);
                });
        } catch (Exception $e) {
            Log::error("Error calling hook $hook: " . $e->getMessage());

            return new Collection;
        }
    }

    /**
     * @param  string|object  $name_or_hook
     * @return array
     */
    public function getSettings($name_or_hook): array
    {
        $name = $this->getPluginName($name_or_hook);

        return (array) $this->getPlugin($name)->settings;
    }

    /**
     * @param  string|object  $name_or_hook
     * @param  array  $settings
     * @return bool
     */
    public function setSettings($name_or_hook, array $settings): bool
    {
        $plugin = $this->getPlugin($this->getPluginName($name_or_hook));
        $plugin->settings = $settings;

        return $plugin->save();
    }

    /**
     * @param  string|object  $plugin
     * @param  string  $file
     * @return string|null
     */
    public function pluginPath($plugin, $file = null): ?string
    {
        try {
            $reflection = new ReflectionClass($plugin);

            return dirname($reflection->getFileName()) . '/' . $file;
        } catch (ReflectionException $e) {
            return null;
        }
    }

    public function pluginEnabled(string $class): bool
    {
        $name = $this->getPluginName($class);

        return $this->getPlugin($name)->plugin_active;
    }

    private function getPlugin(string $name): ?Plugin
    {
        $plugin = $this->getPlugins()->get($name);

        if (! $plugin) {
            // FIXME do not add plugins that don't exist
            try {
                $plugin = Plugin::create([
                    'plugin_name' => $name,
                    'plugin_active' => 1,
                    'version' => 2,
                ]);
                $this->getPlugins()->put($name, $plugin);
            } catch (QueryException $e) {
                // DB not migrated/connected
            }
        }

        return $plugin;
    }

    private function getPlugins(): Collection
    {
        if ($this->plugins === null) {
            try {
                $this->plugins = Plugin::versionTwo()->get()->keyBy('plugin_name');
            } catch (QueryException $e) {
                // DB not migrated/connected
                $this->plugins = new Collection;
            }
        }

        return $this->plugins;
    }

    /**
     * @param string|object $class
     * @return string
     */
    public function getPluginName($class): string
    {
        // if it is a plugin hook, get the namespace
        if (is_object($class) || class_exists($class)) {
            try {
                $reflection = new ReflectionClass($class);

                return $reflection->getNamespaceName();
            } catch (ReflectionException $e) {
                // fail
            }
        }

        return $class;
    }
}
