<?php

/*
 * ValidateDeviceAndCreate.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Actions\Device;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\PollingMethodRegistry;

readonly class ValidateDeviceAndCreate
{
    public function __construct(
        private BuildDefaultPollingMethods $builder,
        private ValidateDeviceUniqueness $uniqueness,
        private DiscoverDevicePollingMethods $discoverMethods,
        private DiscoverDeviceMetadata $discoverMetadata,
        private PersistDeviceWithPollingMethods $persister,
        private PollingMethodRegistry $registry,
    ) {
    }

    /**
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods  null for the default methods
     * @param  bool  $force  skip all reachability and duplicate checks, a duplicate hostname is always rejected
     * @param  PollingMethodType[]  $uncheckedMethods  methods to save without checking
     *
     * @throws \LibreNMS\Exceptions\HostExistsException
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws \LibreNMS\Exceptions\SnmpVersionUnsupportedException
     * @throws \LibreNMS\Exceptions\MissingSecretException
     */
    public function execute(
        Device $device,
        ?Collection $pollingMethods = null,
        bool $force = false,
        bool $pingFallback = false,
        array $uncheckedMethods = [],
    ): bool {
        if ($device->exists) {
            return false;
        }

        $this->uniqueness->validateHostname((string) $device->hostname);
        $this->fillDefaults($device);

        $pollingMethods ??= $this->builder->execute($device);

        if (! $force) {
            $this->uniqueness->validateIp($device);

            [$unchecked, $toCheck] = $pollingMethods->partition(
                fn (DevicePollingMethod $m): bool => in_array($m->method_type, $uncheckedMethods, true)
            );
            $pollingMethods = $this->discoverMethods->execute($device, $toCheck, $pingFallback)
                ->concat($unchecked)
                ->values();

            $device->setRelation('pollingMethods', $pollingMethods);

            $this->discoverMetadata->execute($device, $pollingMethods);
        }

        // after the sysName check, only a sysName that was given or read from the device is checked
        $device->sysName = $device->sysName ?: $device->hostname;

        // methods that were not checked have not found credentials
        foreach ($pollingMethods as $deviceMethod) {
            $this->registry->get($deviceMethod->method_type)->assignDefaultSecret($deviceMethod);
        }

        // The OS is detected via SNMP, without it the device is ping only
        $hasSnmp = $pollingMethods->contains(fn (DevicePollingMethod $m) => $m->method_type === PollingMethodType::Snmp && $m->enabled);
        if (! $hasSnmp && $device->os === 'generic') {
            $device->os = 'ping';
        }

        return $this->persister->execute($device, $pollingMethods);
    }

    private function fillDefaults(Device $device): void
    {
        $device->poller_group = $device->poller_group ?: LibrenmsConfig::get('default_poller_group', 0);
        $device->os = $device->os ?: 'generic';
        $device->status_reason = '';
    }
}
