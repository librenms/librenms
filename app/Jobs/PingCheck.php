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
use Illuminate\Support\Facades\Log;
use LibreNMS\Alert\AlertRules;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Source\FpingResponse;

class PingCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Collection<string, Device> List of devices keyed by hostname */
    private Collection $devices;
    /** @var array List of device group ids to check */
    private array $groups = [];

    // working data for loop
    /** @var Collection */
    private Collection $deferred;
    /** @var Collection<int, Collection<int, bool>> device id, parent devices */
    private Collection $waiting_on;
    /** @var Collection<int, bool> */
    private Collection $processed;

    /**
     * Create a new job instance.
     *
     * @param  array  $groups  List of distributed poller groups to check
     */
    public function __construct(array $groups = [])
    {
        $this->groups = $groups;
        $this->deferred = new Collection;
        $this->waiting_on = new Collection;
        $this->processed = new Collection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $ping_start = microtime(true);

        $ordered_hostname_list = $this->orderHostnames($this->fetchDevices());

        Log::info('Processing hosts in this order : ' . implode(', ', $ordered_hostname_list));

        // bulk ping and send FpingResponse's to recordData as they come in
        app()->make(Fping::class)->bulkPing($ordered_hostname_list, [$this, 'handleResponse']);

        // check for any left over devices
        if ($this->deferred->isNotEmpty()) {
            Log::debug("Leftover deferred devices, this shouldn't happen: " . $this->deferred->keys()->implode(', '));
        }

        if ($this->waiting_on->isNotEmpty()) {
            Log::debug("Leftover waiting on devices, this shouldn't happen: " . $this->waiting_on->keys()->implode(', '));
        }

        if (\App::runningInConsole()) {
            printf("Pinged %s devices in %.2fs\n", $this->devices->count(), microtime(true) - $ping_start);
        }
    }

    /**
     * Get an ordered list of hostnames that we need to ping starting from devices with no parents
     */
    private function orderHostnames(Collection $devices): array
    {
        $ordered_device_list = new Collection;

        // start with root nodes (no parents)
        [$current_tier_devices, $pending_children] = $devices->keyBy('device_id')->partition(fn (Device $d) => $d->parents_count === 0);

        // recurse down until no children are found
        while ($current_tier_devices->isNotEmpty()) {
            // add current tier to the list
            $ordered_device_list = $ordered_device_list->merge($current_tier_devices);
            // fetch the next tier of devices
            $current_tier_devices = $current_tier_devices
                ->pluck('children.*.device_id')->flatten() // get all direct child ids
                ->map(fn ($child_id) => $pending_children->pull($child_id)) // fetch and remove the device from pending if it exists
                ->filter(); // filter out children that are already in the list
        }

        // just add any left over
        $ordered_device_list = $ordered_device_list->merge($pending_children);

        return $ordered_device_list->map(fn (Device $device) => $device->overwrite_ip ?: $device->hostname)->all();
    }

    /**
     * Fetch and cache all devices that we need to process
     */
    private function fetchDevices(): Collection
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

        return $this->devices;
    }

    /**
     * Record the data and run alerts if all parents have been processed
     */
    public function handleResponse(FpingResponse $response): void
    {
        Log::debug("Attempting to record data for $response->host");

        $device = $this->devices->get($response->host);

        if ($device === null) {
            Log::error("Ping host from response not found $response->host");

            return;
        }

        $waiting_on = [];
        foreach ($device->parents ?? [] as $parent) {
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
        Log::debug("Recorded data for $device->hostname");

        if (isset($type)) { // only run alert rules if status changed
            Log::debug("Device $device->hostname changed status to $type, running alerts");

            if (count($waiting_on) === 0) {
                $this->runAlerts($device->device_id);
            } else {
                Log::debug('Alerts Deferred');

                $this->deferred->put($device->device_id, $device->parents);
                foreach ($waiting_on as $parent_id) {
                    Log::debug("Adding $device->device_id to list waiting for $parent_id");

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
    private function runDeferredAlerts(int $device_id): void
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
                            Log::debug("Deferring device $child_id triggered by $device_id still waiting for $parent->device_id");

                            $alert_child = false;
                        }
                    }

                    if ($alert_child) {
                        Log::debug("Deferred device $child_id triggered by $device_id");

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
    private function runAlerts(int $device_id): void
    {
        $rules = new AlertRules;
        $rules->runRules($device_id);
    }
}
