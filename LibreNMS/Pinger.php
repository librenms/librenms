<?php
/**
 * Pinger.php
 *
 * Ping a large amount of devices quickly and update the db, ping-perf, and run alerts
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use App\Models\Device;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LibreNMS\RRD\RrdDefinition;
use Symfony\Component\Process\Process;

class Pinger
{
    private $process;
    private $rrd_tags;

    /** @var \Illuminate\Database\Eloquent\Collection $devices List of devices keyed by hostname */
    private $devices;

    // working data for loop
    private $tiered;
    private $current;
    private $current_tier;
    private $deferred;

    public function __construct($groups = [])
    {
        global $vdebug;

        // define rrd tags
        $rrd_step = Config::get('ping_rrd_step', Config::get('rrd.step', 300));
        $rrd_def = RrdDefinition::make()->addDataset('ping', 'GAUGE', 0, 65535, $rrd_step * 2);
        $this->rrd_tags = ['rrd_def' => $rrd_def, 'rrd_step' => $rrd_step];

        // set up fping process
        $timeout = Config::get('fping_options.timeout', 500); // must be smaller than period
        $retries = Config::get('fping_options.retries', 2);  // how many retries on failure

        $cmd = ['fping', '-f', '-', '-e', '-t', $timeout, '-r', $retries];

        $wait = Config::get('rrd_step', 300) * 2;

        $this->process = new Process($cmd, null, null, null, $wait);

        // fetch devices
        /** @var Builder $query */
        $query = Device::canPing()
            ->select(['devices.device_id', 'hostname', 'status', 'status_reason', 'last_ping', 'last_ping_timetaken', 'max_depth'])
            ->orderBy('max_depth');

        if ($groups) {
            $query->whereIn('poller_group', $groups);
        }

        $this->devices = $query->get()->keyBy('hostname');

        // working collections
        $this->tiered = $this->devices->groupBy('max_depth', true)->map->keys();
        $this->deferred = collect();

        // start with tier 1 (the root nodes, 0 is standalone)
        $this->current_tier = 1;
        $this->current = $this->tiered->get($this->current_tier);

        if ($vdebug) {
            $this->tiered->each(function (Collection $tier, $index) {
                echo "Tier $index (" . $tier->count() . "): ";
                echo $tier->implode('hostname', ', ');
                echo PHP_EOL;
            });
        }
    }

    public function start()
    {
        d_echo($this->process->getCommandLine() . PHP_EOL);

        // send hostnames to stdin to avoid overflowing cli length limits
        $this->process->setInput($this->devices->keys()->implode(PHP_EOL));
        $this->process->start(); // start as early as possible

        foreach ($this->process as $type => $line) {
            d_echo($line);

            if (Process::ERR === $type) {
                // Check for devices we couldn't resolve dns for
                if (preg_match('/^(?<hostname>[^\s]+): (?:Name or service not known|Temporary failure in name resolution)/', $line, $errored)) {
                    $this->recordData([
                        'hostname' => $errored['hostname'],
                        'status' => 'unreachable'
                    ]);
                }
                continue;
            }

            if (preg_match(
                '/^(?<hostname>[^\s]+) is (?<status>alive|unreachable)(?: \((?<rtt>[\d.]+) ms\))?/',
                $line,
                $captured
            )) {
                $this->recordData($captured);

                $this->processTier();
            }
        }

        // check for any left over devices
        if ($this->deferred->isNotEmpty()) {
            d_echo("Leftover devices, this shouldn't happen: " . $this->deferred->flatten(1)->implode('hostname', ', ') . PHP_EOL);
            d_echo("Devices left in tier: " . collect($this->current)->implode(', ') . PHP_EOL);
        }
    }

    /**
     * Check if this tier is complete and move to the next tier
     * If we moved to the next tier, check if we can report any of our deferred results
     */
    private function processTier()
    {
        global $vdebug;

        if ($this->current->isNotEmpty()) {
            return;
        }

        $this->current_tier++;  // next tier

        if (!$this->tiered->has($this->current_tier)) {
            // out of devices
            return;
        }

        if ($vdebug) {
            echo "Out of devices at this tier, moving to tier $this->current_tier\n";
        }

        $this->current = $this->tiered->get($this->current_tier);

        // update and remove devices in the current tier
        foreach ($this->deferred->pull($this->current_tier) as $data) {
            $this->recordData($data);
        }

        // try to process the new tier in case we took care of all the devices
        $this->processTier();
    }

    /**
     * If the device is on the current tier, record the data and remove it
     * $data should have keys: hostname, status, and conditionally rtt
     *
     * @param $data
     */
    private function recordData($data)
    {
        global $vdebug;

        if ($vdebug) {
            echo "Attempting to record data for {$data['hostname']}... ";
        }

        /** @var Device $device */
        $device = $this->devices->get($data['hostname']);

        // process the data if this is a standalone device or in the current tier
        if ($device->max_depth === 0 || $this->current->contains($device->hostname)) {
            if ($vdebug) {
                echo "Success\n";
            }

            // mark up only if snmp is not down too
            $device->status = ($data['status'] == 'alive' && $device->status_reason != 'snmp');
            $device->last_ping = Carbon::now();
            $device->last_ping_timetaken = isset($data['rtt']) ? $data['rtt'] : 0;

            if ($device->isDirty('status')) {
                // if changed, update reason
                $device->status_reason = $device->status ? '' : 'icmp';
                $type = $device->status ? 'up' : 'down';
                log_event('Device status changed to ' . ucfirst($type) . " from icmp check.", $device->toArray(), $type);

                echo "Device $device->hostname changed status to $type, running alerts\n";
                RunRules($device->device_id);
            }
            $device->save(); // only saves if needed (which is every time because of last_ping)

            // add data to rrd
            data_update($device->toArray(), 'ping-perf', $this->rrd_tags, ['ping' => $device->last_ping_timetaken]);

            // done with this device
            $this->complete($device->hostname);
            d_echo("Recorded data for $device->hostname (tier $device->max_depth)\n");
        } else {
            if ($vdebug) {
                echo "Deferred\n";
            }

            $this->defer($data);
        }
    }

    /**
     * Done processing $hostname, remove it from our active data
     *
     * @param $hostname
     */
    private function complete($hostname)
    {
        $this->current->offsetUnset($hostname);
        $this->deferred->each->offsetUnset($hostname);
    }

    /**
     * Defer this data processing until all parent devices are complete
     *
     *
     * @param $data
     */
    private function defer($data)
    {
        $device = $this->devices->get($data['hostname']);

        if ($this->deferred->has($device->max_depth)) {
            // add this data to the proper tier, unless it already exists...
            $tier = $this->deferred->get($device->max_depth);
            if (!$tier->has($device->hostname)) {
                $tier->put($device->hostname, $data);
            }
        } else {
            // create a new tier containing this data
            $this->deferred->put($device->max_depth, collect([$device->hostname => $data]));
        }


    }

    public function count()
    {
        return $this->devices->count();
    }
}
