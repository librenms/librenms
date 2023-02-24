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

use App\Models\DevicePerf;

class FpingResponse
{
    /**
     * @var int
     */
    public $transmitted;
    /**
     * @var int
     */
    public $received;
    /**
     * @var int
     */
    public $loss;
    /**
     * @var float
     */
    public $min_latency;
    /**
     * @var float
     */
    public $max_latency;
    /**
     * @var float
     */
    public $avg_latency;
    /**
     * @var int
     */
    public $duplicates;
    /**
     * @var int
     */
    public $exit_code;
    /**
     * @var bool
     */
    private $skipped;

    /**
     * @param  int  $transmitted  ICMP packets transmitted
     * @param  int  $received  ICMP packets received
     * @param  int  $loss  Percentage of packets lost
     * @param  float  $min_latency  Minimum latency (ms)
     * @param  float  $max_latency  Maximum latency (ms)
     * @param  float  $avg_latency  Average latency (ms)
     * @param  int  $duplicates  Number of duplicate responses (Indicates network issue)
     * @param  int  $exit_code  Return code from fping
     */
    public function __construct(int $transmitted, int $received, int $loss, float $min_latency, float $max_latency, float $avg_latency, int $duplicates, int $exit_code, bool $skipped = false)
    {
        $this->transmitted = $transmitted;
        $this->received = $received;
        $this->loss = $loss;
        $this->min_latency = $min_latency;
        $this->max_latency = $max_latency;
        $this->avg_latency = $avg_latency;
        $this->duplicates = $duplicates;
        $this->exit_code = $exit_code;
        $this->skipped = $skipped;
    }

    public static function artificialUp(): FpingResponse
    {
        return new FpingResponse(1, 1, 0, 0, 0, 0, 0, 0, true);
    }

    public function wasSkipped(): bool
    {
        return $this->skipped;
    }

    public static function parseOutput(string $output, int $code): FpingResponse
    {
        preg_match('#= (\d+)/(\d+)/(\d+)%(, min/avg/max = ([\d.]+)/([\d.]+)/([\d.]+))?$#', $output, $parsed);
        [, $xmt, $rcv, $loss, , $min, $avg, $max] = array_pad($parsed, 8, 0);

        if ($loss < 0) {
            $xmt = 1;
            $rcv = 0;
            $loss = 100;
        }

        return new FpingResponse(
            (int) $xmt,
            (int) $rcv,
            (int) $loss,
            (float) $min,
            (float) $max,
            (float) $avg,
            substr_count($output, 'duplicate'),
            $code
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

    public function toModel(): ?DevicePerf
    {
        return new DevicePerf([
            'xmt' => $this->transmitted,
            'rcv' => $this->received,
            'loss' => $this->loss,
            'min' => $this->min_latency,
            'max' => $this->max_latency,
            'avg' => $this->avg_latency,
        ]);
    }

    public function __toString()
    {
        $str = "xmt/rcv/%loss = $this->transmitted/$this->received/$this->loss%";

        if ($this->max_latency) {
            $str .= ", min/avg/max = $this->min_latency/$this->avg_latency/$this->max_latency";
        }

        return $str;
    }
}
