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

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Log;
use Symfony\Component\Process\Process;

class PhpSnmpQuery implements SnmpQueryInterface
{
    private string $log_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
    private string $log_replacement = '*';

    /**
     * @var string[]
     */
    private array $mibFilesLoaded = [];
    private array $mibDirs = [];
    private array $mibs = ['SNMPv2-SMI', 'SNMPv2-TC', 'SNMPv2-CONF', 'SNMPv2-MIB', 'IANAifType-MIB', 'IF-MIB', 'IP-MIB', 'TCP-MIB', 'UDP-MIB', 'NET-SNMP-VACM-MIB'];
    private Device $device;
    private bool $abort = false;
    private bool $cache = false;
    private \SNMP $snmp;
    private NetSnmpQuery $netsnmp;

    public function __construct()
    {
        Log::debug("New PHP-SNMP");
        $this->device = DeviceCache::getPrimary();

        $snmpver = match($this->device->snmpver) {
            'v1' => \SNMP::VERSION_1,
            'v2c' => \SNMP::VERSION_2c,
            'v3' => \SNMP::VERSION_3,
            default => null,
        };
        $this->snmp = new \SNMP(
            $snmpver,
            $this->device->hostname,
            $this->device->snmpver === 'v3' ? ($this->device->authname ?: 'root') : $this->device->community,
            ($this->device->timeout ?? LibrenmsConfig::get('snmp.timeout')) * 1000,
            $this->device->retries ?? LibrenmsConfig::get('snmp.retries'),
        );
        $this->snmp->quick_print = true;
        $this->snmp->valueretrieval = SNMP_VALUE_PLAIN;
        $this->snmp->exceptions_enabled = \SNMP::ERRNO_ANY;

        if ($this->device->snmpver === 'v3') {
            $this->setSecurity(null);
        }

        $this->loadMibs();
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
     * @param  string  $context  Version 2/3 context name
     * @param  string|null  $v3_prefix  Optional context prefix to prepend for Version 3 queries
     * @return SnmpQueryInterface
     */
    public function context(string $context, ?string $v3_prefix = null): SnmpQueryInterface
    {
        if ($context) {
            if ($this->device->snmpver === 'v3') {
                $this->setSecurity($v3_prefix . $context);
            } else {
                // Cannot change context for community based 
                throw new \Exception('Cannot change context for v1/v2c');
            }
        } else {
            if ($this->device->snmpver === 'v3') {
                $this->setSecurity(null);
            }
        }

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
        $this->mibs = array_merge($this->mibs, $mibs);
        $this->loadMibs();

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
        $this->snmp->oid_increasing_check = false;

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numeric(bool $numeric = true): SnmpQueryInterface
    {
        $this->snmp->oid_output_format = ($numeric ? SNMP_OID_OUTPUT_NUMERIC : SNMP_OID_OUTPUT_MODULE);

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numericIndex(bool $numericIndex = true): SnmpQueryInterface
    {
        // This is the default output, so set the same either way
        $this->snmp->oid_output_format = SNMP_OID_OUTPUT_MODULE;

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface
    {
        $this->snmp->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;

        return $this;
    }

    /**
     * Output enum values as strings instead of values. This could affect index output.
     */
    public function enumStrings(): SnmpQueryInterface
    {
        $this->snmp->enum_print = false;

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
        throw new \Exception('PHP-SNMP does not support raw options - we should fall back to NetSnmpQuery');

        return $this;
    }

    /**
     * snmpget an OID
     * Commonly used to fetch a single or multiple explicit values.
     *
     * @param  array|string  $oid
     * @return SnmpResponse
     */
    public function get($oid): SnmpResponse
    {
        $ret = [];
        foreach ($this->limitOids($this->parseOid($oid)) as $oids) {
            $measure = Measurement::start('snmpget');
            Log::debug('SNMP GET: ' . implode(' ', $oids));
            try {
                $res = $this->snmp->get($oids);
            } catch (\SNMPException $e) {
                Log::debug('SNMP error: ' . $e->getMessage());
                return new SnmpResponse($e->getMessage(), $e->getMessage(), 1);
            }
            if ($res) {
                $ret = array_merge($ret, $res);
            } else {
                Log::debug('Error in SNMP get for OIDS: "' . implode('","', $oids) . '"');
            }
            $measure->manager()->recordSnmp($measure->end());
        }

        Log::debug($ret);

        return new SnmpResponse($ret);
    }

    /**
     * snmpwalk an OID
     * Fetches all OIDs under a given OID, commonly used with tables.
     *
     * @param  array|string  $oid
     * @return SnmpResponse
     */
    public function walk($oid): SnmpResponse
    {
        $measure = Measurement::start('snmpwalk');
        $oids = $this->parseOid($oid);
        Log::debug('SNMP WALK: ' . implode(' ', $oids));
        try {
            $ret = $this->snmp->walk($oids);
        } catch (\SNMPException $e) {
            Log::debug('SNMP error: ' . $e->getMessage());
            return new SnmpResponse($e->getMessage(), $e->getMessage(), 1);
        }
        $measure->manager()->recordSnmp($measure->end());

        Log::debug($ret);

        return new SnmpResponse($ret);
    }

    /**
     * snmpnext for the given oid
     * snmpnext retrieves the first oid after the given oid.
     *
     * @param  array|string  $oid
     * @return SnmpResponse
     */
    public function next($oid): SnmpResponse
    {
        $measure = Measurement::start('snmpget');
        foreach ($this->limitOids($this->parseOid($oid)) as $oids) {
            Log::debug('SNMP GETNEXT: ' . implode(' ', $oids));
            try {
                $res = $this->snmp->getnext($oids);
            } catch (\SNMPException $e) {
                Log::debug('SNMP error: ' . $e->getMessage());
                return new SnmpResponse($e->getMessage(), $e->getMessage(), 1);
            }
            if ($res) {
                $ret = array_merge($ret, $res);
            } else {
                Log::debug('Error in SNMP getnext for OIDS: "' . implode('","', $oids) . '"');
            }
        }
        $measure->manager()->recordSnmp($measure->end());

        Log::debug($ret);

        return new SnmpResponse($ret);
    }

    /**
     * Translate an OID using NetSnmp class.
     */
    public function translate(string $oid): string
    {
        return $this->netsnmp->translate($oid);
    }

    private function mibDirectories(): array
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

        return $dirs;
    }

    private function limitOids(array $oids): array
    {
        // get max oids per query device attrib > os setting > global setting
        $configured_max = $this->device->getAttrib('snmp_max_oid') ?: LibrenmsConfig::getOsSetting($this->device->os, 'snmp_max_oid', LibrenmsConfig::get('snmp.max_oid', 10));
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

    private function setSecurity(?string $context): void
    {
        $options = [$this->device->authlevel];
        if ($this->device->authlevel === 'authpriv') {
            $options[] = $this->device->authalgo;
            $options[] = $this->device->authpass;
            $options[] = $this->device->cryptoalgo;
            $options[] = $this->device->cryptopass;
            $options[] = $context ?: '';
        } elseif ($this->device->authlevel === 'authnopriv') {
            $options[] = $this->device->authalgo;
            $options[] = $this->device->authpass;
            $options[] = '';
            $options[] = '';
            $options[] = $context ?: '';
        } else {
            $options[] = '';
            $options[] = '';
            $options[] = '';
            $options[] = '';
            $options[] = $context ?: '';
        }
        $this->snmp->setSecurity($options);
    }

    private function loadMibs(): void
    {
        $mibdirs = $this->mibDirectories();
        foreach ($this->mibs as $mib) {
            $mibfound = false;

            foreach ($mibdirs as $dir) {
                $mibfile = $dir . '/' . $mib;
                if (isset($this->mibFilesLoaded[$mibfile])) {
                    $mibfound = true;
                    break;
                } elseif (file_exists($mibfile)) {
                    if(Debug::isVerbose()) {
                        Log::debug("Reading SNMP MIB $mibfile");
                    }

                    if (snmp_read_mib($mibfile)) {
                        $mibfound = true;
                        $this->mibFilesLoaded[$mibfile] = true;
                        break;
                    } else {
                        Log::debug("Failed to load SNMP MIB $mibfile");
                    }
                }
            }

            if (!$mibfound) {
                Log::debug("MIB $mib was not found");
            }
        }
    }
}
