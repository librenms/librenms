<?php
/**
 * PingCheck.php
 *
 * Device up/down icmp check job
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Jobs;

use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;
use LibreNMS\Util\Debug;

class PingCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Illuminate\Database\Eloquent\Collection<string, Device>|null List of devices keyed by hostname */
    private $devices;
    /** @var array List of device group ids to check */
    private $groups = [];

    // working data for loop
    /** @var Collection */
    private $deferred;
    /** @var Collection */
    private $waiting_on;
    /** @var Collection */
    private $processed;

    /**
     * Create a new job instance.
     *
     * @param  array  $groups  List of distributed poller groups to check
     */
    public function __construct($groups = [])
    {
        if (is_array($groups)) {
            $this->groups = $groups;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $ping_start = microtime(true);

        $ordered_hostname_list = $this->fetchOrderedHostnames();

        if (Debug::isVerbose()) {
            echo 'Processing hosts in this order : ' . implode(', ', $ordered_hostname_list) . PHP_EOL;
        }

        // bulk ping and send FpingResponse's to recordData as they come in
        app()->make(Fping::class)->bulkPing($ordered_hostname_list, [$this, 'handleResponse']);

        // check for any left over devices
        if ($this->deferred->isNotEmpty()) {
            d_echo("Leftover deferred devices, this shouldn't happen: " . $this->deferred->keys()->implode(', ') . PHP_EOL);
        }

        if ($this->waiting_on->isNotEmpty()) {
            d_echo("Leftover waiting on devices, this shouldn't happen: " . $this->waiting_on->keys()->implode(', ') . PHP_EOL);
        }

        if (\App::runningInConsole()) {
            printf("Pinged %s devices in %.2fs\n", $this->devices->count(), microtime(true) - $ping_start);
        }
    }

    /**
     * Get an ordered list of hostnames that we need to ping starting from devices with no parents
     */
    private function fetchOrderedHostnames(): array
    {
        $ret = [];
        $current_hostnames = [];
        $deferred_device_ids = new Collection();

        $device_list = $this->fetchDevices();

        foreach ($device_list as $hostname => $device) {
            if ($device->parents->count() === 0) {
                $current_hostnames[] = $hostname;
            } else {
                $deferred_device_ids->put($device->device_id, $hostname);
            }
        }

        while (count($current_hostnames) > 0) {
            if (Debug::isVerbose()) {
                echo 'Adding hosts to ordered list : ' . implode(', ', $current_hostnames) . PHP_EOL;
            }

            $ret = array_merge($ret, $current_hostnames);

            $current_hostnames = $this->getUnprocessedChildren($current_hostnames, $deferred_device_ids);
        }

        if (count($deferred_device_ids) > 0) {
            if (Debug::isVerbose()) {
                echo 'Adding unprocessed deferred hosts to ordered list : ' . $deferred_device_ids->values()->implode(', ') . PHP_EOL;
            }

            $ret = array_merge($ret, $deferred_device_ids->values()->toArray());
        }

        return $ret;
    }

    /**
     * For each hostname given, get the device and return any unprocessed child hostnames, removing from the unprocessed list
     */
    private function getUnprocessedChildren(array $hostnames, Collection &$unprocessed): array
    {
        $ret = [];

        foreach ($hostnames as $hostname) {
            $device = $this->devices->get($hostname);
            foreach ($device->children as $child) {
                if ($unprocessed->has($child->device_id)) {
                    $child_id = $unprocessed->pull($child->device_id);
                    $ret[] = $child_id;
                }
            }
        }

        return $ret;
    }

    /**
     * Fetch and cache all devices that we need to process
     */
    private function fetchDevices()
    {
        if (isset($this->devices)) {
            return $this->devices;
        }

        $query = Device::canPing()
            ->select(['devices.device_id', 'hostname', 'overwrite_ip', 'status', 'status_reason', 'last_ping', 'last_ping_timetaken'])
            ->with([
                'parents' => function ($q) {
                    $q->canPing()->select('devices.device_id');
                },
                'children' => function ($q) {
                    $q->canPing()->select('devices.device_id');
                },
            ])
            ->withCount('parents');

        if ($this->groups) {
            $query->whereIntegerInRaw('poller_group', $this->groups);
        }

        $this->devices = $query->get()->keyBy(function ($device) {
            return $device->overwrite_ip ?: $device->hostname;
        });

        // working collections
        $this->deferred = new Collection();
        $this->waiting_on = new Collection();
        $this->processed = new Collection();

        return $this->devices;
    }

    /**
     * Record the data and run alerts if all parents have been processed
     */
    public function handleResponse(FpingResponse $response): void
    {
        if (Debug::isVerbose()) {
            echo "Attempting to record data for $response->host... \n";
        }

        $device = $this->devices->get($response->host);

        $waiting_on = [];
        foreach ($device->parents as $parent) {
            if (! $this->processed->has($parent->device_id)) {
                $waiting_on[] = $parent->device_id;
            }
        }

        // mark up only if snmp is not down too
        $device->status = ($response->success() && $device->status_reason != 'snmp');
        if ($device->isDirty('status')) {
            // if changed, update reason
            $device->status_reason = $device->status ? '' : 'icmp';
            $type = $device->status ? 'up' : 'down';
        }

        // save last_ping_timetaken and rrd data
        $response->saveStats($device);

        // mark as processed
        $this->processed->put($device->device_id, true);
        d_echo("Recorded data for $device->hostname\n");

        if (isset($type)) { // only run alert rules if status changed
            if (Debug::isEnabled()) {
                echo "Device $device->hostname changed status to $type, running alerts\n";
            }
            if (count($waiting_on) === 0) {
                if (Debug::isVerbose()) {
                    echo "Success\n";
                }

                $this->runAlerts($device->device_id);
            } else {
                if (Debug::isVerbose()) {
                    echo "Deferred\n";
                }

                $this->deferred->put($device->device_id, $device->parents);
                foreach ($waiting_on as $parent_id) {
                    if (Debug::isVerbose()) {
                        echo "Adding $device->device_id to list waiting for $parent_id\n";
                    }

                    if ($this->waiting_on->has($parent_id)) {
                        $child_list = $this->waiting_on->get($parent_id);
                        $child_list->put($device->device_id, true);
                    } else {
                        // create a new entry containing this device
                        $this->waiting_on->put($parent_id, collect([$device->device_id => true]));
                    }
                }
            }
        }

        $this->runDeferredAlerts($device->device_id);
    }

    /**
     * Run any deferred alerts
     */
    private function runDeferredAlerts(int $device_id)
    {
        // check for any devices waiting on this device
        if ($this->waiting_on->has($device_id)) {
            $children = $this->waiting_on->get($device_id)->keys();

            // Check each child to see if alerts have been deferred
            foreach ($children as $child_id) {
                if ($this->deferred->has($child_id)) {
                    // run alert if all parents have been processed
                    $alert_child = true;
                    $parents = $this->deferred->get($child_id);

                    foreach ($parents as $parent) {
                        if (! $this->processed->has($parent->device_id)) {
                            if (Debug::isVerbose()) {
                                echo "Deferring device $child_id triggered by $device_id still waiting for $parent->device_id\n";
                            }

                            $alert_child = false;
                        }
                    }

                    if ($alert_child) {
                        if (Debug::isVerbose()) {
                            echo "Deferred device $child_id triggered by $device_id\n";
                        }

                        $this->runAlerts($child_id);
                        $this->deferred->pull($child_id);
                    }
                }
            }
        }

        $this->waiting_on->pull($device_id);
    }

    /**
     * run alerts for a device
     */
    private function runAlerts(int $device_id)
    {
        $rules = new AlertRules;
        $rules->runRules($device_id);
    }
}
