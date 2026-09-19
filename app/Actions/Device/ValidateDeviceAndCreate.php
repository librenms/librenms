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

class ValidateDeviceAndCreate
{
    /**
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods
     */
    public function __construct(
        private readonly Device $device,
        private readonly ?Collection $pollingMethods = null,
        private readonly bool $force = false,
        private readonly bool $ping_fallback = false,
        private readonly BuildDefaultPollingMethods $builder = new BuildDefaultPollingMethods,
        private readonly ValidateDeviceUniqueness $uniqueness = new ValidateDeviceUniqueness,
        private readonly DiscoverDevicePollingMethods $discoverMethods = new DiscoverDevicePollingMethods,
        private readonly DiscoverDeviceMetadata $discoverMetadata = new DiscoverDeviceMetadata,
        private readonly PersistDeviceWithPollingMethods $persister = new PersistDeviceWithPollingMethods,
    ) {
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

            $this->discoverMetadata->execute($this->device, $pollingMethods);
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
