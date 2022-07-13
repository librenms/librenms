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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Source;

use LibreNMS\Config;
use Log;
use Symfony\Component\Process\Process;

class Fping
{
    /**
     * Run fping against a hostname/ip in count mode and collect stats.
     *
     * @param  string  $host
     * @param  int  $count  (min 1)
     * @param  int  $interval  (min 20)
     * @param  int  $timeout  (not more than $interval)
     * @param  string  $address_family  ipv4 or ipv6
     * @return \LibreNMS\Data\Source\FpingResponse
     */
    public function ping($host, $count = 3, $interval = 1000, $timeout = 500, $address_family = 'ipv4'): FpingResponse
    {
        $interval = max($interval, 20);

        $fping = Config::get('fping');
        $fping_tos = Config::get('fping_options.tos', 0);
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
            '-O',
            $fping_tos,
            $host,
        ]);

        $process = app()->make(Process::class, ['command' => $cmd]);
        Log::debug('[FPING] ' . $process->getCommandLine() . PHP_EOL);
        $process->run();

        $response = FpingResponse::parseOutput($process->getErrorOutput(), $process->getExitCode());

        Log::debug("response: $response");

        return $response;
    }
}
