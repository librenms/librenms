<?php

/**
 * NetworkScanner.php
 *
 * Scan networks for hosts and add them as devices.
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

namespace LibreNMS\Scan;

use App\Actions\Device\AddFoundDeviceResult;
use App\Jobs\AddScannedHost;
use Exception;
use Generator;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Util\IP;

class NetworkScanner
{
    /** the addresses that are checked for known devices and pinged together */
    private const BATCH_SIZE = 256;

    public function __construct(
        private readonly KnownAddresses $knownAddresses,
    ) {
    }

    /**
     * Probe every address concurrently and add the hosts that reply, one at a time.
     * The addresses are read as the scan goes, so a network of any size uses little memory.
     *
     * @param  IP[]  $networks
     * @param  callable(string, AddFoundDeviceResult): void  $onResult  called once for every address
     */
    public function scan(array $networks, ScanOptions $options, callable $onResult): void
    {
        /** @var HostProbe[] $probes */
        $probes = [];
        /** @var PingBatch[] $pings the batches fping is still running for */
        $pings = [];

        // check on every probe, report the finished ones
        $check = function () use (&$probes, &$pings, $onResult): void {
            $finished = false;

            // read the fping output once, not once for every probe
            foreach ($pings as $key => $ping) {
                if ($ping->poll()) {
                    unset($pings[$key]);
                }
            }

            foreach ($probes as $key => $probe) {
                $result = $probe->result();
                if ($result !== null) {
                    unset($probes[$key]);
                    $finished = true;
                    // the other probes keep running in their own processes while a host is added
                    $onResult($probe->ip, $result instanceof AddScannedHost ? $this->add($result) : new AddFoundDeviceResult($result));
                }
            }

            if (! $finished) {
                usleep(10000);
            }
        };

        foreach ($this->batches($this->withoutOverlaps($networks)) as $batch) {
            $batch = $this->withoutSkipped($batch, $options, $onResult);
            $ping = $options->icmpEnabled && $batch !== [] ? new PingBatch($batch) : null;
            if ($ping !== null) {
                $pings[] = $ping;
            }

            foreach ($batch as $ip) {
                while (count($probes) >= $options->concurrency) {
                    $check();
                }

                $probes[] = new HostProbe($ip, $options, $ping);
            }
        }

        while ($probes !== []) {
            $check();
        }
    }

    /**
     * Add a host, a failure is reported as the result of the host, so the scan goes on.
     */
    private function add(AddScannedHost $job): AddFoundDeviceResult
    {
        try {
            return Bus::dispatchNow($job);
        } catch (Exception $e) {
            Log::error("Failed to add scanned host $job->ip: " . $e->getMessage(), ['exception' => $e]);

            return new AddFoundDeviceResult(DeviceAddOutcome::Error, $e->getMessage());
        }
    }

    /**
     * Report the excluded and known addresses instead of probing them.
     *
     * @param  string[]  $batch
     * @param  callable(string, AddFoundDeviceResult): void  $onResult
     * @return string[] the addresses to probe
     */
    private function withoutSkipped(array $batch, ScanOptions $options, callable $onResult): array
    {
        $probe = $batch;
        if ($options->excludedNetworks !== []) {
            $probe = [];
            foreach ($batch as $ip) {
                if (IP::parse($ip)->inNetworks($options->excludedNetworks)) {
                    $onResult($ip, new AddFoundDeviceResult(DeviceAddOutcome::Excluded));
                } else {
                    $probe[] = $ip;
                }
            }
        }

        $known = $probe === [] ? [] : $this->knownAddresses->find($probe);
        foreach ($known as $ip => $hostname) {
            $onResult($ip, new AddFoundDeviceResult(DeviceAddOutcome::Known, trans('exceptions.host_exists.hostname_exists', ['hostname' => $hostname])));
        }

        return array_values(array_diff($probe, array_keys($known)));
    }

    /**
     * Drop the networks that another network already covers, so no address is probed twice.
     *
     * @param  IP[]  $networks
     * @return IP[] in the given order
     */
    public function withoutOverlaps(array $networks): array
    {
        // a network can only be covered by a larger prefix, check the largest first.
        // not by addressCount(), a /30 and a /31 both yield two addresses
        $bySize = $networks;
        uasort($bySize, fn (IP $a, IP $b) => ($b->host_bits - $b->cidr) <=> ($a->host_bits - $a->cidr));

        $covering = [];
        /** @var array<int, IP[]> $kept */
        $kept = [];
        foreach ($bySize as $key => $network) {
            // a point-to-point network at the edge of a larger network shares only one address with it, check them one at a time
            $parts = $network->cidr >= $network->host_bits - 1 ? array_map(IP::parse(...), iterator_to_array($network, false)) : [$network];

            foreach ($parts as $part) {
                foreach ($covering as $larger) {
                    if ($larger->includes($part)) {
                        continue 2;
                    }
                }

                $covering[] = $part;
                $kept[$key][] = $part;
            }
        }

        ksort($kept);

        return array_merge(...array_values($kept));
    }

    /**
     * @param  IP[]  $networks
     * @return Generator<int, string[]>
     */
    private function batches(array $networks): Generator
    {
        $batch = [];

        foreach ($networks as $network) {
            foreach ($network as $ip) {
                $batch[] = $ip;

                if (count($batch) === self::BATCH_SIZE) {
                    yield $batch;
                    $batch = [];
                }
            }
        }

        if ($batch !== []) {
            yield $batch;
        }
    }
}
