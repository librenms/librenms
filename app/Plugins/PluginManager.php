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
use LibreNMS\Util\Notifications;
use Log;

class PluginManager
{
    /** @var Collection */
    private $hooks;
    /** @var Collection */
    private $plugins;

    /** @var array */
    private $validPlugins = [];

    public function __construct()
    {
        $this->hooks = new Collection;
    }

    /**
     * Publish plugin hook, this is the main way to hook into different parts of LibreNMS.
     * plugin_name should be unique. For internal (user) plugins in the app/Plugins directory, the directory name will be used.
     * Hook type will be the full class name of the hook from app/Plugins/Hooks.
     *
     * @param  string  $pluginName
     * @param  string  $hookType
     * @param  string  $implementationClass
     * @return bool
     */
    public function publishHook(string $pluginName, string $hookType, string $implementationClass): bool
    {
        try {
            $instance = new $implementationClass;
            $this->validPlugins[$pluginName] = 1;

            if ($instance instanceof $hookType && $this->pluginEnabled($pluginName)) {
                if (! $this->hooks->has($hookType)) {
                    $this->hooks->put($hookType, new Collection);
                }

                $this->hooks->get($hookType)->push([
                    'plugin_name' => $pluginName,
                    'instance' => $instance,
                ]);

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
     * @param  string|null  $plugin  only for this plugin if set
     * @return bool
     */
    public function hasHooks(string $hookType, array $args = [], ?string $plugin = null): bool
    {
        return $this->hooksFor($hookType, $args, $plugin)->isNotEmpty();
    }

    /**
     * Coll all hooks for the given hook type.
     * args will be available for injection into the handle method to pass data through
     * settings is automatically injected
     *
     * @param  string  $hookType
     * @param  array  $args
     * @param  string|null  $plugin  only for this plugin if set
     * @return \Illuminate\Support\Collection
     */
    public function call(string $hookType, array $args = [], ?string $plugin = null): Collection
    {
        return $this->hooksFor($hookType, $args, $plugin)
            ->map(function ($hook) use ($args, $hookType) {
                try {
                    return app()->call([$hook['instance'], 'handle'], $this->fillArgs($args, $hook['plugin_name']));
                } catch (Exception|\Error $e) {
                    $name = $hook['plugin_name'];
                    Log::error("Error calling hook $hookType for $name: " . $e->getMessage() . PHP_EOL . $e->getTraceAsString());

                    if (\LibreNMS\Config::get('plugins.show_errors')) {
                        throw $e;
                    }

                    Notifications::create("Plugin $name disabled", "$name caused an error and was disabled, please check with the plugin creator to fix the error. The error can be found in logs/librenms.log", 'plugins', 2);
                    Plugin::where('plugin_name', $name)->update(['plugin_active' => 0]);

                    return 'HOOK FAILED';
                }
            })->filter(function ($hook) {
                return $hook !== 'HOOK FAILED';
            });
    }

    /**
     * Get the settings stored in the database for a plugin.
     * One plugin shares the settings across all hooks
     *
     * @param  string  $pluginName
     * @return array
     */
    public function getSettings(string $pluginName): array
    {
        return (array) $this->getPlugin($pluginName)->settings;
    }

    /**
     * Save settings array to the database for the given plugin
     *
     * @param  string  $pluginName
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
     * Check if plugin exists.
     * Does not create a DB entry if it does not exist.
     *
     * @param  string  $pluginName
     * @return bool
     */
    public function pluginExists(string $pluginName): bool
    {
        return $this->getPlugins()->has($pluginName);
    }

    /**
     * Check if plugin of the given name is enabled.
     * Creates DB entry if one does not exist yet.
     *
     * @param  string  $pluginName
     * @return bool
     */
    public function pluginEnabled(string $pluginName): bool
    {
        return (bool) $this->getPlugin($pluginName)?->plugin_active;
    }

    /**
     * Remove plugins that do not have any registered hooks.
     */
    public function cleanupPlugins(): void
    {
        try {
            $valid = array_keys($this->validPlugins);
            Plugin::versionTwo()->whereNotIn('plugin_name', $valid)->get()->each->delete();
        } catch (QueryException $qe) {
            Log::error('Failed to clean up plugins: ' . $qe->getMessage());
        }
    }

    protected function getPlugin(string $name): ?Plugin
    {
        $plugin = $this->getPlugins()->get($name);

        if (! $plugin) {
            try {
                // plugin should not exist, but check for safety
                $plugin = Plugin::firstOrCreate([
                    'version' => 2,
                    'plugin_name' => $name,
                ], [
                    'plugin_name' => $name,
                    'plugin_active' => $name !== 'ExamplePlugin',
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
     * @param  string  $hookType
     * @param  array  $args
     * @param  string|null  $onlyPlugin
     * @return \Illuminate\Support\Collection
     */
    protected function hooksFor(string $hookType, array $args, ?string $onlyPlugin): Collection
    {
        if (! $this->hooks->has($hookType)) {
            return new Collection;
        }

        return $this->hooks->get($hookType)
            ->when($onlyPlugin, function (Collection $hooks, $only) {
                return $hooks->where('plugin_name', $only);
            })
            ->filter(function ($hook) use ($args) {
                return app()->call([$hook['instance'], 'authorize'], $this->fillArgs($args, $hook['plugin_name']));
            });
    }

    protected function fillArgs(array $args, string $pluginName): array
    {
        if (isset($args['settings'])) {
            throw new PluginException('You cannot inject "settings", this is a reserved name');
        }

        if (isset($args['pluginName'])) {
            throw new PluginException('You cannot inject "pluginName", this is a reserved name');
        }

        return array_merge($args, [
            'pluginName' => $pluginName,
            'settings' => $this->getSettings($pluginName),
        ]);
    }
}
