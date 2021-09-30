<?php
/*
 * PollerHelper.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Polling;

use App\Models\Device;
use LibreNMS\Config;
use Log;
use Symfony\Component\Process\Process;

/**
 * @property string $family
 */
class PollerHelper
{
    /**
     * @var \App\Models\Device
     */
    private $device;
    /**
     * @var bool
     */
    private $savePingPerf = false;

    public function __construct(Device $device)
    {
        $this->device = $device;
        $this->target = $device->overwrite_ip ?: $device->hostname;
    }

    public function savePingPerf()
    {
        $this->savePingPerf = true;
    }

    public function isUp(): bool
    {
        $poller_target = Device::pollerTarget($device['hostname']);
        $ping_response = isPingable($poller_target, $address_family, $device['attribs']);
        $deviceModel = DeviceCache::get($device['device_id']);
        $deviceModel->perf()->save($ping_response->toModel());
        $device_perf = $ping_response['db'];
        $device_perf['device_id'] = $device['device_id'];
        $device_perf['timestamp'] = ['NOW()'];
        $consider_maintenance = Config::get('graphing.availability_consider_maintenance');
        $state_update_again = false;

        if ($this->savePingPerf && $this->canPing()) {
            $perf = $ping_response->toModel();
            if (! $ping_response->success() && Config::get('debug.run_trace', false)) {
                $perf->debug = $this->traceroute();
            }
            $this->device->perf()->save($perf);
            $this->device->last_ping_timetaken = $ping_response->avg_latency ?: $this->device->last_ping_timetaken;
            $this->device->save();
        }

        $response = [];
        $response['ping_time'] = $ping_response['last_ping_timetaken'];
        if ($ping_response['result']) {
            if ($this->device->snmp_disable || isSNMPable($device)) {
                $response['status'] = '1';
                $response['status_reason'] = '';
            } else {
                echo 'SNMP Unreachable';
                $response['status'] = '0';
                $response['status_reason'] = 'snmp';
            }
        } else {
            echo 'Unpingable';
            $response['status'] = '0';
            $response['status_reason'] = 'icmp';
        }

        // Special case where the device is still down, optional mode is on, device not in maintenance mode and has no ongoing outages
        if (($consider_maintenance && ! ($deviceModel->isUnderMaintenance())) && ($device['status'] == '0' && $response['status'] == '0')) {
            $state_update_again = empty(dbFetchCell('SELECT going_down FROM device_outages WHERE device_id=? AND up_again IS NULL ORDER BY going_down DESC', [$device['device_id']]));
        }

        if ($device['status'] != $response['status'] || $device['status_reason'] != $response['status_reason'] || $state_update_again) {
            if (! $state_update_again) {
                dbUpdate(
                    ['status' => $response['status'], 'status_reason' => $response['status_reason']],
                    'devices',
                    'device_id=?',
                    [$device['device_id']]
                );
            }

            if ($response['status']) {
                $type = 'up';
                $reason = $device['status_reason'];

                $going_down = dbFetchCell('SELECT going_down FROM device_outages WHERE device_id=? AND up_again IS NULL ORDER BY going_down DESC', [$device['device_id']]);
                if (! empty($going_down)) {
                    $up_again = time();
                    dbUpdate(
                        ['device_id' => $device['device_id'], 'up_again' => $up_again],
                        'device_outages',
                        'device_id=? and going_down=? and up_again is NULL',
                        [$device['device_id'], $going_down]
                    );
                }
            } else {
                $type = 'down';
                $reason = $response['status_reason'];

                if ($device['status'] != $response['status']) {
                    if (! $consider_maintenance || (! ($deviceModel->isUnderMaintenance()) && $consider_maintenance)) {
                        // use current time as a starting point when an outage starts
                        $data = ['device_id' => $device['device_id'],
                            'going_down' => time(), ];
                        dbInsert($data, 'device_outages');
                    }
                }
            }

            log_event('Device status changed to ' . ucfirst($type) . " from $reason check.", $device, $type);
        }

        return $response;

    }

    /**
     * Check if the device responds to ICMP echo requests ("pings").
     */
    public function isPingable(): FpingResponse
    {
        if (! $this->canPing()) {
            return \LibreNMS\Polling\FpingResponse::artificialUp();
        }

        $status = app()->make(Fping::class)->ping(
            $this->target,
            Config::get('fping_options.count', 3),
            Config::get('fping_options.interval', 500),
            Config::get('fping_options.timeout', 500),
            $this->ipFamily()
        );

        if ($status->duplicates > 0) {
            Log::event('Duplicate ICMP response detected! This could indicate a network issue.', $this->device, 'icmp', 4);
            $status->exit_code = 0;   // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
        }

        return $status;
    }

    public function isSNMPable()
    {
        $pos = snmp_check($device);
        if ($pos === true) {
            return true;
        } else {
            $pos = snmp_get($device, 'sysObjectID.0', '-Oqv', 'SNMPv2-MIB');
            if ($pos === '' || $pos === false) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function traceroute()
    {
        $command = [Config::get('traceroute', 'traceroute'), '-q', '1', '-w', '1', $this->target];
        if ($this->ipFamily() == 'ipv6') {
            $command[] = '-6';
        }

        $process = new Process($command);
        $process->run();
        return [
            'traceroute' => $process->getOutput(),
            'output' => $process->getErrorOutput(),
        ];
    }

    public function canPing()
    {
        return Config::get('icmp_check') && ! ($this->device->exists && $this->device->getAttrib('override_icmp_disable') === 'true');
    }

    public function ipFamily(): string
    {
        if ($this->family === null) {
            $this->family = preg_match('/6$/', $this->device->transport) ? 'ipv6' : 'ipv4';
        }

        return $this->family;
    }
}
