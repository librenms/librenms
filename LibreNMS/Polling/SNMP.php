<?php
/*
 * SNMP.php
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

namespace LibreNMS\Polling;

use App\Models\Device;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\Alert;
use LibreNMS\Util\Debug;
use Log;
use Symfony\Component\Process\Process;

class SNMP
{
    private $cleanup = [
        'command' => [
            [
                '/-c\' \'[\S]+\'/',
                '/-u\' \'[\S]+\'/',
                '/-U\' \'[\S]+\'/',
                '/-A\' \'[\S]+\'/',
                '/-X\' \'[\S]+\'/',
                '/-P\' \'[\S]+\'/',
                '/-H\' \'[\S]+\'/',
                '/(udp|udp6|tcp|tcp6):([^:]+):([\d]+)/',
            ], [
                '-c\' \'COMMUNITY\'',
                '-u\' \'USER\'',
                '-U\' \'USER\'',
                '-A\' \'PASSWORD\'',
                '-X\' \'PASSWORD\'',
                '-P\' \'PASSWORD\'',
                '-H\' \'HOSTNAME\'',
                '\1:HOSTNAME:\3',
            ]
        ],
        'output' => [
            '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/',
            '*',
        ]
    ];
    /**
     * @var string
     */
    private $context = '';
    /**
     * @var string
     */
    private $mibDir;
    /**
     * @var array|string
     */
    private $options = [];

    private $defaultOptions = ['-OqX'];
    /**
     * @var string
     */
    private $mib;

    public function __construct()
    {
        $this->device = \DeviceCache::getPrimary();
    }

    public function device(Device $device): SNMP
    {
        $this->device = $device;
        return $this;
    }

    public function context(string $context): SNMP
    {
        $this->context = $context;
        return $this;
    }

    public function mibDir(string $dir): SNMP
    {
        $this->mibDir = $dir;
        return $this;
    }

    public function mib(string $mib): SNMP
    {
        $this->mib = $mib;
        return $this;
    }

    /**
     * @param array|string $options
     * @return $this
     */
    public function options($options): SNMP
    {
        $this->options = Arr::wrap($options);
        return $this;
    }

    /**
     * Get and OID
     * @param array|string $oid
     * @return \LibreNMS\Polling\SnmpResponse
     */
    public function get($oid): SnmpResponse
    {
        return $this->exec('snmpget', is_string($oid) ? explode(' ', $oid) : $oid);
    }

    /**
     * Walk and OID
     * @param array|string $oid
     * @return \LibreNMS\Polling\SnmpResponse
     */
    public function walk($oid): SnmpResponse
    {
        return $this->exec('snmpwalk', is_string($oid) ? explode(' ', $oid) : $oid);
    }

    private function recordStatistic(string $type, $start_time): float
    {
        global $snmp_stats;

        $runtime = microtime(true) - $start_time;
        $snmp_stats['ops'][$type] = isset($snmp_stats['ops'][$type]) ? $snmp_stats['ops'][$type] + 1 : 0;
        $snmp_stats['time'][$type] = isset($snmp_stats['time'][$type]) ? $snmp_stats['time'][$type] + $runtime : $runtime;

        return $runtime;
    }

    private function buildCli(string $command, array $oids): array
    {
        $cmd = $this->initCommand($command);

        // authentication
        if ($this->device->snmpver === 'v3') {
            array_push($cmd, '-v3', '-l', $this->device->authlevel);
            array_push($cmd, '-n', $this->context);

            switch (strtolower($this->device->authlevel)) {
                case 'authpriv':
                    array_push($cmd, '-x', $this->device->cryptoalgo);
                    array_push($cmd, '-X', $this->device->cryptopass);
                // fallthrough
                case 'authnopriv':
                    array_push($cmd, '-a', $this->device->authalgo);
                    array_push($cmd, '-A', $this->device->authpass);
                // fallthrough
                case 'noauthnopriv':
                    array_push($cmd, '-u', $this->device->authname ?: 'root');
                    break;
                default:
                    Log::debug("Unsupported SNMPv3 AuthLevel: {$this->device->snmpver}");
            }

        } elseif ($this->device->snmpver === 'v2c' || $this->device->snmpver === 'v1') {
            array_push($cmd, '-' . $this->device->snmpver, '-c', $this->context ? "{$this->device->community}@$this->context" : $this->device->community);
        } else {
            Log::debug("Unsupported SNMP Version: {$this->device->snmpver}");
        }

        // mibs
        if ($this->mib) {
            array_push($cmd, '-m', $this->mib);
        }
        array_push($cmd, '-M', $this->mibDirectories());

        $cmd = array_merge($cmd, $this->defaultOptions, $this->options);

        $timeout = $this->device->timeout ?? Config::get('snmp.timeout');
        if ($timeout && $timeout !== 1) {
            array_push($cmd, '-t', $timeout);
        }

        $retries = $this->device->retries ?? Config::get('snmp.retries');
        if ($retries && $retries !== 5) {
            array_push($cmd, '-r', $retries);
        }

        $hostname = \LibreNMS\Util\Rewrite::addIpv6Brackets($this->device->overwrite_ip ?: $this->device->hostname);
        $cmd[] = ($this->device->transport ?? 'udp') . ':' . $hostname . ':' . $this->device->port;

        return array_merge($cmd, $oids);
    }

    private function exec(string $command, array $oids): SnmpResponse
    {
        $time_start = microtime(true);

        $proc = new Process($this->buildCli($command, $oids));
        $proc->setTimeout(Config::get('snmp.exec_timeout', 1200));

        $this->logCommand($proc->getCommandLine());

        $proc->run();
        $exitCode = $proc->getExitCode();
        $output = $proc->getOutput();
        $stderr = $proc->getErrorOutput();

        // check exit code and log possible bad auth
        $this->checkExitCode($exitCode, $stderr);
        $this->logOutput($output, $stderr);

        $this->recordStatistic($command, $time_start);

        return new SnmpResponse($output, $exitCode);
    }

    private function initCommand($binary): array
    {
        if ($binary == 'snmpwalk') {
            if ($this->device->snmpver == 'v1' || (isset($this->device->os) && Config::getOsSetting($this->device->os, 'snmp_bulk', true) == false)) {
                return [Config::get($binary, $binary)];
            } else {
                $snmpcmd = [Config::get('snmpbulkwalk', 'snmpbulkwalk')];

                $max_repeaters = $this->device->getAttrib('snmp_max_repeaters') ?: Config::getOsSetting($this->device->os, 'snmp.max_repeaters', Config::get('snmp.max_repeaters', false));
                if ($max_repeaters > 0) {
                    $snmpcmd[] = "-Cr$max_repeaters";
                }
                return $snmpcmd;
            }
        }

        return [$binary];


        return $snmpcmd;
    }

    private function mibDirectories(): string
    {
        $base = Config::get('mib_dir');
        $dirs = [$base];

        // os directory
        if ($os_mibdir = Config::get("os.{$this->device->os}.mib_dir")) {
            $dirs[] = "$base/$os_mibdir";
        } elseif (file_exists($base . '/' . $this->device->os)) {
            $dirs[] = $base . '/' . $this->device->os;
        }

        // os group
        if ($os_group = Config::get("os.{$this->device->os}.os_group")) {
            if (file_exists("$base/$os_group")) {
                $dirs[] = "$base/$os_group";
            }
        }

        if ($this->mibDir) {
            $dirs[] = "$base/$this->mibDir";
        }

        // remove trailing /, remove empty dirs, and remove duplicates
        $dirs = array_unique(array_filter(array_map(function ($dir) {
            return rtrim($dir, '/');
        }, $dirs)));

        return implode(':', $dirs);
    }

    private function checkExitCode($code, $error)
    {
        if ($code) {
            if (Str::startsWith($error, 'Invalid authentication protocol specified')) {
                Log::event('Unsupported SNMP authentication algorithm - ' . $code, $this->device, 'poller', Alert::ERROR);
            } elseif (Str::startsWith($error, 'Invalid privacy protocol specified')) {
                Log::event('Unsupported SNMP privacy algorithm - ' . $code, $this->device, 'poller', Alert::ERROR);
            }
            Log::debug('Exitcode: ' . $code, [$error]);
        }
    }

    private function logCommand(string $command)
    {
        if (Debug::isEnabled() && ! Debug::isVerbose()) {
            $debug_command = preg_replace($this->cleanup['command'][0], $this->cleanup['command'][1], $command);
            Log::debug('SNMP[%c' . $debug_command . "%n]", ['color' => true]);
        } elseif (Debug::isVerbose()) {
            Log::debug('SNMP[%c' . $command . "%n]", ['color' => true]);
        }
    }

    private function logOutput(string $output, string $error)
    {
        if (Debug::isEnabled() && ! Debug::isVerbose()) {
            Log::debug(preg_replace($this->cleanup['output'][0], $this->cleanup['output'][1], $output));
        } elseif (Debug::isVerbose()) {
            Log::debug($output);
        }
        Log::debug($error);
    }
}
