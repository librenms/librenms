<?php

/*
 * FpingResponse.php
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

namespace LibreNMS\Data\Source\Icmp;

use App\Facades\Rrd;
use App\Models\Device;
use App\Models\DeviceStats;
use LibreNMS\Enum\FpingExitCode;
use LibreNMS\Exceptions\FpingUnparsableLine;
use LibreNMS\RRD\RrdDefinition;

class FpingResponse implements \Stringable
{
    /**
     * @param  int  $transmitted  ICMP packets transmitted
     * @param  int  $received  ICMP packets received
     * @param  int  $loss  Percentage of packets lost
     * @param  float  $min_latency  Minimum latency (ms)
     * @param  float  $max_latency  Maximum latency (ms)
     * @param  float  $avg_latency  Average latency (ms)
     * @param  int  $duplicates  Number of duplicate responses (Indicates network issue)
     * @param  FpingExitCode  $exit_code  Return code from fping
     * @param  string|null  $host  Hostname/IP pinged
     */
    public function __construct(
        public readonly int $transmitted,
        public readonly int $received,
        public readonly int $loss,
        public readonly float $min_latency,
        public readonly float $max_latency,
        public readonly float $avg_latency,
        public readonly int $duplicates,
        public FpingExitCode $exit_code,
        public readonly ?string $host = null,
        private readonly bool $skipped = false
    ) {
    }

    public static function artificialUp(?string $host = null): static
    {
        return new static(
            transmitted: 1,
            received: 1,
            loss: 0,
            min_latency: 0.0,
            max_latency: 0.0,
            avg_latency: 0.0,
            duplicates: 0,
            exit_code: FpingExitCode::Success,
            host: $host,
            skipped: true,
        );
    }

    public static function artificialDown(?string $host = null): static
    {
        return new static(
            transmitted: 1,
            received: 0,
            loss: 100,
            min_latency: 0.0,
            max_latency: 0.0,
            avg_latency: 0.0,
            duplicates: 0,
            exit_code: FpingExitCode::Success,
            host: $host,
            skipped: false,
        );
    }

    public static function createError(FpingExitCode $exitCode, ?string $host = null): static
    {
        return new static(
            transmitted: 0,
            received: 0,
            loss: 0,
            min_latency: 0.0,
            max_latency: 0.0,
            avg_latency: 0.0,
            duplicates: 0,
            exit_code: $exitCode,
            host: $host,
        );
    }

    public function wasSkipped(): bool
    {
        return $this->skipped;
    }

    public static function parseAliveLine(string $output): FpingResponse
    {
        // Try parsing as simple "alive / unreachable" first
        if (preg_match('/^(\S+) is (alive|unreachable)$/', $output, $parsed)) {
            $host = $parsed[1];
            $isAlive = $parsed[2] === 'alive';

            return new static(
                transmitted: 1,
                received: $isAlive ? 1 : 0,
                loss: $isAlive ? 0 : 100,
                min_latency: 0.0,
                max_latency: 0.0,
                avg_latency: 0.0,
                duplicates: 0,
                exit_code: $isAlive ? FpingExitCode::Success : FpingExitCode::Unreachable,
                host: $host,
            );
        }

        // Try parsing name resolution error lines
        if (preg_match('/^(\S+): (Name or service not known|Temporary failure in name resolution)$/', $output, $parsed)) {
            $host = $parsed[1];
            $error = $parsed[2];

            $exitCode = $error === 'Name or service not known' ? FpingExitCode::InvalidHost : FpingExitCode::SysCallFail;

            return static::createError($exitCode, $host);
        }

        throw new FpingUnparsableLine($output);
    }

    public static function parseMetricLine(string $output, ?int $code = null): FpingResponse
    {
        $matched = preg_match('#(\S+)\s*: (xmt/rcv/%loss = (\d+)/(\d+)/(?:(100)%|(\d+)%, min/avg/max = ([\d.]+)/([\d.]+)/([\d.]+))|Name or service not known|Temporary failure in name resolution)$#', $output, $parsed);

        if (! $matched) {
            throw new FpingUnparsableLine($output);
        }

        [, $host, $error, $xmt, $rcv, $loss100, $loss, $min, $avg, $max] = array_pad($parsed, 10, null);
        $loss = $loss100 ?: $loss;

        if ($error === 'Name or service not known') {
            return static::createError(FpingExitCode::InvalidHost, (string) $host);
        }

        if ($error === 'Temporary failure in name resolution') {
            return static::createError(FpingExitCode::SysCallFail, (string) $host);
        }

        $parsedExitCode = $code !== null ? (FpingExitCode::tryFrom($code) ?? FpingExitCode::SysCallFail) : ($loss100 ? FpingExitCode::Unreachable : FpingExitCode::Success);

        return new static(
            transmitted: (int) $xmt,
            received: (int) $rcv,
            loss: (int) $loss,
            min_latency: (float) $min,
            max_latency: (float) $max,
            avg_latency: (float) $avg,
            duplicates: substr_count($output, 'duplicate'),
            exit_code: $parsedExitCode,
            host: (string) $host,
        );
    }

    /**
     * Ping result was successful.
     * fping didn't have an error and we got at least one ICMP packet back.
     */
    public function isAlive(): bool
    {
        return $this->exit_code === FpingExitCode::Success && $this->loss < 100;
    }

    /**
     * Change the exit code to 0, this may be appropriate when a non-fatal error was encountered
     */
    public function ignoreFailure(): void
    {
        $this->exit_code = FpingExitCode::Success;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getExitCode(): FpingExitCode
    {
        return $this->exit_code;
    }

    public function __toString(): string
    {
        $str = "$this->host : xmt/rcv/%loss = $this->transmitted/$this->received/$this->loss%";

        if ($this->max_latency) {
            $str .= ", min/avg/max = $this->min_latency/$this->avg_latency/$this->max_latency";
        }

        return $str;
    }

    /**
     * Save ping stats to device_stats table and icmp-perf rrd
     */
    public function saveStats(Device $device): void
    {
        $stats = $device->stats ?? new DeviceStats(['device_id' => $device->device_id]);
        $stats->fillStats($this);
        $stats->save();

        // detailed multi-ping capable graph
        app('Datastore')->put($device->toArray(), 'icmp-perf', [
            'rrd_def' => RrdDefinition::make()
                ->addDataset('avg', 'GAUGE', 0, 65535, source_ds: 'ping', source_file: Rrd::name($device->hostname, 'ping-perf'))
                ->addDataset('xmt', 'GAUGE', 0, 65535)
                ->addDataset('rcv', 'GAUGE', 0, 65535)
                ->addDataset('min', 'GAUGE', 0, 65535)
                ->addDataset('max', 'GAUGE', 0, 65535),
        ], [
            'avg' => $this->avg_latency,
            'xmt' => $this->transmitted,
            'rcv' => $this->received,
            'min' => $this->min_latency,
            'max' => $this->max_latency,
        ]);
    }
}
