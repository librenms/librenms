<?php

/*
 * ConnectivityHelper.php
 *
 * Helper to check the connectivity to a device and optionally save metrics about that connectivity
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

namespace LibreNMS\Polling;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DeviceOutage;
use App\Models\Eventlog;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;
use LibreNMS\Enum\MaintenanceStatus;
use LibreNMS\Enum\Severity;
use SnmpQuery;
use Symfony\Component\Process\Process;

class ConnectivityHelper
{
    /**
     * @var Device
     */
    private $device;
    /**
     * @var bool
     */
    private $saveMetrics = false;
    /**
     * @var string
     */
    private $family;
    /**
     * @var string
     */
    private $target;

    public function __construct(Device $device)
    {
        $this->device = $device;
        $this->target = $device->overwrite_ip ?: $device->hostname;
    }

    /**
     * After pinging the device, save metrics about the ping response
     */
    public function saveMetrics(): void
    {
        $this->saveMetrics = true;
    }

    /**
     * Check if the device is up.
     * Save availability and ping data if enabled with savePingPerf()
     */
    public function isUp(): bool
    {
        $ping_response = $this->isPingable();

        // calculate device status
        if ($ping_response->success()) {
            if (! $this->canSnmp() || $this->isSNMPable()) {
                // up
                $this->device->status = true;
                $this->device->status_reason = '';
            } else {
                // snmp down
                $this->device->status = false;
                $this->device->status_reason = 'snmp';
            }
        } else {
            // icmp down
            $this->device->status = false;
            $this->device->status_reason = 'icmp';
        }

        if ($this->saveMetrics) {
            if ($this->canPing()) {
                $ping_response->saveStats($this->device);
            }
            $this->updateAvailability($this->device->status);

            $this->device->save(); // confirm device is saved
        }

        return $this->device->status;
    }

    /**
     * Check if the device responds to ICMP echo requests ("pings").
     */
    public function isPingable(): FpingResponse
    {
        if (! $this->canPing()) {
            return FpingResponse::artificialUp($this->target);
        }

        $status = app()->make(Fping::class)->ping($this->target, $this->ipFamily());

        if ($status->duplicates > 0) {
            Eventlog::log('Duplicate ICMP response detected! This could indicate a network issue.', $this->device, 'icmp', Severity::Warning);
            $status->ignoreFailure(); // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
        }

        return $status;
    }

    public function isSNMPable(): bool
    {
        $response = SnmpQuery::device($this->device)->get('SNMPv2-MIB::sysObjectID.0');

        return $response->getExitCode() === 0 || $response->isValid();
    }

    public function traceroute(): array
    {
        $command = [LibrenmsConfig::get('traceroute', 'traceroute'), '-q', '1', '-w', '1', '-I', $this->target];
        if ($this->ipFamily() == 'ipv6') {
            $command[] = '-6';
        }

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        return [
            'traceroute' => $process->getOutput(),
            'traceroute_output' => $process->getErrorOutput(),
        ];
    }

    public function canSnmp(): bool
    {
        return ! $this->device->snmp_disable;
    }

    public function canPing(): bool
    {
        return LibrenmsConfig::get('icmp_check') && ! ($this->device->exists && $this->device->getAttrib('override_icmp_disable') === 'true');
    }

    public function ipFamily(): string
    {
        if ($this->family === null) {
            $this->family = preg_match('/6$/', $this->device->transport ?? '') ? 'ipv6' : 'ipv4';
        }

        return $this->family;
    }

    private function updateAvailability(bool $current_status): void
    {
        // skip update if we are considering maintenance
        if (LibrenmsConfig::get('graphing.availability_consider_maintenance')
            && $this->device->getMaintenanceStatus() !== MaintenanceStatus::NONE) {
            return;
        }

        if ($current_status) {
            // Device is up, close any open outages
            $this->device->outages()->whereNull('up_again')->get()->each(function (DeviceOutage $outage) {
                $outage->up_again = time();
                $outage->save();
            });

            return;
        }

        // Device is down, only open a new outage if none is currently open
        if ($this->device->getCurrentOutage() === null) {
            $this->device->outages()->save(new DeviceOutage(['going_down' => time()]));
        }
    }
}
