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
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Facades\Cache;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Log;

class PhpSnmpQuery implements SnmpQueryInterface
{
    private string $output_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
    private string $output_replacement = '*';

    /**
     * @var string[]
     */
    private array $mibDirs = [];
    private array $mibs = [];
    private Device $device;
    private bool $abort = false;
    private \SNMP $snmp;
    private bool $snmpinit = false;
    private bool $mibinit = false;
    private readonly NetSnmpQuery $netsnmp;

    public function __construct()
    {
        $this->netsnmp = new NetSnmpQuery();

        $this->device(DeviceCache::getPrimary());
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
        // Update NetSnmp object in case we need to pivot to it
        $this->netsnmp->device($device);

        // Fall back to NetSnmp if new device is incompatible with PhpSnmp
        if (! $this->worksFor($device)) {
            return $this->netsnmp;
        }

        $old_snmp = $this->snmpinit ? $this->snmp : null;
        $this->device = $device;

        $snmpver = match ($this->device->snmpver) {
            'v1' => \SNMP::VERSION_1,
            'v2c' => \SNMP::VERSION_2c,
            'v3' => \SNMP::VERSION_3,
            default => null,
        };
        $hostname = Rewrite::addIpv6Brackets((string) ($this->device->overwrite_ip ?: $this->device->hostname));

        $this->snmp = new \SNMP(
            $snmpver,
            ($hostname ?: 'localhost') . ':' . $this->device->port,
            $this->device->snmpver === 'v3' ? ($this->device->authname ?: 'root') : $this->device->community,
            ($this->device->timeout ?? LibrenmsConfig::get('snmp.timeout')) * 1000000,
            $this->device->retries ?? LibrenmsConfig::get('snmp.retries'),
        );
        $this->snmp->quick_print = true;
        $this->snmp->valueretrieval = SNMP_VALUE_PLAIN;

        if ($this->device->snmpver === 'v3') {
            $this->snmp->setSecurity(...self::getSecurityOptions($this->device, null));
        }

        // Copy settings from old SNMP object
        if ($old_snmp) {
            $this->snmp->oid_increasing_check = $old_snmp->oid_increasing_check;
            $this->snmp->enum_print = $old_snmp->enum_print;
            $this->snmp->numeric_timeticks = $old_snmp->numeric_timeticks; /** @phpstan-ignore property.notFound, property.notFound */
            $this->snmp->extended_index = $old_snmp->extended_index;  /** @phpstan-ignore property.notFound, property.notFound */
            $this->snmp->dontprint_units = $old_snmp->dontprint_units;  /** @phpstan-ignore property.notFound, property.notFound */
            if ($old_snmp->oid_output_format) {
                $this->snmp->oid_output_format = $old_snmp->oid_output_format;
            }
        } else {
            $this->snmp->oid_increasing_check = true;
            $this->snmp->enum_print = true;
            $this->snmp->numeric_timeticks = true;  /** @phpstan-ignore property.notFound */
            $this->snmp->extended_index = true;  /** @phpstan-ignore property.notFound */
            $this->snmp->dontprint_units = true;  /** @phpstan-ignore property.notFound */
        }

        // Make sure we copy settings if the device changes in future
        $this->snmpinit = true;

        return $this;
    }

    /**
     * Specify a device by a device array.
     * The device will be fetched from the cache if it is loaded, otherwise, it will fill the array into a new Device
     */
    public function deviceArray(array $device): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->deviceArray($device);

        if (isset($device['device_id']) && DeviceCache::has($device['device_id'])) {
            $this->device = DeviceCache::get($device['device_id']);

            return $this;
        }

        $this->device = new Device($device);

