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

namespace LibreNMS\Data\Source;

use App\Facades\Rrd;
use App\Models\Device;
use Carbon\Carbon;
use LibreNMS\Exceptions\FpingUnparsableLine;
use LibreNMS\RRD\RrdDefinition;

class FpingResponse
{
    const SUCESS = 0;
    const UNREACHABLE = 1;
    const INVALID_HOST = 2;
    const INVALID_ARGS = 3;
    const SYS_CALL_FAIL = 4;

    /**
     * @param  int  $transmitted  ICMP packets transmitted
     * @param  int  $received  ICMP packets received
     * @param  int  $loss  Percentage of packets lost
     * @param  float  $min_latency  Minimum latency (ms)
     * @param  float  $max_latency  Maximum latency (ms)
     * @param  float  $avg_latency  Average latency (ms)
     * @param  int  $duplicates  Number of duplicate responses (Indicates network issue)
     * @param  int  $exit_code  Return code from fping
     * @param  string|null  $host  Hostname/IP pinged
     */
    private function __construct(
        public readonly int $transmitted,
        public readonly int $received,
        public readonly int $loss,
        public readonly float $min_latency,
        public readonly float $max_latency,
        public readonly float $avg_latency,
        public readonly int $duplicates,
        public int $exit_code,
        public readonly ?string $host = null,
        private bool $skipped = false)
    {
    }

    public static function artificialUp(?string $host = null): static
    {
        return new static(1, 1, 0, 0, 0, 0, 0, 0, $host, true);
    }

    public static function artificialDown(?string $host = null): static
    {
        return new static(1, 0, 100, 0, 0, 0, 0, 0, $host, false);
    }

    /**
     * Change the exit code to 0, this may be approriate when a non-fatal error was encourtered
     */
    public function ignoreFailure(): void
    {
        $this->exit_code = 0;
    }

    public function wasSkipped(): bool
    {
        return $this->skipped;
    }

    public static function parseLine(string $output, ?int $code = null): FpingResponse
    {
        $matched = preg_match('#(\S+)\s*: (xmt/rcv/%loss = (\d+)/(\d+)/(?:(100)%|(\d+)%, min/avg/max = ([\d.]+)/([\d.]+)/([\d.]+))|Name or service not known|Temporary failure in name resolution)$#', $output, $parsed);

        if ($code == 0 && ! $matched) {
            throw new FpingUnparsableLine($output);
        }

        [, $host, $error, $xmt, $rcv, $loss100, $loss, $min, $avg, $max] = array_pad($parsed, 10, 0);
        $loss = $loss100 ?: $loss;

        if ($error == 'Name or service not known') {
            return new FpingResponse(0, 0, 0, 0, 0, 0, 0, self::INVALID_HOST, $host);
        } elseif ($error == 'Temporary failure in name resolution') {
            return new FpingResponse(0, 0, 0, 0, 0, 0, 0, self::SYS_CALL_FAIL, $host);
        }

        return new static(
            (int) $xmt,
            (int) $rcv,
            (int) $loss,
            (float) $min,
            (float) $max,
            (float) $avg,
            substr_count($output, 'duplicate'),
            $code ?? ($loss100 ? self::UNREACHABLE : self::SUCESS),
            $host,
        );
    }

    /**
     * Ping result was successful.
     * fping didn't have an error and we got at least one ICMP packet back.
     */
    public function success(): bool
    {
        return $this->exit_code == 0 && $this->loss < 100;
    }

    public function __toString()
    {
        $str = "$this->host : xmt/rcv/%loss = $this->transmitted/$this->received/$this->loss%";

        if ($this->max_latency) {
            $str .= ", min/avg/max = $this->min_latency/$this->avg_latency/$this->max_latency";
        }

        return $str;
    }

    public function saveStats(Device $device): void
    {
        $device->last_ping = Carbon::now();
        $device->last_ping_timetaken = $this->avg_latency ?: $device->last_ping_timetaken;
        $device->save();

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
