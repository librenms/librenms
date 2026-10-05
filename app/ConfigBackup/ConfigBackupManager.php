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
     * Built-in providers in priority order; the first one that is configured
     * and supports the device wins.
     *
     * @var list<class-string<ConfigBackupProvider>>
     */
    public static array $providers = [
        \App\ConfigBackup\Providers\UnimusProvider::class,
        \App\ConfigBackup\Providers\OxidizedProvider::class,
        // future: RancidProvider::class
        // \App\ConfigBackup\Providers\FakeConfigBackupProvider::class,
    ];

    /**
     * Providers registered after the built-in ones, lowest priority.
     *
     * @var list<class-string<ConfigBackupProvider>>
     */
    protected static array $appended = [];

    /**
     * Providers registered before the built-in ones, highest priority.
     *
     * @var list<class-string<ConfigBackupProvider>>
     */
    protected static array $prepended = [];

    /**
     * Register an external config backup provider.
     *
     * A plugin package can call this from its service provider boot() method
     * to add a backend without a core change. The provider must implement
     * ConfigBackupProvider. Set $prepend to true to give the provider priority
     * over the built-in providers.
     *
     * @param  class-string<ConfigBackupProvider>  $provider
     */
    public static function register(string $provider, bool $prepend = false): void
    {
        if (! is_a($provider, ConfigBackupProvider::class, true)) {
            throw new \InvalidArgumentException($provider . ' must implement ' . ConfigBackupProvider::class);
        }

        if ($prepend) {
            static::$prepended[] = $provider;
        } else {
            static::$appended[] = $provider;
        }
    }

    /**
     * The effective provider list in priority order: prepended, built-in,
     * then appended. Duplicates keep their first (highest priority) position.
     *
     * @return list<class-string<ConfigBackupProvider>>
     */
    public static function providers(): array
    {
        return array_values(array_unique(array_merge(
            static::$prepended,
            static::$providers,
            static::$appended,
        )));
    }

    public function providerFor(Device $device): ?ConfigBackupProvider
    {
        foreach (static::providers() as $class) {
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

    public function handles(Device $device): bool
    {
        return $this->providerFor($device) !== null;
    }
}
