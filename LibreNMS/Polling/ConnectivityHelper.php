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

use App\Models\Device;
use App\Models\DeviceOutage;
use App\Models\Eventlog;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;
use LibreNMS\Enum\Severity;
use LibreNMS\RRD\RrdDefinition;
use SnmpQuery;
use Symfony\Component\Process\Process;

class ConnectivityHelper
{
    /**
     * @var \App\Models\Device
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
        $previous = $this->device->status;
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
                $this->savePingStats($ping_response);
            }
            $this->updateAvailability($previous, $this->device->status);

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
            return FpingResponse::artificialUp();
        }

        $status = app()->make(Fping::class)->ping(
            $this->target,
            Config::get('fping_options.count', 3),
            Config::get('fping_options.interval', 500),
            Config::get('fping_options.timeout', 500),
            $this->ipFamily()
        );

        if ($status->duplicates > 0) {
            Eventlog::log('Duplicate ICMP response detected! This could indicate a network issue.', $this->device, 'icmp', Severity::Warning);
            $status->exit_code = 0;   // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
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
        $command = [Config::get('traceroute', 'traceroute'), '-q', '1', '-w', '1', '-I', $this->target];
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
        return Config::get('icmp_check') && ! ($this->device->exists && $this->device->getAttrib('override_icmp_disable') === 'true');
    }

    public function ipFamily(): string
    {
        if ($this->family === null) {
            $this->family = preg_match('/6$/', $this->device->transport ?? '') ? 'ipv6' : 'ipv4';
        }

        return $this->family;
    }

    private function updateAvailability(bool $previous, bool $status): void
    {
        if (Config::get('graphing.availability_consider_maintenance') && $this->device->isUnderMaintenance()) {
            return;
        }

        // check for open outage
        $open_outage = $this->device->getCurrentOutage();

        if ($status) {
            if ($open_outage) {
                $open_outage->up_again = time();
                $open_outage->save();
            }
        } elseif ($previous || $open_outage === null) {
            // status changed from up to down or there is no open outage
            // open new outage
            $this->device->outages()->save(new DeviceOutage(['going_down' => time()]));
        }
    }

    /**
     * Save the ping stats to db and rrd, also updates last_ping_timetaken and saves the device model.
     */
    private function savePingStats(FpingResponse $ping_response): void
    {
        $perf = $ping_response->toModel();
        $perf->debug = ['poller_name' => Config::get('distributed_poller_name')];
        if (! $ping_response->success() && Config::get('debug.run_trace', false)) {
            $perf->debug = array_merge($perf->debug, $this->traceroute());
        }
        $this->device->perf()->save($perf);
        $this->device->last_ping = Carbon::now();
        $this->device->last_ping_timetaken = $ping_response->avg_latency ?: $this->device->last_ping_timetaken;
        $this->device->save();

        app('Datastore')->put($this->device->toArray(), 'ping-perf', [
            'rrd_def' => RrdDefinition::make()->addDataset('ping', 'GAUGE', 0, 65535),
        ], [
            'ping' => $ping_response->avg_latency,
        ]);
    }
}
