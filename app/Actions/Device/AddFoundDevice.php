<?php

/**
 * AddFoundDevice.php
 *
 * Add a device that was found by a network scan or through another device.
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

namespace App\Actions\Device;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Exception;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostUnreachableException;

readonly class AddFoundDevice
{
    private const LOCK_SECONDS = 300; // longer than adding a device takes

    public function __construct(
        private ValidateDeviceAndCreate $create,
    ) {
    }

    /**
     * Add the device, the same device found by several sources at once is only added once.
     *
     * @param  Device  $device  a new device, it is saved when it is added
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods  null for the default methods
     * @param  PollingMethodType[]  $uncheckedMethods  methods that are already known to work
     */
    public function execute(
        Device $device,
        ?Collection $pollingMethods = null,
        array $uncheckedMethods = [],
    ): AddFoundDeviceResult {
        try {
            return Cache::lock('add-device:' . strtolower((string) $device->hostname), self::LOCK_SECONDS)
                ->block(self::LOCK_SECONDS, fn () => $this->create($device, $pollingMethods, $uncheckedMethods));
        } catch (LockTimeoutException) {
            return new AddFoundDeviceResult(DeviceAddOutcome::Error, "Timed out waiting for another add of $device->hostname");
        }
    }

    /**
     * @param  Collection<int, DevicePollingMethod>|null  $pollingMethods
     * @param  PollingMethodType[]  $uncheckedMethods
     */
    private function create(Device $device, ?Collection $pollingMethods, array $uncheckedMethods): AddFoundDeviceResult
    {
        try {
            if ($this->create->execute($device, $pollingMethods, uncheckedMethods: $uncheckedMethods)) {
                return new AddFoundDeviceResult(DeviceAddOutcome::Added, trans('commands.device:add.messages.added', ['hostname' => $device->hostname, 'device_id' => $device->device_id]));
            }

            return new AddFoundDeviceResult(DeviceAddOutcome::Error, trans('commands.device:add.messages.save_failed', ['hostname' => $device->hostname]));
        } catch (HostExistsException $e) {
            return new AddFoundDeviceResult(DeviceAddOutcome::Known, $e->getMessage());
        } catch (HostUnreachableException $e) {
            return new AddFoundDeviceResult(
                $e->method === PollingMethodType::Icmp ? DeviceAddOutcome::Unpingable : DeviceAddOutcome::Unreachable,
                implode(PHP_EOL, [$e->getMessage(), ...$e->getReasons()]),
            );
        } catch (Exception $e) {
            return new AddFoundDeviceResult(DeviceAddOutcome::Error, $e->getMessage());
        }
    }
}
