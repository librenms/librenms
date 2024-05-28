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
    private $tiered;
    /** @var Collection */
    private $current;
    private $current_tier;
    /** @var Collection */
    private $deferred;

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

        $this->fetchDevices();

        $ordered_device_list = $this->tiered->get(1, collect())->keys()// root nodes before standalone nodes
        ->merge($this->devices->keys())
            ->unique()->all();

        // bulk ping and send FpingResponse's to recordData as they come in
        app()->make(Fping::class)->bulkPing($ordered_device_list, [$this, 'handleResponse']);

        // check for any left over devices
        if ($this->deferred->isNotEmpty()) {
            d_echo("Leftover devices, this shouldn't happen: " . $this->deferred->flatten(1)->implode('hostname', ', ') . PHP_EOL);
            d_echo('Devices left in tier: ' . collect($this->current)->implode('hostname', ', ') . PHP_EOL);
        }

        if (\App::runningInConsole()) {
            printf("Pinged %s devices in %.2fs\n", $this->devices->count(), microtime(true) - $ping_start);
        }
    }

    private function fetchDevices()
    {
        if (isset($this->devices)) {
            return $this->devices;
        }

        $query = Device::canPing()
            ->select(['devices.device_id', 'hostname', 'overwrite_ip', 'status', 'status_reason', 'last_ping', 'last_ping_timetaken', 'max_depth'])
            ->orderBy('max_depth');

        if ($this->groups) {
            $query->whereIntegerInRaw('poller_group', $this->groups);
        }

        $this->devices = $query->get()->keyBy(function ($device) {
            return $device->overwrite_ip ?: $device->hostname;
        });

        // working collections
        $this->tiered = $this->devices->groupBy('max_depth', true);
        $this->deferred = new Collection();

        // start with tier 1 (the root nodes, 0 is standalone)
        $this->current_tier = 1;
        $this->current = $this->tiered->get($this->current_tier, collect());

        if (Debug::isVerbose()) {
            $this->tiered->each(function (Collection $tier, $index) {
                echo "Tier $index (" . $tier->count() . '): ';
                echo $tier->implode('hostname', ', ');
                echo PHP_EOL;
            });
        }

        return $this->devices;
    }

    /**
     * Check if this tier is complete and move to the next tier
     * If we moved to the next tier, check if we can report any of our deferred results
     */
    private function processTier()
    {
        if ($this->current->isNotEmpty()) {
            return;
        }

        $this->current_tier++;  // next tier

        if (! $this->tiered->has($this->current_tier)) {
            // out of devices
            return;
        }

        if (Debug::isVerbose()) {
            echo "Out of devices at this tier, moving to tier $this->current_tier\n";
        }

        $this->current = $this->tiered->get($this->current_tier);

        // update and remove devices in the current tier
        foreach ($this->deferred->pull($this->current_tier, []) as $fpingResponse) {
            $this->handleResponse($fpingResponse);
        }

        // try to process the new tier in case we took care of all the devices
        $this->processTier();
    }

    /**
     * If the device is on the current tier, record the data and remove it
     * $data should have keys: hostname, status, and conditionally rtt
     */
    public function handleResponse(FpingResponse $response): void
    {
        if (Debug::isVerbose()) {
            echo "Attempting to record data for $response->host... ";
        }

        $device = $this->devices->get($response->host);

        // process the data if this is a standalone device or in the current tier
        if ($device->max_depth === 0 || $this->current->has($device->hostname)) {
            if (Debug::isVerbose()) {
                echo "Success\n";
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

            if (isset($type)) { // only run alert rules if status changed
                echo "Device $device->hostname changed status to $type, running alerts\n";
                $rules = new AlertRules;
                $rules->runRules($device->device_id);
            }

            // done with this device
            $this->complete($device->hostname);
            d_echo("Recorded data for $device->hostname (tier $device->max_depth)\n");
        } else {
            if (Debug::isVerbose()) {
                echo "Deferred\n";
            }

            $this->defer($response);
        }

        $this->processTier();
    }

    /**
     * Done processing $hostname, remove it from our active data
     *
     * @param  string  $hostname
     */
    private function complete($hostname)
    {
        $this->current->offsetUnset($hostname);
        $this->deferred->each->offsetUnset($hostname);
    }

    /**
     * Defer this data processing until all parent devices are complete
     */
    private function defer(FpingResponse $response): void
    {
        $device = $this->devices->get($response->host);
        if ($device == null) {
            dd("could not find $response->host");
        }

        if ($this->deferred->has($device->max_depth)) {
            // add this data to the proper tier, unless it already exists...
            $tier = $this->deferred->get($device->max_depth);
            if (! $tier->has($device->hostname)) {
                $tier->put($device->hostname, $response);
            }
        } else {
            // create a new tier containing this data
            $this->deferred->put($device->max_depth, collect([$device->hostname => $response]));
        }
    }
}
