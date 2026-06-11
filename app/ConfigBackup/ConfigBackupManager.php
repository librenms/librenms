<?php

/**
 * ConfigBackupManager.php
 *
 * Resolves which config backup provider serves a given device.
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
 * @copyright  2026 LibreNMS
 */

namespace App\ConfigBackup;

use App\Models\Device;
use LibreNMS\Interfaces\ConfigBackupProvider;

class ConfigBackupManager
{
    /**
     * Providers in priority order; the first one that is configured and
     * supports the device wins.
     *
     * @var list<class-string<ConfigBackupProvider>>
     */
    public static array $providers = [
        \App\ConfigBackup\Providers\UnimusProvider::class,
        // future: OxidizedProvider::class, RancidProvider::class
    ];

    /** @var array<int, ConfigBackupProvider|null> */
    private array $resolved = [];

    public function providerFor(Device $device): ?ConfigBackupProvider
    {
        if (! array_key_exists($device->device_id, $this->resolved)) {
            $this->resolved[$device->device_id] = $this->resolve($device);
        }

        return $this->resolved[$device->device_id];
    }

    public function handles(Device $device): bool
    {
        return $this->providerFor($device) !== null;
    }

    private function resolve(Device $device): ?ConfigBackupProvider
    {
        foreach (self::$providers as $class) {
            if (! $class::isConfigured()) {
                continue;
            }

            $provider = app($class);
            if ($provider->supportsDevice($device)) {
                return $provider;
            }
        }

        return null;
    }
}