        return $this;
    }

    public function cache(): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->cache();

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
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->context($context, $v3_prefix);

        if ($context) {
            if ($this->device->snmpver === 'v3') {
                $this->snmp->setSecurity(...self::getSecurityOptions($this->device, $v3_prefix . $context));
            } else {
                // Cannot change context for community based
                throw new \Exception('Cannot change context for v1/v2c');
            }
        } else {
            if ($this->device->snmpver === 'v3') {
                $this->snmp->setSecurity(...self::getSecurityOptions($this->device, null));
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
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->mibDir($dir);

        $this->mibDirs[] = $dir;
        $this->mibinit = false;

        return $this;
    }

    /**
     * Set MIBs to use for this query. Base mibs are included by default.
     * They will be appended to existing mibs unless $append is set to false.
     */
    public function mibs(array $mibs, bool $append = true): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->mibs($mibs, $append);

        $this->mibs = array_merge($this->mibs, $mibs);

        // Read it in immediately if we are initialised
        if ($this->mibinit) {
            foreach ($mibs as $mib) {
                snmp_read_mib($mib);
            }
        }

        return $this;
    }

    /**
     * When walking multiple OIDs, stop if one fails. Used when the first OID indicates if the rest are supported.
     * OIDs will be walked in order, so you may want to put your OIDs in a specific order.
     */
    public function abortOnFailure(): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->abortOnFailure();

        $this->abort = true;

        return $this;
    }

    /**
     * Do not error on out of order indexes.
     * Use with caution as we could get stuck in an infinite loop.
     */
    public function allowUnordered(): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->allowUnordered();

        $this->snmp->oid_increasing_check = false;

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numeric(bool $numeric = true): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->numeric();

        $this->snmp->oid_output_format = ($numeric ? SNMP_OID_OUTPUT_NUMERIC : SNMP_OID_OUTPUT_MODULE);

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numericIndex(bool $numericIndex = true): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->numericIndex();

        // This is the default output, so set the same either way
        $this->snmp->oid_output_format = SNMP_OID_OUTPUT_MODULE;

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->hideMib();

        $this->snmp->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;

        return $this;
    }

    /**
     * Output enum values as strings instead of values. This could affect index output.
     */
    public function enumStrings(): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->enumStrings();

        $this->snmp->enum_print = false;

        return $this;
    }

    private function initMibs(): void
    {
        // Do nothing if we are already initialised and nothing has changed
        if ($this->mibinit) {
            return;
        }

        snmp_mib_allow_underscores(true); /** @phpstan-ignore function.notFound */
        snmp_init_mib(implode(':', $this->mibDirectories())); /** @phpstan-ignore function.notFound */
        foreach ($this->mibs as $mib) {
            snmp_read_mib($mib);
        }

        $this->mibinit = true;
    }

    /**
     * Set option(s) for net-snmp command line. Overrides the default options.
     * Some options may break parsing, but you can manually parse the raw output if needed.
     * This will override other options set such as setting numeric.
     * Calling with null will reset to the default options (-OQXUte).
     * Try to avoid setting options this way to keep the API generic.
     */
    public function options($options = []): SnmpQueryInterface
    {
        // Return NetSnmp object if options are set
        return $this->netsnmp->options($options);
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
        $response = new SnmpResponse('');

        foreach ($this->limitOids($this->parseOid($oid)) as $oids) {
            $response = $this->cmd('get', $oids, $response);

            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', array_map(fn ($group) => is_array($group) ? implode(',', $group) : $group, $oids));
                Log::info("SNMP failed getting $oid_list aborting.");

                return $response;
            }
        }

        return $response;
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
        $response = new SnmpResponse('');

        $oids = $this->parseOid($oid);
        $ret = [];

        foreach ($oids as $oid) {
            $response = $this->cmd('walk', $oid, $response);

            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', array_map(fn ($group) => is_array($group) ? implode(',', $group) : $group, $oids));
                Log::info("SNMP failed getting $oid_list aborting.");

                return $response;
            }
        }

        return $response;
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
        $response = new SnmpResponse('');

        foreach ($this->limitOids($this->parseOid($oid)) as $oids) {
            $response = $this->cmd('getnext', $oids, $response);

            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', array_map(fn ($group) => is_array($group) ? implode(',', $group) : $group, $oids));
                Log::info("SNMP failed getting $oid_list aborting.");

                return $response;
            }
        }

        return $response;
    }

    public function cmd(string $cmd, array|string $oids, SnmpResponse $response): SnmpResponse
    {
        $this->initMibs();

        $missing = [];
        $errors = '';

        $max_repeaters = $this->device->getAttrib('snmp_max_repeaters') ?: LibrenmsConfig::getOsSetting($this->device->os, 'snmp.max_repeaters', LibrenmsConfig::get('snmp.max_repeaters', false));

        set_error_handler(function (int $err_severity, string $err_msg, string $err_filename, int $err_line) use (&$missing, &$errors): bool {
            if (preg_match('/\'([^\']+)\': (No Such Object available on this agent at this OID|No Such Instance currently exists at this OID)/', $err_msg, $matches)) {
                $missing[$matches[1]] = $matches[2];
            } elseif (preg_match('/Invalid object identifier: (\S+)/', $err_msg, $matches)) {
                $errors .= "$matches[1]: Unknown Object Identifier\n";
            } else {
                $errors .= "$err_msg\n";
            }

            return true;
        }, E_WARNING);

        $this->logSnmpCmd($cmd, is_array($oids) ? $oids : [$oids]);
        $measure = Measurement::start('php' . $cmd);
        $res = match ($cmd) {
            'get' => $this->snmp->get($oids),
            'getnext' => $this->snmp->getnext($oids),
            'walk' => $this->snmp->walk($oids, false, $max_repeaters > 0 ? $max_repeaters : 10, 0),
            default => throw new \Exception("SNMP comand $cmd is not supported"),
        };
        $measure->manager()->recordSnmp($measure->end());

        restore_error_handler();

        $res_str = '';
        if ($res) {
            foreach ($res as $k => $v) {
                $res_str .= "$k = $v\n";
            }
        }
        foreach ($missing as $k => $v) {
            $res_str .= "$k = $v\n";
        }

        $this->logOutput($res_str, '');

        return $response->append(new SnmpResponse($res_str, $errors, $errors ? 1 : 0));
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

    private function parseOid(array|string $oids): array
    {
        return is_string($oids) ? explode(' ', $oids) : $oids;
    }

    private static function getSecurityOptions(Device $device, ?string $context): array
    {
        if ($device->authlevel === 'authpriv') {
            $options[] = 'authPriv';
            $options[] = $device->authalgo;
            $options[] = $device->authpass;
            $options[] = $device->cryptoalgo;
            $options[] = $device->cryptopass;
            $options[] = $context ?: '';
        } elseif ($device->authlevel === 'authnopriv') {
            $options[] = 'authNoPriv';
            $options[] = $device->authalgo;
            $options[] = $device->authpass;
            $options[] = '';
            $options[] = '';
            $options[] = $context ?: '';
        } else {
            $options[] = 'noAuthNoPriv';
            $options[] = '';
            $options[] = '';
            $options[] = '';
            $options[] = '';
            $options[] = $context ?: '';
        }

        return $options;
    }

    private function logSnmpCmd(string $cmd, array $oids): void
    {
        if (Debug::isVerbose()) {
            Log::debug("SNMP $cmd - MIBS: " . implode(':', array_keys($this->mibs)) . ' OIDS: ' . implode(' ', $oids));
        } else {
            Log::debug("SNMP $cmd - OIDS: " . implode(' ', $oids));
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

    public static function worksFor(Device $device): bool
    {
        if (! class_exists('\SNMP')) {
            return false;
        }

        // We need to be able to reset MIBs
        if (! function_exists('snmp_init_mib')) {
            return false;
        }

        if (($device->transport ?? 'udp') !== 'udp') {
            return false;
        }

        if ($device->snmpver == 'v3') {
            // Dummy SNMP object to check security settings are supported
            $snmp = new \SNMP(\SNMP::VERSION_3, 'localhost', 'root', 1000000, 3);
            try {
                $snmp->setSecurity(...self::getSecurityOptions($device, null));
            } catch (\Exception) {
                return false;
            }
        }

        return true;
    }
}
