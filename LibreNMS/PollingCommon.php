<?php
/*
 * PollingCommon.php
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

namespace LibreNMS;

use App\Models\Device;

class PollingCommon
{

    protected function pingDevice(Device $device): FpingResponse
    {

    }


    protected function deviceIsUp(Device $device)
    {
        $address_family = snmpTransportToAddressFamily($device['transport']);
        $poller_target = Device::pollerTarget($device['hostname']);
        $ping_response = isPingable($poller_target, $address_family, $device['attribs']);
        $device_perf = $ping_response['db'];
        $device_perf['device_id'] = $device['device_id'];
        $device_perf['timestamp'] = ['NOW()'];
        $maintenance = $device->isUnderMaintenance();
        $consider_maintenance = Config::get('graphing.availability_consider_maintenance');
        $state_update_again = false;

        if ($record_perf === true && can_ping_device($device['attribs'])) {
            $trace_debug = [];
            if ($ping_response['result'] === false && Config::get('debug.run_trace', false)) {
                $trace_debug = runTraceroute($device);
            }
            $device_perf['debug'] = json_encode($trace_debug);
            dbInsert($device_perf, 'device_perf');

            // if device_perf is inserted and the ping was successful then update device last_ping timestamp
            if (! empty($ping_response['last_ping_timetaken']) && $ping_response['last_ping_timetaken'] != '0') {
                dbUpdate(
                    ['last_ping' => NOW(), 'last_ping_timetaken' => $ping_response['last_ping_timetaken']],
                    'devices',
                    'device_id=?',
                    [$device['device_id']]
                );
            }
        }
        $response = [];
        $response['ping_time'] = $ping_response['last_ping_timetaken'];
        if ($ping_response['result']) {
            if ($device['snmp_disable'] || isSNMPable($device)) {
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
        if (($consider_maintenance && ! $maintenance) && ($device['status'] == '0' && $response['status'] == '0')) {
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
                    if (! $consider_maintenance || (! $maintenance && $consider_maintenance)) {
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
     * Check if the given host responds to ICMP echo requests ("pings").
     *
     * @param  string  $hostname  The hostname or IP address to send ping requests to.
     * @param  string  $address_family  The address family ('ipv4' or 'ipv6') to use. Defaults to IPv4.
     *                                  Will *not* be autodetected for IP addresses, so it has to be set to 'ipv6' when pinging an IPv6 address or an IPv6-only host.
     * @return array 'result' => bool pingable, 'last_ping_timetaken' => int time for last ping, 'db' => fping results
     */
    private function isPingable(string $hostname, string $address_family = 'ipv4')
    {
        $device = \DeviceCache::getByHostname($hostname);
        if ($device->getAttrib() !== true) {
            return [
                'result' => true,
                'last_ping_timetaken' => 0,
            ];
        }

        $status = app()->make(Fping::class)->ping(
            $hostname,
            Config::get('fping_options.count', 3),
            Config::get('fping_options.interval', 500),
            Config::get('fping_options.timeout', 500),
            $address_family
        );

        if ($status['dup'] > 0) {
            Log::event('Duplicate ICMP response detected! This could indicate a network issue.', getidbyname($hostname), 'icmp', 4);
            $status['exitcode'] = 0;   // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
        }

        return [
            'result' => ($status['exitcode'] == 0 && $status['loss'] < 100),
            'last_ping_timetaken' => $status['avg'],
            'db' => array_intersect_key($status, array_flip(['xmt', 'rcv', 'loss', 'min', 'max', 'avg'])),
        ];
    }
}
