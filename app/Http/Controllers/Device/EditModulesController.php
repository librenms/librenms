<?php

/**
 * EditModulesController.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use LibreNMS\Util\Module;

class EditModulesController
{
    use AuthorizesRequests;

    /**
     * @var array<string, array{config: string, attrib: string}>
     */
    private const TYPES = [
        'polling' => ['config' => 'poller_modules', 'attrib' => 'poll_'],
        'discovery' => ['config' => 'discovery_modules', 'attrib' => 'discover_'],
    ];

    public function index(Device $device): View
    {
        $this->authorize('update', $device);

        $attribs = $device->getAttribs();
        $modules = [];

        foreach (self::TYPES as $type => ['config' => $configKey, 'attrib' => $attribPrefix]) {
            foreach ($this->modules($configKey) as $module => $globalEnabled) {
                $osEnabled = LibrenmsConfig::has("os.$device->os.$configKey.$module")
                    ? (bool) LibrenmsConfig::get("os.$device->os.$configKey.$module")
                    : null;
                $deviceEnabled = isset($attribs[$attribPrefix . $module]) ? (bool) $attribs[$attribPrefix . $module] : null;

                $modules[$module][$type] = [
                    'global' => (bool) $globalEnabled,
                    'os' => $osEnabled,
                    'device' => $deviceEnabled,
                ];
            }
        }

        ksort($modules);

        foreach ($modules as $module => $settings) {
            $modules[$module] = [
                'module' => $module,
                'name' => $this->moduleName($module),
                'has_data' => Module::fromName($module)->dataExists($device),
                'discovery' => $settings['discovery'] ?? null,
                'polling' => $settings['polling'] ?? null,
            ];
        }

        return view('device.edit.modules', [
            'device' => $device,
            'modules' => array_values($modules),
        ]);
    }

    public function update(Request $request, Device $device, string $module): JsonResponse
    {
        $this->authorize('update', $device);

        $validated = $request->validate([
            'discovery' => 'sometimes|in:true,false,clear',
            'polling' => 'sometimes|in:true,false,clear',
        ]);

        foreach (self::TYPES as $type => ['config' => $configKey, 'attrib' => $attribPrefix]) {
            if (! isset($validated[$type])) {
                continue;
            }

            abort_unless(array_key_exists($module, $this->modules($configKey)), 404);

            if ($validated[$type] == 'clear') {
                $device->forgetAttrib($attribPrefix . $module);
            } else {
                $device->setAttrib($attribPrefix . $module, $validated[$type] == 'true' ? 1 : 0);
            }
        }

        // return the effective module status
        return response()->json([
            'discovery' => (bool) $device->getAttrib('discover_' . $module, LibrenmsConfig::getCombined($device->os, 'discovery_modules')[$module] ?? false),
            'polling' => (bool) $device->getAttrib('poll_' . $module, LibrenmsConfig::getCombined($device->os, 'poller_modules')[$module] ?? false),
        ]);
    }

    public function delete(Device $device, string $module): JsonResponse
    {
        $this->authorize('delete', $device);

        abort_unless(array_key_exists($module, $this->modules('poller_modules') + $this->modules('discovery_modules')), 404);

        return response()->json([
            'deleted' => Module::fromName($module)->cleanup($device),
        ]);
    }

    private function moduleName(string $module): string
    {
        foreach (self::TYPES as ['config' => $configKey]) {
            $descriptionKey = "settings.settings.$configKey.$module.description";
            if (Lang::has($descriptionKey)) {
                return __($descriptionKey);
            }
        }

        return $module;
    }

    /**
     * Toggleable modules, core cannot be toggled
     *
     * @return array<string, mixed>
     */
    private function modules(string $configKey): array
    {
        $modules = (array) LibrenmsConfig::get($configKey, []);
        unset($modules['core']);

        return $modules;
    }
}
