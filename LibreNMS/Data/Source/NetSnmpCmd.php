<?php

/*
 * NetSnmpCmd.php
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
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source;

use App\Facades\LibrenmsConfig;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Debug;
use Log;
use Symfony\Component\Process\Process;

class NetSnmpCmd
{
    /** @var string[] */
    protected array $commandCleanupPatterns = [
        '/-c\' \'[\S]+\'/',
        '/-u\' \'[\S]+\'/',
        '/-U\' \'[\S]+\'/',
        '/-A\' \'[\S]+\'/',
        '/-X\' \'[\S]+\'/',
        '/-P\' \'[\S]+\'/',
        '/-H\' \'[\S]+\'/',
        '/(udp|udp6|tcp|tcp6):([^:]+):([\d]+)/',
    ];

    /** @var string[] */
    protected array $commandReplacementPatterns = [
        '-c\' \'COMMUNITY\'',
        '-u\' \'USER\'',
        '-U\' \'USER\'',
        '-A\' \'PASSWORD\'',
        '-X\' \'PASSWORD\'',
        '-P\' \'PASSWORD\'',
        '-H\' \'HOSTNAME\'',
        '\1:HOSTNAME:\3',
    ];

    protected string $output_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
    protected string $output_replacement = '*';

    protected bool $cache = false;

    protected function exec(string $command, array $oids): SnmpResponse
    {
        // use runtime(array) cache if requested. The 'null' driver will simply return the value without caching
        $driver = 'null';
        $key = '';

        if ($this->cache) {
            $driver = 'array';
            $key = $this->getCacheKey($command, $oids);

            if (Debug::isEnabled()) {
                $cache_performance = Cache::driver($driver)->get('SnmpQuery_cache_performance', []);
                $cache_performance[$key] ??= 0;

                if (Cache::driver($driver)->has($key)) {
                    Log::debug("Cache hit for $command " . implode(',', $oids));
                    $cache_performance[$key]++;
                } else {
                    Log::debug("Cache miss for $command " . implode(',', $oids) . ', grabbing fresh data.');
                }

                // update cache performance
                Cache::driver($driver)->put('SnmpQuery_cache_performance', $cache_performance);
            }
        }

        return Cache::driver($driver)->rememberForever($key, function () use ($command, $oids) {
            $measure = Measurement::start($command);
            $proc = new Process($this->buildCli($command, $oids));
            $proc->setTimeout(LibrenmsConfig::get('snmp.exec_timeout', 1200));

            $this->logCommand($proc->getCommandLine());

            $proc->run();
            $exitCode = $proc->getExitCode();
            $output = $proc->getOutput();
            $stderr = $proc->getErrorOutput();

            // check exit code and log possible bad auth
            $this->checkExitCode($exitCode, $stderr);
            $this->logOutput($output, $stderr);

            $measure->manager()->recordSnmp($measure->end());

            return new SnmpResponse($output, $stderr, $exitCode);
        });
    }

    protected function mibDirectories(): string
    {
        $base = LibrenmsConfig::get('mib_dir');
        $dirs = [$base];

        // os group
        if ($os_group = LibrenmsConfig::getOsSetting($this->device->os, 'group')) {
            if (file_exists("$base/$os_group")) {
                $dirs[] = "$base/$os_group";
            }
        }

        // os directory
        $os_mibdir = LibrenmsConfig::getOsSetting($this->device->os, 'mib_dir');
        if ($os_mibdir && is_string($os_mibdir)) {
            $dirs[] = "$base/$os_mibdir";
        } elseif (file_exists($base . '/' . $this->device->os)) {
            $dirs[] = $base . '/' . $this->device->os;
        }

        foreach ($this->mibDirs as $mibDir) {
            $dirs[] = "$base/$mibDir";
        }

        // remove trailing /, remove empty dirs, and remove duplicates
        $dirs = array_unique(array_filter(array_map(fn ($dir) => rtrim((string) $dir, '/'), $dirs)));

        return implode(':', $dirs);
    }

    private function checkExitCode(int $code, string $error): void
    {
        if ($code) {
            if (Str::startsWith($error, 'Invalid authentication protocol specified')) {
                Eventlog::log('Unsupported SNMP authentication algorithm - ' . $code, $this->device, 'poller', Severity::Error);
            } elseif (Str::startsWith($error, 'Invalid privacy protocol specified')) {
                Eventlog::log('Unsupported SNMP privacy algorithm - ' . $code, $this->device, 'poller', Severity::Error);
            }
            Log::debug('Exitcode: ' . $code, [$error]);
        }
    }

    private function logCommand(string $command): void
    {
        if (Debug::isEnabled() && ! Debug::isVerbose()) {
            $debug_command = preg_replace($this->commandCleanupPatterns, $this->commandReplacementPatterns, $command);
            Log::debug('SNMP[%c' . $debug_command . '%n]', ['color' => true]);
        } elseif (Debug::isVerbose()) {
            Log::debug('SNMP[%c' . $command . '%n]', ['color' => true]);
        }
    }

    private function logOutput(string $output, string $error): void
    {
        if (Debug::isEnabled() && ! Debug::isVerbose()) {
            Log::debug(preg_replace($this->output_regex, $this->output_replacement, $output));
        } elseif (Debug::isVerbose()) {
            Log::debug($output);
        }
        Log::debug($error);
    }

    private function getCacheKey(string $type, array $oids): string
    {
        $oids = implode(',', $oids);
        $options = implode(',', $this->options);

        return "$type|{$this->device->hostname}|{$this->device->community}|$this->context|$oids|$options";
    }
}
