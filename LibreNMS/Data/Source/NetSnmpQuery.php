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

namespace LibreNMS\Data\Source;

use App\Models\Device;
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\Alert;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Rewrite;
use Log;
use Symfony\Component\Process\Process;

class NetSnmpQuery implements SnmpQueryInterface
{
    private const DEFAULT_FLAGS = '-OQXUte';

    /**
     * @var array
     */
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
            ],
        ],
        'output' => [
            '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/',
            '*',
        ],
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
    private $options = [self::DEFAULT_FLAGS];
    /**
     * @var \App\Models\Device
     */
    private $device;

    public function __construct()
    {
        $this->device = DeviceCache::getPrimary();
    }

    /**
     * Easy way to start a new instance
     */
    public static function make(): SnmpQueryInterface
    {
        return new static;
    }

    /**
     * Specify a device to make the snmp query against.
     * By default the query will use the primary device.
     */
    public function device(Device $device): SnmpQueryInterface
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Specify a device by a device array.
     * The device will be fetched from the cache if it is loaded, otherwise, it will fill the array into a new Device
     */
    public function deviceArray(array $device): SnmpQueryInterface
    {
        if (isset($device['device_id']) && DeviceCache::has($device['device_id'])) {
            $this->device = DeviceCache::get($device['device_id']);

            return $this;
        }

        $this->device = new Device($device);

        return $this;
    }

    /**
     * Set a context for the snmp query
     * This is most commonly used to fetch alternate sets of data, such as different VRFs
     *
     * @param  string  $v2  Version 2/3 context name
     * @param  string|null  $v3  Version 3 context name if different from v2 context name
     * @return \LibreNMS\Data\Source\SnmpQueryInterface
     */
    public function context(string $v2, string $v3 = null): SnmpQueryInterface
    {
        $this->context = $this->device->snmpver === 'v3' && $v3 !== null ? $v3 : $v2;

        return $this;
    }

    /**
     * Set an additional MIB directory to search for MIBs.
     * You do not need to specify the base and os directories, they are already included.
     */
    public function mibDir(?string $dir): SnmpQueryInterface
    {
        $this->mibDir = $dir;

        return $this;
    }

    /**
     * Do not error on out of order indexes.
     * Use with caution as we could get stuck in an infinite loop.
     */
    public function allowUnordered(): SnmpQueryInterface
    {
        $this->options = array_merge($this->options, ['-Cc']);

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numeric(): SnmpQueryInterface
    {
        $this->options = array_merge($this->options, ['-On']);

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface
    {
        $this->options = array_merge($this->options, ['-Os']);

        return $this;
    }

    /**
     * Output enum values as strings instead of values. This could affect index output.
     */
    public function enumStrings(): SnmpQueryInterface
    {
        // remove -Oe from the default flags
        if (isset($this->options[0]) && Str::contains($this->options[0], 'e')) {
            $this->options[0] = str_replace('e', '', $this->options[0]);
        }

        return $this;
    }

    /**
     * Set option(s) for net-snmp command line. Overrides the default options.
     * Some options may break parsing, but you can manually parse the raw output if needed.
     * This will override other options set such as setting numeric.
     * Calling with null will reset to the default options (-OQXUte).
     * Try to avoid setting options this way to keep the API generic.
     *
     * @param  array|string|null  $options
     * @return $this
     */
    public function options($options = []): SnmpQueryInterface
    {
        $this->options = $options !== null
            ? Arr::wrap($options)
            : [self::DEFAULT_FLAGS];

        return $this;
    }

    /**
     * snmpget an OID
     * Commonly used to fetch a single or multiple explicit values.
     *
     * @param  array|string  $oid
     * @return \LibreNMS\Data\Source\SnmpResponse
     */
    public function get($oid): SnmpResponse
    {
        return $this->exec('snmpget', $this->parseOid($oid));
    }

    /**
     * snmpwalk an OID
     * Fetches all OIDs under a given OID, commonly used with tables.
     *
     * @param  array|string  $oid
     * @return \LibreNMS\Data\Source\SnmpResponse
     */
    public function walk($oid): SnmpResponse
    {
        return $this->exec('snmpwalk', $this->parseOid($oid));
    }

    /**
     * snmpnext for the given oid
     * snmpnext retrieves the first oid after the given oid.
     *
     * @param  array|string  $oid
     * @return \LibreNMS\Data\Source\SnmpResponse
     */
    public function next($oid): SnmpResponse
    {
        return $this->exec('snmpgetnext', $this->parseOid($oid));
    }

    /**
     * Translate an OID.
     * call numeric() on the query to output numeric OID
     */
    public function translate(string $oid, ?string $mib = null): SnmpResponse
    {
        if ($mib) {
            $this->options = array_merge($this->options, ['-m', $mib]);
        }

        return $this->exec('snmptranslate', [$oid]);
    }

    private function buildCli(string $command, array $oids): array
    {
        $cmd = $this->initCommand($command, $oids);

        array_push($cmd, '-M', $this->mibDirectories());

        if ($command === 'snmptranslate') {
            return array_merge($cmd, $this->options, $oids);
        }

        // authentication
        $this->buildAuth($cmd);

        $cmd = array_merge($cmd, $this->options);

        $timeout = $this->device->timeout ?? Config::get('snmp.timeout');
        if ($timeout && $timeout !== 1) {
            array_push($cmd, '-t', $timeout);
        }

        $retries = $this->device->retries ?? Config::get('snmp.retries');
        if ($retries && $retries !== 5) {
            array_push($cmd, '-r', $retries);
        }

        $hostname = Rewrite::addIpv6Brackets((string) ($this->device->overwrite_ip ?: $this->device->hostname));
        $cmd[] = ($this->device->transport ?? 'udp') . ':' . $hostname . ':' . $this->device->port;

        return array_merge($cmd, $oids);
    }

    private function buildAuth(array &$cmd): void
    {
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
    }

    private function exec(string $command, array $oids): SnmpResponse
    {
        $measure = Measurement::start($command);

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

        $measure->manager()->recordSnmp($measure->end());

        return new SnmpResponse($output, $stderr, $exitCode);
    }

    private function initCommand(string $binary, array $oids): array
    {
        if ($binary == 'snmpwalk'
            && $this->device->snmpver !== 'v1'
            && Config::getOsSetting($this->device->os, 'snmp_bulk', true)
            && empty(array_intersect($oids, Config::getCombined($this->device->os, 'oids.no_bulk'))) // skip for oids that do not work with bulk
        ) {
            $snmpcmd = [Config::get('snmpbulkwalk', 'snmpbulkwalk')];

            $max_repeaters = $this->device->getAttrib('snmp_max_repeaters') ?: Config::getOsSetting($this->device->os, 'snmp.max_repeaters', Config::get('snmp.max_repeaters', false));
            if ($max_repeaters > 0) {
                $snmpcmd[] = "-Cr$max_repeaters";
            }

            return $snmpcmd;
        }

        return [Config::get($binary, $binary)];
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
        if ($os_group = Config::get("os.{$this->device->os}.group")) {
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

    private function checkExitCode(int $code, string $error): void
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

    private function logCommand(string $command): void
    {
        if (Debug::isEnabled() && ! Debug::isVerbose()) {
            $debug_command = preg_replace($this->cleanup['command'][0], $this->cleanup['command'][1], $command);
            Log::debug('SNMP[%c' . $debug_command . '%n]', ['color' => true]);
        } elseif (Debug::isVerbose()) {
            Log::debug('SNMP[%c' . $command . '%n]', ['color' => true]);
        }
    }

    private function logOutput(string $output, string $error): void
    {
        if (Debug::isEnabled() && ! Debug::isVerbose()) {
            Log::debug(preg_replace($this->cleanup['output'][0], $this->cleanup['output'][1], $output));
        } elseif (Debug::isVerbose()) {
            Log::debug($output);
        }
        Log::debug($error);
    }

    /**
     * @param  array|string  $oid
     */
    private function parseOid($oid): array
    {
        return is_string($oid) ? explode(' ', $oid) : $oid;
    }
}
