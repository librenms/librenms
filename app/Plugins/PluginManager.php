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

use App\Exceptions\PluginException;
use App\Models\Plugin;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Log;

class PluginManager
{
    /** @var array */
    private $hooks = [];
    /** @var Collection */
    private $plugins;

    /**
     * Publish plugin hook, this is the main way to hook into different parts of LibreNMS.
     * plugin_name should be unique. For internal (user) plugins in the app/Plugins directory, the directory name will be used.
     * Hook type will be the full class name of the hook from app/Plugins/Hooks.
     *
     * @param  string  $pluginName
     * @param  string  $hookType
     * @param  string  $implementationClass
     */
    public function publishHook(string $pluginName, string $hookType, string $implementationClass): bool
    {
        try {
            if ($implementationClass instanceof $hookType && $this->pluginEnabled($pluginName)) {
                $this->hooks[$hookType][$pluginName] = new $implementationClass;

                return true;
            }
        } catch (Exception $e) {
            Log::error("Error when loading hook $implementationClass of type $hookType for $pluginName: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Check if there are any valid hooks
     *
     * @param  string  $hookType
     * @param  array  $args
     * @return bool
     */
    public function hasHooks(string $hookType, array $args = []): bool
    {
        return $this->hooksFor($hookType, $args)->isNotEmpty();
    }

    /**
     * Coll all hooks for the given hook type.
     * args will be available for injection into the handle method to pass data through
     * settings is automatically injected
     *
     * @param  string  $hookType
     * @param  array  $args
     * @return \Illuminate\Support\Collection
     */
    public function call(string $hookType, array $args = []): Collection
    {
        try {
            return $this->hooksFor($hookType, $args)
                ->map(function ($hook, $plugin_name) use ($args) {
                    return app()->call([$hook, 'handle'], $this->fillArgs($args, $plugin_name));
                });
        } catch (Exception $e) {
            Log::error("Error calling hook $hookType: " . $e->getMessage());

            return new Collection;
        }
    }

    /**
     * Get the settings stored in the database for a plugin.
     * One plugin shares the settings across all hooks
     *
     * @param  string $pluginName
     * @return array
     */
    public function getSettings(string $pluginName): array
    {
        return (array) $this->getPlugin($pluginName)->settings;
    }

    /**
     * Save settings array to the database for the given plugin
     *
     * @param  string $pluginName
     * @param  array  $settings
     * @return bool
     */
    public function setSettings(string $pluginName, array $settings): bool
    {
        $plugin = $this->getPlugin($pluginName);
        $plugin->settings = $settings;

        return $plugin->save();
    }

    /**
     * Check if plugin of the given name is enabled.
     *
     * @param  string  $pluginName
     * @return bool
     */
    public function pluginEnabled(string $pluginName): bool
    {
        return $this->getPlugin($pluginName)->plugin_active;
    }

    /**
     * Remove plugins that do not have any registered hooks.
     */
    public function cleanupPlugins(): void
    {
        $valid = collect($this->hooks)->map('array_keys')->flatten()->unique();
        Plugin::versionTwo()->whereNotIn('plugin_name', $valid)->get()->each->delete();
    }

    protected function getPlugin(string $name): ?Plugin
    {
        $plugin = $this->getPlugins()->get($name);

        if (! $plugin) {
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

    protected function getPlugins(): Collection
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
     * @param  string  $hook_type
     * @param  array  $args
     * @return \Illuminate\Support\Collection
     */
    protected function hooksFor(string $hook_type, array $args = []): Collection
    {
        return collect($this->hooks[$hook_type] ?? [])
            ->filter(function ($hook, $plugin_name) use ($args) {
                return app()->call([$hook, 'authorize'], $this->fillArgs($args, $plugin_name));
            });
    }

    protected function fillArgs(array $args, $plugin_name)
    {
        if (isset($args['settings'])) {
            throw new PluginException('You cannot inject "settings", this is a reserved name');
        }

        if (isset($args['pluginName'])) {
            throw new PluginException('You cannot inject "pluginName", this is a reserved name');
        }

        return array_merge($args, [
            'pluginName' => $plugin_name,
            'settings' => $this->getSettings($plugin_name),
        ]);
    }
}
