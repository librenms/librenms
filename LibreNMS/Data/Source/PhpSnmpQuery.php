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
use LibreNMS\Util\Debug;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Log;

class PhpSnmpQuery implements SnmpQueryInterface
{
    private string $output_regex = '/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/';
    private string $output_replacement = '*';

    private const DEFAULT_OPTIONS = [
        'oid_increasing_check' => true,
        'quick_print' => true,
        'enum_print' => true,
        'numeric_index' => false,
        'numeric_timeticks' => true,
        'extended_index' => true,
        'dont_print_units' => true,
        'escape_quotes' => false,
        'print_hex_text' => false,
        'string_output_format' => SNMP_STRING_OUTPUT_GUESS, /** @phpstan-ignore constant.notFound */
        'oid_output_format' => SNMP_OID_OUTPUT_MODULE,
    ];

    private const LIBRARY_DEFAULT_OPTIONS = [
        'oid_increasing_check' => false,
        'quick_print' => false,
        'enum_print' => false,
        'numeric_index' => false,
        'numeric_timeticks' => false,
        'extended_index' => false,
        'dont_print_units' => false,
        'escape_quotes' => false,
        'print_hex_text' => false,
        'string_output_format' => SNMP_STRING_OUTPUT_GUESS, /** @phpstan-ignore constant.notFound */
        'oid_output_format' => SNMP_OID_OUTPUT_MODULE,
    ];

    /**
     * @var string[]
     */
    private array $mibDirs = [];
    private array $options = self::DEFAULT_OPTIONS;
    private array $mibs = [];
    private Device $device;
    private bool $abort = false;
    private \SNMP $snmp;
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
        if (! self::worksFor($device)) {
            return $this->netsnmp;
        }

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
            $this->device->snmpver === 'v3' ? ($this->device->authname ?: 'root') : ($this->device->community ?: 'public'),
            ($this->device->timeout ?? LibrenmsConfig::get('snmp.timeout')) * 1000000,
            $this->device->retries ?? LibrenmsConfig::get('snmp.retries'),
        );

        if ($this->device->snmpver === 'v3') {
            $this->snmp->setSecurity(...self::getSecurityOptions($this->device, null));
        }

        // Set SNMP options for the new SNMP object
        $this->setOptions();

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
                // php-snmp doesn't support changing community.  Fall back to NetSnmp
                return $this->netsnmp;
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

        if ($this->mibinit) {
            if ($append) {
                // Read new MIBs immediately if we are initialised and appending
                $this->readMibs($mibs);
            } else {
                // Reset the mibinit flag if we need to reset the MIBs to the new list
                $this->mibinit = false;
            }
        }

        return $this;
    }

    /**
     * Read in a group of MIB files
     */
    private function readMibs(array $mibs): void
    {
        foreach ($mibs as $mib) {
            foreach ($this->mibDirectories() as $dir) {
                $mibfile = "$dir/$mib";
                if (file_exists($mibfile)) {
                    snmp_read_mib($mibfile);

                    return;
                }
            }
        }
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

        $this->options['oid_increasing_check'] = false;
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

        $this->options['oid_output_format'] = ($numeric ? SNMP_OID_OUTPUT_NUMERIC : SNMP_OID_OUTPUT_MODULE);
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

        $this->options['numeric_index'] = true;
        $this->snmp->numeric_index = true;  /** @phpstan-ignore property.notFound */

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface
    {
        // Update NetSnmp object in case we need to switch
        $this->netsnmp->hideMib();

        $this->options['oid_output_format'] = SNMP_OID_OUTPUT_SUFFIX;
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

        $this->options['enum_print'] = false;
        $this->snmp->enum_print = false;

        return $this;
    }

    private function initMibs(): void
    {
        // Do nothing if we are already initialised and nothing has changed
        if ($this->mibinit) {
            return;
        }

        snmp_set_mib_option(SNMP_MIB_ALLOW_UNDERSCORES, true); /** @phpstan-ignore function.notFound, constant.notFound */
        snmp_init_mib(implode(':', $this->mibDirectories())); /** @phpstan-ignore function.notFound */
        $this->readMibs($this->mibs);

        $this->mibinit = true;
    }

    /**
     * Set option(s) for net-snmp command line. Overrides the default options.
     * Some options may break parsing, but you can manually parse the raw output if needed.
     * This will override other options set such as setting numeric.
     * Calling with null will reset to the default options (-OQXUte).
     * Try to avoid setting options this way to keep the API generic.
     *
     * @param  array|string|null  $options
     */
    public function options($options = []): SnmpQueryInterface
    {
        // Update NetSnmp object
        $this->netsnmp->options($options);

        if (is_null($options)) {
            $this->options = self::DEFAULT_OPTIONS;

            return $this->setOptions();
        }

        if (is_string($options)) {
            $options = [$options];
        }

        // Reset all options to library defaults
        $this->options = self::LIBRARY_DEFAULT_OPTIONS;

        // Parse options, returning the NetSnmp object if we come across an unknown option
        foreach ($options as $option) {
            if ($option === '-Ci') {
                $this->options['oid_increasing_check'] = false;
            } elseif ($option === '-Pu') {
                // Do nothing - we always accept underscores in MIBs
            } elseif ($option === '-Ih') {
                // Ignore input options for GET requests
            } elseif (str_starts_with((string) $option, '-O')) {
                foreach (str_split(substr((string) $option, 2)) as $outopt) {
                    switch ($outopt) {
                        case 'a':
                            $this->options['string_output_format'] = SNMP_STRING_OUTPUT_ASCII; /** @phpstan-ignore constant.notFound */
                            break;
                        case 'x':
                            $this->options['string_output_format'] = SNMP_STRING_OUTPUT_HEX; /** @phpstan-ignore constant.notFound */
                            break;
                        case 'f':
                            $this->options['oid_output_format'] = SNMP_OID_OUTPUT_FULL;
                            break;
                        case 's':
                            $this->options['oid_output_format'] = SNMP_OID_OUTPUT_SUFFIX;
                            break;
                        case 'S':
                            $this->options['oid_output_format'] = SNMP_OID_OUTPUT_MODULE;
                            break;
                        case 'u':
                            $this->options['oid_output_format'] = SNMP_OID_OUTPUT_UCD;
                            break;
                        case 'n':
                            $this->options['oid_output_format'] = SNMP_OID_OUTPUT_NUMERIC;
                            break;
                        case 'b':
                            $this->options['numeric_index'] = true;
                            break;
                        case 'e':
                            $this->options['enum_print'] = true;
                            break;
                        case 'E':
                            $this->options['escape_quotes'] = true;
                            break;
                        case 'Q':
                            $this->options['quick_print'] = true;
                            break;
                        case 't':
                            $this->options['numeric_timeticks'] = true;
                            break;
                        case 'T':
                            $this->options['print_hex_text'] = true;
                            break;
                        case 'U':
                            $this->options['dont_print_units'] = true;
                            break;
                        case 'X':
                            $this->options['extended_index'] = true;
                            break;
                        default:
                            // We do not know how to parse this option - return the NetSnmp object
                            Log::debug("Unknown option -C$outopt : Falling back to NetSnmp");

                            return $this->netsnmp;
                    }
                }
            } else {
                Log::debug("Unknown option $option : Falling back to NetSnmp");

                // We do not know how to parse this option - return the NetSnmp object
                return $this->netsnmp;
            }
        }

        return $this->setOptions();
    }

    /**
     * Set the SNMP object to the configured options
     */
    private function setOptions(): SnmpQueryInterface
    {
        foreach ($this->options as $prop => $val) {
            $this->snmp->$prop = $val;
        }

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
