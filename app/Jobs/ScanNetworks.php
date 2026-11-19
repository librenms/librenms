<?php

/**
 * ScanNetworks.php
 *
 * Scan networks for hosts and add them to LibreNMS.
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

use App\Actions\Device\AddFoundDeviceResult;
use App\Events\NetworkAddressScanned;
use App\Facades\LibrenmsConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Util\IP;

/**
 * The result of each address is reported with the NetworkAddressScanned event.
 * Only plain values are queued, the SNMP credentials are read when the scan runs so they are not stored in the queue.
 */
class ScanNetworks implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 1; // the hosts that were added would be found as known, don't retry
    public int $timeout = 3600; // less than the queue retry_after

    /**
     * @param  string  $scanId  passed to the NetworkAddressScanned events, to tell scans apart
     * @param  string[]|null  $networks  in CIDR notation, null for the nets setting
     * @param  int  $pollerGroup  the poller group the devices are added to, the scan runs on a poller of this group
     */
    public function __construct(
        public string $scanId,
        public ?array $networks = null,
        public int $pollerGroup = 0,
        public bool $pingFallback = false,
        public bool $pingOnly = false,
        public bool $dnsOnly = false,
        public int $concurrency = 32,
    ) {
        // the hosts must be reachable from the poller that adds them
        $this->onQueue($pollerGroup ? "discovery-$pollerGroup" : 'discovery');
    }

    public function displayName(): string
    {
        return "ScanNetworks:$this->scanId";
    }

    public function handle(NetworkScanner $scanner): void
    {
        $options = ScanOptions::fromConfig(
            concurrency: $this->concurrency,
            excludedNetworks: $this->excludedNetworks(),
            pollerGroup: $this->pollerGroup,
            dnsOnly: $this->dnsOnly,
            pingFallback: $this->pingFallback,
            pingOnly: $this->pingOnly,
        );

        $scanner->scan($this->parsedNetworks(), $options, function (string $ip, AddFoundDeviceResult $result): void {
            NetworkAddressScanned::dispatch($this->scanId, $ip, $result);
        });
    }

    /**
     * @return IP[]
     */
    private function parsedNetworks(): array
    {
        $networks = [];
        foreach ($this->networks ?? (array) LibrenmsConfig::get('nets', []) as $network) {
            $networks[] = IP::parse($network, true) ?? throw new InvalidArgumentException("Invalid network $network");
        }

        return $networks;
    }

    /**
     * @return string[]
     */
    private function excludedNetworks(): array
    {
        $excluded = [];
        foreach ((array) LibrenmsConfig::get('autodiscovery.nets-exclude', []) as $network) {
            if (IP::parse($network, true) === null) {
                Log::warning("Ignoring invalid network $network in autodiscovery.nets-exclude");
            } else {
                $excluded[] = $network;
            }
        }

        return $excluded;
    }
}
