<?php
/**
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
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS;

use Log;
use Symfony\Component\Process\Process;

class Fping
{
    /**
     * Run fping against a hostname/ip in count mode and collect stats.
     *
     * @param string $host
     * @param int $count (min 1)
     * @param int $interval (min 20)
     * @param int $timeout (not more than $interval)
     * @param string $address_family ipv4 or ipv6
     * @return array
     */
    public function ping($host, $count = 3, $interval = 1000, $timeout = 500, $address_family = 'ipv4')
    {
        $interval = max($interval, 20);

        $fping = Config::get('fping');
        $cmd = [$fping];
        if ($address_family == 'ipv6') {
            $fping6 = Config::get('fping6');
            $cmd = is_executable($fping6) ? [$fping6] : [$fping, '-6'];
        }

        // build the command
        $cmd = array_merge($cmd, [
            '-e',
            '-q',
            '-c',
            max($count, 1),
            '-p',
            $interval,
            '-t',
            max($timeout, $interval),
            $host,
        ]);

        $process = app()->make(Process::class, ['command' => $cmd]);
        Log::debug('[FPING] ' . $process->getCommandLine() . PHP_EOL);
        $process->run();
        $output = $process->getErrorOutput();

        preg_match('#= (\d+)/(\d+)/(\d+)%(, min/avg/max = ([\d.]+)/([\d.]+)/([\d.]+))?$#', $output, $parsed);
        [, $xmt, $rcv, $loss, , $min, $avg, $max] = array_pad($parsed, 8, 0);

        if ($loss < 0) {
            $xmt = 1;
            $rcv = 1;
            $loss = 100;
        }

        $response = [
            'xmt'  => (int) $xmt,
            'rcv'  => (int) $rcv,
            'loss' => (int) $loss,
            'min'  => (float) $min,
            'max'  => (float) $max,
            'avg'  => (float) $avg,
            'dup'  => substr_count($output, 'duplicate'),
            'exitcode' => $process->getExitCode(),
        ];
        Log::debug('response: ', $response);

        return $response;
    }
}
