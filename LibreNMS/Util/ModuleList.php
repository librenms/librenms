<?php

/**
 * ModuleList.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\ProcessType;
use LibreNMS\Polling\ModuleStatus;

class ModuleList
{
    public readonly array $overrides;

    public function __construct(
        array $overrides = [],
    ) {
        $this->overrides = $this->parseUserOverrides($overrides);
    }

    public function hasOverride(): bool
    {
        return ! empty($this->overrides);
    }

    public function moduleHasOverride(string $module_name): bool
    {
        return isset($this->overrides[$module_name]);
    }

    public function printOverrides(ProcessType $type): void
    {
        if ($this->hasOverride()) {
            $modules = array_map(fn ($module, $status) => $module . (is_array($status) ? '(' . implode(',', $status) . ')' : ''), array_keys($this->overrides), array_values($this->overrides));

            Log::debug(sprintf('Override %s modules: %s', $type->name, implode(', ', $modules)));
        }
    }

    /**
     * @return array<string, ModuleStatus>
     */
    public function modulesWithStatus(ProcessType $type, Device $device): array
    {
        $default_modules = match ($type) {
            ProcessType::poller => LibrenmsConfig::get('poller_modules', []),
            ProcessType::discovery => LibrenmsConfig::get('discovery_modules', []),
        };

        $modules_with_overrides = empty($this->overrides)
            ? array_keys($default_modules)
            : array_keys(array_intersect_key($default_modules, $this->overrides)); // ensure order

        $module_status = [];
        foreach ($modules_with_overrides as $module_name) {
            $module_status[$module_name] = $this->moduleStatus($type, $module_name, $device);
        }

        return $module_status;
    }

    private function moduleStatus(ProcessType $type, string $module_name, Device $device): ModuleStatus
    {
        $override = $this->overrides[$module_name] ?? null;

        return match ($type) {
            ProcessType::discovery => new ModuleStatus(
                LibrenmsConfig::get("discovery_modules.$module_name"),
                LibrenmsConfig::get("os.{$device->os}.discovery_modules.$module_name"),
                $device->getAttrib("discover_$module_name"),
                $override == null ? null : true,
                is_array($override) ? $override : null,
            ),
            ProcessType::poller => new ModuleStatus(
                LibrenmsConfig::get("poller_modules.$module_name"),
                LibrenmsConfig::get("os.{$device->os}.poller_modules.$module_name"),
                $device->getAttrib("poll_$module_name"),
                $override == null ? null : true,
                is_array($override) ? $override : null,
            ),
        };
    }

    private function parseUserOverrides(array $overrides): array
    {
        $modules = [];

        foreach ($overrides as $module) {
            // parse submodules (only supported by some modules)
            if (str_contains((string) $module, '/')) {
                [$module, $submodule] = explode('/', (string) $module, 2);
                $modules[$module][] = $submodule;
            } elseif (Module::exists($module)) {
                $modules[$module] = true;
            }
        }

        return $modules;
    }
}
