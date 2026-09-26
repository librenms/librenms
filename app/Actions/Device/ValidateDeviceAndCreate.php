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

readonly class ValidateDeviceAndCreate
{
    private BuildDefaultPollingMethods $builder;
    private ValidateDeviceUniqueness $uniqueness;
    private DiscoverDevicePollingMethods $discoverMethods;
    private DiscoverDeviceMetadata $discoverMetadata;
    private PersistDeviceWithPollingMethods $persister;

    /**
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods
     */
    public function __construct(
        private Device $device,
        private ?Collection $pollingMethods = null,
        private bool $force = false,
        private bool $ping_fallback = false,
        ?BuildDefaultPollingMethods $builder = null,
        ?ValidateDeviceUniqueness $uniqueness = null,
        ?DiscoverDevicePollingMethods $discoverMethods = null,
        ?DiscoverDeviceMetadata $discoverMetadata = null,
        ?PersistDeviceWithPollingMethods $persister = null,
    ) {
        $this->builder = $builder ?? resolve(BuildDefaultPollingMethods::class);
        $this->uniqueness = $uniqueness ?? resolve(ValidateDeviceUniqueness::class);
        $this->discoverMethods = $discoverMethods ?? resolve(DiscoverDevicePollingMethods::class);
        $this->discoverMetadata = $discoverMetadata ?? resolve(DiscoverDeviceMetadata::class);
        $this->persister = $persister ?? resolve(PersistDeviceWithPollingMethods::class);
    }

    /**
     * @return bool
     *
     * @throws \LibreNMS\Exceptions\HostExistsException
     * @throws \LibreNMS\Exceptions\HostUnreachableException
     * @throws \LibreNMS\Exceptions\SnmpVersionUnsupportedException
     */
    public function execute(): bool
    {
        if ($this->device->exists) {
            return false;
        }

        $this->uniqueness->validateHostname((string) $this->device->hostname);
        $this->fillDefaults();

        $pollingMethods = $this->pollingMethods ?? $this->builder->execute($this->device);

        if (! $this->force) {
            $this->uniqueness->validateIp($this->device);

            $pollingMethods = $this->discoverMethods->execute(
                $this->device,
                $pollingMethods,
                $this->ping_fallback
            );

            $this->device->setRelation('pollingMethods', $pollingMethods);

            $this->discoverMetadata->execute($this->device, $pollingMethods);
        }

        // The OS is detected via SNMP, without it the device is ping only
        $hasSnmp = $pollingMethods->contains(fn (DevicePollingMethod $m) => $m->method_type === PollingMethodType::Snmp && $m->enabled);
        if (! $hasSnmp && $this->device->os === 'generic') {
            $this->device->os = 'ping';
        }

        return $this->persister->execute($this->device, $pollingMethods);
    }

    private function fillDefaults(): void
    {
        $this->device->poller_group = $this->device->poller_group ?: LibrenmsConfig::get('default_poller_group', 0);
        $this->device->os = $this->device->os ?: 'generic';
        $this->device->status_reason = '';
        $this->device->sysName = $this->device->sysName ?: $this->device->hostname;
    }
}
