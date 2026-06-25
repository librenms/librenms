<?php

/*
 * Fping.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://librenms.org
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source\Icmp;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use LibreNMS\Enum\AddressFamily;
use LibreNMS\Enum\FpingExitCode;
use LibreNMS\Exceptions\FpingUnparsableLine;
use Log;
use Symfony\Component\Process\Process;

class Fping
{
    private readonly int $count;
    private readonly int $interval;
    private readonly int $timeout;
    private readonly int $retries;
    private readonly int $tos;
    private readonly array $fping4_cmd;
    private readonly array $fping6_cmd;

    public function __construct()
    {
        $this->count = max((int) LibrenmsConfig::get('fping_options.count', 3), 1);
        $this->interval = max((int) LibrenmsConfig::get('fping_options.interval', 500), 20);
        $this->timeout = max((int) LibrenmsConfig::get('fping_options.timeout', 500), $this->interval);
        $this->retries = (int) LibrenmsConfig::get('fping_options.retries', 2);
        $this->tos = (int) LibrenmsConfig::get('fping_options.tos', 0);

        $fping_bin = LibrenmsConfig::get('fping', 'fping');
        $fping6 = LibrenmsConfig::get('fping6', 'fping6');
        $fping6_bin = is_executable($fping6) ? $fping6 : false;

        $this->fping4_cmd = $fping6_bin === false ? [$fping_bin, '-4'] : [$fping_bin];
        $this->fping6_cmd = $fping6_bin === false ? [$fping_bin, '-6'] : [$fping6_bin];
    }

    /**
     * Get the fping command for a given address family
     */
    public function fpingCommand(AddressFamily $af): array
    {
        return match ($af) {
            AddressFamily::IPv4 => $this->fping4_cmd,
            AddressFamily::IPv6 => $this->fping6_cmd,
        };
    }

    /**
     * Run fping against a hostname/ip in count mode and collect stats.
     *
     * @param  string  $host  hostname or ip
     * @param  AddressFamily  $address_family  ipv4 or ipv6
     */
    public function ping(string $host, AddressFamily $address_family = AddressFamily::IPv4): FpingResponse
    {
        $measure = Measurement::start('ping');

        $args = [
            '-e',
            '-q',
            '-c', (string) $this->count,
            '-p', (string) $this->interval,
            '-t', (string) $this->timeout,
            '-O', (string) $this->tos,
            $host,
        ];
        $cmd = array_merge($this->fpingCommand($address_family), $args);

        $process = app()->make(Process::class, ['command' => $cmd]);
        Log::debug('[FPING] ' . $process->getCommandLine() . PHP_EOL);
        $process->run();

        try {
            $response = FpingResponse::parseMetricLine($process->getErrorOutput(), $process->getExitCode());
        } catch (FpingUnparsableLine $e) {
            $code = $process->getExitCode();
            if ($code !== 0) {
                $response = FpingResponse::createError(FpingExitCode::tryFrom($code) ?? FpingExitCode::SysCallFail, $host);
            } else {
                throw $e;
            }
        }
        $measure->manager()->recordFping($measure->end());

        Log::debug("response: $response");

        return $response;
    }

    /**
     * Run bulk fping and stream responses via callback.
     *
     * @param  array<string>  $hosts
     * @param  callable(FpingResponse): void  $callback
     */
    public function bulkPing(array $hosts, callable $callback): void
    {
        $args = [
            '-f', '-',
            '-t', (string) $this->timeout,
            '-r', (string) $this->retries,
            '-O', (string) $this->tos,
        ];
        $cmd = array_merge($this->fpingCommand(AddressFamily::IPv4), $args);

        $process = app()->make(Process::class, ['command' => $cmd]);
        $process->setTimeout(LibrenmsConfig::get('rrd.step', 300) * 2);
        $process->setInput(implode(PHP_EOL, $hosts) . PHP_EOL);

        Log::debug('[FPING] ' . $process->getCommandLine() . PHP_EOL);

        $partials = [Process::ERR => '', Process::OUT => ''];

        $process->run(function ($type, $output) use ($callback, &$partials): void {
            $data = $partials[$type] . $output;
            $lines = explode(PHP_EOL, $data);
            $partials[$type] = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    try {
                        $response = FpingResponse::parseAliveLine($line);
                        $callback($response);
                    } catch (FpingUnparsableLine) {
                        // ignore
                    }
                }
            }
        });

        foreach ($partials as $line) {
            $line = trim($line);
            if ($line !== '') {
                try {
                    $response = FpingResponse::parseAliveLine($line);
                    $callback($response);
                } catch (FpingUnparsableLine) {
                    // ignore
                }
            }
        }
    }

    /**
     * Test MTU by sending a ping with a specific payload size.
     *
     * @param  string  $host  hostname or ip
     * @param  int  $size  packet size in bytes (headers included)
     * @param  AddressFamily  $address_family  ipv4 or ipv6
     */
    public function testMtu(string $host, int $size, AddressFamily $address_family = AddressFamily::IPv4): bool
    {
        $bytes = $size > 28 ? $size - 28 : $size;

        $args = [
            '-q',
            '-b', (string) $bytes,
            $host,
        ];
        $cmd = array_merge($this->fpingCommand($address_family), $args);

        Log::debug('[MTU] ' . implode(' ', $cmd) . PHP_EOL);

        $process = app()->make(Process::class, ['command' => $cmd]);
        $process->disableOutput();
        $process->run();

        return $process->isSuccessful();
    }
}
