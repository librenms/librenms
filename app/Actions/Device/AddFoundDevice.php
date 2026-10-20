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
use Exception;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostUnreachableException;

readonly class AddFoundDevice
{
    public function __construct(
        private BuildDefaultPollingMethods $builder,
        private ValidateDeviceAndCreate $create,
    ) {
    }

    /**
     * Add the device with the default polling methods.
     *
     * @param  Device  $device  a new device, it is saved when it is added
     * @param  bool  $pingFallback  add the device as ping only if it does not reply to SNMP
     * @param  bool  $pingOnly  add the device as ping only
     */
    public function execute(Device $device, bool $pingFallback = false, bool $pingOnly = false): AddFoundDeviceResult
    {
        $pollingMethods = $pingOnly
            ? $this->builder->execute($device, ['methods' => [PollingMethodType::Icmp->value => ['active' => true]]])
            : null;

        try {
            if ($this->create->execute($device, $pollingMethods, pingFallback: $pingFallback)) {
                return new AddFoundDeviceResult(DeviceAddOutcome::Added);
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
