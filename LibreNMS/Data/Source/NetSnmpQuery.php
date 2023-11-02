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
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Oid;
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
     * @var string[]
     */
    private array $mibDirs = [];
    private string $context = '';
    private array|string $options = [self::DEFAULT_FLAGS];
    private Device $device;
    private bool $abort = false;
    private bool $cache = false;
    // defaults for net-snmp https://net-snmp.sourceforge.io/docs/man/snmpcmd.html
    private array $mibs = ['SNMPv2-TC', 'SNMPv2-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'];

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

    public function cache(): SnmpQueryInterface
    {
        $this->cache = true;

        return $this;
    }

    /**
     * Set a context for the snmp query
     * This is most commonly used to fetch alternate sets of data, such as different VRFs
     *
     * @param  string|null  $context  Version 2/3 context name
     * @param  string|null  $v3_prefix  Optional context prefix to prepend for Version 3 queries
     * @return \LibreNMS\Data\Source\SnmpQueryInterface
     */
    public function context(?string $context, ?string $v3_prefix = null): SnmpQueryInterface
    {
        if ($context && $this->device->snmpver === 'v3') {
            $context = $v3_prefix . $context;
        }

        $this->context = $context;

        return $this;
    }

    /**
     * Set an additional MIB directory to search for MIBs.
     * You do not need to specify the base and os directories, they are already included.
     */
    public function mibDir(?string $dir): SnmpQueryInterface
    {
        $this->mibDirs[] = $dir;

        return $this;
    }

    /**
     * Set MIBs to use for this query. Base mibs are included by default.
     * They will be appended to existing mibs unless $append is set to false.
     */
    public function mibs(array $mibs, bool $append = true): SnmpQueryInterface
    {
        $this->mibs = $append ? array_merge($this->mibs, $mibs) : $mibs;

        return $this;
    }

    /**
     * When walking multiple OIDs, stop if one fails. Used when the first OID indicates if the rest are supported.
     * OIDs will be walked in order, so you may want to put your OIDs in a specific order.
     */
    public function abortOnFailure(): SnmpQueryInterface
    {
        $this->abort = true;

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
    public function numeric(bool $numeric = true): SnmpQueryInterface
    {
        $this->options = $numeric
            ? array_merge($this->options, ['-On'])
            : array_diff($this->options, ['-On']);

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numericIndex(bool $numericIndex = true): SnmpQueryInterface
    {
        $this->options = $numericIndex
            ? array_merge($this->options, ['-Ob'])
            : array_diff($this->options, ['-Ob']);

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
        return $this->execMultiple('snmpget', $this->limitOids($this->parseOid($oid)));
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
        return $this->execMultiple('snmpwalk', $this->parseOid($oid));
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
        return $this->execMultiple('snmpgetnext', $this->limitOids($this->parseOid($oid)));
    }

    /**
     * Translate an OID.
     * call numeric() on the query to output numeric OID
     */
    public function translate(string $oid): string
    {
        $this->options = array_diff($this->options, [self::DEFAULT_FLAGS]); // remove default options

        $this->options[] = '-Pu'; // don't error on _

        // user did not specify numeric, output full text
        if (! in_array('-On', $this->options)) {
            $this->options[] = '-OS';
        } elseif (Oid::isNumeric($oid)) {
            return Str::start($oid, '.'); // numeric to numeric optimization
        }

        // if mib is not directly specified and it doesn't have a numeric root
        if (! str_contains($oid, '::') && ! Oid::hasNumericRoot($oid)) {
            $this->options[] = '-IR'; // search for mib
        }

        return $this->exec('snmptranslate', [$oid])->value();
    }

    private function buildCli(string $command, array $oids): array
    {
        $cmd = $this->initCommand($command, $oids);

        array_push($cmd, '-M', $this->mibDirectories());
        array_push($cmd, '-m', implode(':', $this->mibs));

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

    private function execMultiple(string $command, array $oids): SnmpResponse
    {
        $response = new SnmpResponse('');

        foreach ($oids as $oid) {
            $response = $response->append($this->exec($command, Arr::wrap($oid)));

            // if abort on failure is set, return after first failure
            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', array_map(fn ($group) => is_array($group) ? implode(',', $group) : $group, $oids));
                Log::debug("SNMP failed walking $oid of $oid_list aborting.");

                return $response;
            }
        }

        return $response;
    }

    private function exec(string $command, array $oids): SnmpResponse
    {
        // use runtime(array) cache if requested. The 'null' driver will simply return the value without caching
        $driver = $this->cache ? 'array' : 'null';
        $key = $this->cache ? $this->getCacheKey($command, $oids) : '';

        return Cache::driver($driver)->rememberForever($key, function () use ($command, $oids) {
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
        });
    }

    private function initCommand(string $binary, array $oids): array
    {
        if ($binary == 'snmpwalk') {
            // allow unordered responses for specific oids
            if (! empty(array_intersect($oids, Config::getCombined($this->device->os, 'oids.unordered', 'snmp.')))) {
                $this->allowUnordered();
            }

            // handle bulk settings
            if ($this->device->snmpver !== 'v1'
                && Config::getOsSetting($this->device->os, 'snmp_bulk', true)
                && empty(array_intersect($oids, Config::getCombined($this->device->os, 'oids.no_bulk', 'snmp.'))) // skip for oids that do not work with bulk
            ) {
                $snmpcmd = [Config::get('snmpbulkwalk', 'snmpbulkwalk')];

                $max_repeaters = $this->device->getAttrib('snmp_max_repeaters') ?: Config::getOsSetting($this->device->os, 'snmp.max_repeaters', Config::get('snmp.max_repeaters', false));
                if ($max_repeaters > 0) {
                    $snmpcmd[] = "-Cr$max_repeaters";
                }

                return $snmpcmd;
            }
        }

        return [Config::get($binary, $binary)];
    }

    private function mibDirectories(): string
    {
        $base = Config::get('mib_dir');
        $dirs = [$base];

        // os group
        if ($os_group = Config::getOsSetting($this->device->os, 'group')) {
            if (file_exists("$base/$os_group")) {
                $dirs[] = "$base/$os_group";
            }
        }

        // os directory
        $os_mibdir = Config::getOsSetting($this->device->os, 'mib_dir');
        if ($os_mibdir && is_string($os_mibdir)) {
            $dirs[] = "$base/$os_mibdir";
        } elseif (file_exists($base . '/' . $this->device->os)) {
            $dirs[] = $base . '/' . $this->device->os;
        }

        foreach ($this->mibDirs as $mibDir) {
            $dirs[] = "$base/$mibDir";
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

    private function limitOids(array $oids): array
    {
        // get max oids per query device attrib > os setting > global setting
        $configured_max = $this->device->getAttrib('snmp_max_oid') ?: Config::getOsSetting($this->device->os, 'snmp_max_oid', Config::get('snmp.max_oid', 10));
        $max_oids = max($configured_max, 1); // 0 or less would break things.

        if (count($oids) > $max_oids) {
            return array_chunk($oids, $max_oids);
        }

        return [$oids]; // wrap in array for execMultiple so they are all done at once
    }

    private function parseOid(array|string $oid): array
    {
        return is_string($oid) ? explode(' ', $oid) : $oid;
    }

    private function getCacheKey(string $type, array $oids): string
    {
        $oids = implode(',', $oids);
        $options = implode(',', $this->options);

        return "$type|{$this->device->hostname}|$this->context|$oids|$options";
    }
}
