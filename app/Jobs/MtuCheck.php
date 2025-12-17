<?php

/**
 * MtuCheck.php
 *
 * Device mtu check job
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
 * @copyright  2025 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace App\Jobs;

use App\Action;
use App\Actions\Alerts\RunAlertRulesAction;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class MtuCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Collection<string, Device> List of devices keyed by hostname */
    private Collection $devices;

    /**
     * Create a new job instance.
     *
     * @param  array  $groups  List of distributed poller groups to check
     */
    public function __construct(private array $groups = [])
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $mtu_start = microtime(true);

        $remaining_devices = $this->fetchDevices();

        $bytes = LibrenmsConfig::get('mtu_options.bytes');
        if ($bytes != null) {
            $fping_bytes = $bytes > 28 ? $bytes - 28 : $bytes;

            [$ping_hostname_list, $remaining_devices] = $this->getPingHosts($this->fetchDevices());
            Log::info('Pinging ' . count($ping_hostname_list) . ' hosts');
            Log::debug('Pinging the following hosts: ' . implode(', ', $ping_hostname_list));

            $this->testFping($ping_hostname_list, $fping_bytes);

            if ($remaining_devices->count() != 0) {
                Log::info('Tests were not run on the following hosts:' . implode(', ', $remaining_devices->map(fn (Device $d) => $d->overwrite_ip ?: $d->hostname)->all()));
            }
        } else {
            Log::info('Set mtu_options.bytes to enable MTU tests');
        }

        if (\App::runningInConsole()) {
            printf("Tested %s devices in %.2fs\n", $this->devices->count(), microtime(true) - $mtu_start);
        }
    }

    /**
     * Get an list of hostnames that we need to ping.  We don't care about order at the moment
     */
    private function getPingHosts(Collection $devices): array
    {
        // Select all devices with ping enabled
        [$ping_devices, $other_devices] = $devices->partition(fn (Device $d) => ! ($d->exists && $d->getAttrib('override_icmp_disable') === 'true'));

        return [$ping_devices->map(fn (Device $d) => $d->overwrite_ip ?: $d->hostname)->all(), $other_devices];
    }

    /**
     * Fetch and cache all devices that we need to process
     */
    private function fetchDevices(): Collection
    {
        if (isset($this->devices)) {
            return $this->devices;
        }

        // Only check devices that are enabled and online
        $query = Device::where('disabled', 0)->where('status', 1);

        if ($this->groups) {
            $query->whereIntegerInRaw('poller_group', $this->groups);
        }

        $this->devices = $query->get()->keyBy(fn ($device) => $device->overwrite_ip ?: $device->hostname);

        return $this->devices;
    }

    /**
     * Tests devices using fping
     */
    public function testFping(array $hostnames, int $bytes): void
    {
        $process = app()->make(Process::class, ['command' => [
            LibrenmsConfig::get('fping', 'fping'),
            '-f', '-',
            '-b', $bytes,
            '-r', 10,
        ]]);

        // Allow up to twice polling interval as a timeout
        $process->setTimeout(LibrenmsConfig::get('rrd.step', 300) * 2);
        // send hostnames to stdin to avoid overflowing cli length limits
        $process->setInput(implode(PHP_EOL, $hostnames) . PHP_EOL);

        Log::debug('[MTU] ' . $process->getCommandLine() . PHP_EOL);

        $partial = '';
        $process->run(function ($type, $output) use (&$partial): void {
            // stdout contains normal ping responses, stderr contains summaries
            if ($type == Process::OUT) {
                $lines = explode(PHP_EOL, $output);
                foreach ($lines as $index => $line) {
                    if ($line) {
                        Log::debug("Fping OUTPUT|$line PARTIAL|$partial");
                        $matched = preg_match('/(\S+)\s*is\s*(\S+)/', $line, $parsed);
                        if (! $matched) {
                            // handle possible partial line (only save it if it is the last line of output)
                            $partial = $index === array_key_last($lines) ? $partial . $line : '';
                        } else {
                            $this->processResult($parsed[1], $parsed[2] == 'alive');
                        }
                    }
                }
            }
        });
    }

    /**
     * process results for a device
     */
    private function processResult(string $hostname, bool $result): void
    {
        $device = $this->devices->get($hostname);

        if ($device->mtu_status != $result) {
            $device->mtu_status = $result;
            $device->save();
            Action::execute(RunAlertRulesAction::class, $device);
        } else {
            Log::debug("$hostname status has not changed");
        }
    }
}
