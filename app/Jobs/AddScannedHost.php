<?php

/**
 * AddScannedHost.php
 *
 * Add a host found by a network scan to LibreNMS.
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

namespace App\Jobs;

use App\Actions\Device\LegacyDeviceCreator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\ScanOutcome;
use LibreNMS\Exceptions\HostExistsException;
use LibreNMS\Exceptions\HostUnreachableException;
use LibreNMS\Util\Dns;
use Throwable;

class AddScannedHost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1; // a scan is repeated on its own schedule, don't retry

    public function __construct(
        public string $ip,
        public int $pollerGroup = 0,
        public bool $dnsOnly = false,
        public bool $pingFallback = false,
        public bool $pingOnly = false,
    ) {
    }

    public function displayName(): string
    {
        return "AddScannedHost:$this->ip";
    }

    /**
     * The outcome is returned when run in the current process with Bus::dispatchNow().
     */
    public function handle(Dns $dns): ScanOutcome
    {
        $hostname = $dns->resolveHostname($this->ip);

        if ($this->dnsOnly && ! $hostname) {
            Log::info("$this->ip: DNS not resolved");

            return ScanOutcome::NoDns;
        }

        $creator = new LegacyDeviceCreator(
            hostname: $hostname ?: $this->ip,
            poller_group: $this->pollerGroup,
            ping_only: $this->pingOnly,
            ping_fallback: $this->pingFallback && ! $this->pingOnly,
        );

        try {
            if (! $creator->execute()) {
                Log::error(trans('commands.device:add.messages.save_failed', ['hostname' => $creator->hostname]));

                return ScanOutcome::Failed;
            }

            $device = $creator->getDevice();
            Log::info(trans('commands.device:add.messages.added', ['hostname' => $device->hostname, 'device_id' => $device->device_id]));

            return ScanOutcome::Added;
        } catch (HostUnreachableException $e) {
            Log::info($e->getMessage() . PHP_EOL . implode(PHP_EOL, $e->getReasons()));

            return $e->method === PollingMethodType::Icmp ? ScanOutcome::Unpingable : ScanOutcome::Failed;
        } catch (HostExistsException $e) {
            Log::info($e->getMessage());

            return ScanOutcome::Known;
        } catch (Throwable $e) {
            Log::error("$this->ip: " . $e->getMessage());

            return ScanOutcome::Error;
        }
    }
}
