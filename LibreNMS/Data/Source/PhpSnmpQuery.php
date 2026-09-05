<?php

/*
 * PhpSnmpQuery.php
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

use App\Events\SnmpQueryExecuted;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Polling\Measure\Measurement;
use DeviceCache;
use LibreNMS\Util\Mib;
use LibreNMS\Util\Oid;
use LibreNMS\Util\Rewrite;
use Log;

class PhpSnmpQuery implements SnmpQueryInterface
{
    /** @var string[] */
    private array $mibDirs = [];
    private PhpSnmpOptions $options;
    /** @var string[] */
    private array $mibs = [];
    private Device $device;
    private bool $abort = false;
    private \SNMP $snmp;
    private bool $mibinit = false;
    private bool $cache = false;

    public function __construct()
    {
        $this->options = new PhpSnmpOptions();
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
        $this->device = $device;

        // Fall back to NetSnmp if new device is incompatible with PhpSnmp
        if (! self::worksFor($device)) {
            return $this->netsnmp();
        }

        $this->snmp = new \SNMP(
            $this->snmpver(),
            $this->hostname() . ':' . $this->device->port,
            $this->device->snmpver === 'v3' ? ($this->device->authname ?: 'root') : ($this->device->community ?: 'public'),
            ($this->device->timeout ?? LibrenmsConfig::get('snmp.timeout')) * 1000000,
            $this->device->retries ?? LibrenmsConfig::get('snmp.retries'),
        );

        if ($this->device->snmpver === 'v3') {
            $this->snmp->setSecurity(...self::getSecurityOptions($this->device, null));
        }

        // Set SNMP options for the new SNMP object
        $this->options->setOptions($this->snmp);

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
                $this->snmp->setSecurity(...self::getSecurityOptions($this->device, $v3_prefix . $context));
            } else {
                $this->snmp = new \SNMP(
                    $this->snmpver(),
                    $this->hostname() . ':' . $this->device->port,
                    "{$this->device->community}@$context",
                    ($this->device->timeout ?? LibrenmsConfig::get('snmp.timeout')) * 1000000,
                    $this->device->retries ?? LibrenmsConfig::get('snmp.retries'),
                );

                // Set SNMP options for the new SNMP object
                $this->options->setOptions($this->snmp);
            }
        } else {
            if ($this->device->snmpver === 'v3') {
                $this->snmp->setSecurity(...self::getSecurityOptions($this->device, null));
            } else {
                $this->snmp = new \SNMP(
                    $this->snmpver(),
                    $this->hostname() . ':' . $this->device->port,
                    $this->device->community ?: 'public',
                    ($this->device->timeout ?? LibrenmsConfig::get('snmp.timeout')) * 1000000,
                    $this->device->retries ?? LibrenmsConfig::get('snmp.retries'),
                );

                // Set SNMP options for the new SNMP object
                $this->options->setOptions($this->snmp);
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
        $this->mibinit = false;

        return $this;
    }

    /**
     * Set MIBs to use for this query. Base mibs are included by default.
     * They will be appended to existing mibs unless $append is set to false.
     *
     * @param  string[]  $mibs
     */
    public function mibs(array $mibs, bool $append = true): SnmpQueryInterface
    {
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
     *
     * @param  string[]  $mibs
     */
    private function readMibs(array $mibs): void
    {
        foreach ($mibs as $mib) {
            foreach (explode(':', Mib::mibDirectories($this->device, $this->mibDirs)) as $dir) {
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
        $this->abort = true;

        return $this;
    }

    /**
     * Do not error on out of order indexes.
     * Use with caution as we could get stuck in an infinite loop.
     */
    public function allowUnordered(): SnmpQueryInterface
    {
        $this->options->oid_increasing_check = false;
        $this->snmp->oid_increasing_check = false;

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numeric(bool $numeric = true): SnmpQueryInterface
    {
        $this->options->oid_output_format = ($numeric ? \Snmp\OidOutput::Numeric : \Snmp\OidOutput::Module); /** @phpstan-ignore class.notFound, class.notFound */
        $this->snmp->setOidOutputFormat($numeric ? \Snmp\OidOutput::Numeric : \Snmp\OidOutput::Module); /** @phpstan-ignore method.notFound, class.notFound, class.notFound */

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numericIndex(bool $numericIndex = true): SnmpQueryInterface
    {
        $this->options->numeric_index = $this->snmp->numeric_index = true;  /** @phpstan-ignore property.notFound */

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface
    {
        $this->options->oid_output_format = \Snmp\OidOutput::Suffix; /** @phpstan-ignore class.notFound */
        $this->snmp->setOidOutputFormat(\Snmp\OidOutput::Suffix); /** @phpstan-ignore method.notFound, class.notFound */

        return $this;
    }

    /**
     * Output enum values as strings instead of values. This could affect index output.
     */
    public function enumStrings(): SnmpQueryInterface
    {
        $this->options->enum_print = $this->snmp->enum_print = false;

        return $this;
    }

    private function initMibs(): void
    {
        // Do nothing if we are already initialised and nothing has changed
        if ($this->mibinit) {
            return;
        }

        snmp_set_mib_option(\Snmp\Mib::AllowUnderscores, true); /** @phpstan-ignore function.notFound, class.notFound */
        snmp_init_mib(Mib::mibDirectories($this->device, $this->mibDirs)); /** @phpstan-ignore function.notFound */
        $this->readMibs($this->mibs);

        $this->mibinit = true;
    }

    /**
     * Set option(s) based on net-snmp command line options. Overrides the default options.
     * Try to avoid setting options this way to keep the API generic.
     *
     * @param  string[]|string|null  $options
     */
    public function options($options = []): SnmpQueryInterface
    {
        $this->options->setOptions($options);

        return $this;
    }

    /**
     * snmpget an OID
     * Commonly used to fetch a single or multiple explicit values.
     *
     * @param  string[]|string  $oid
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
     * @param  string[]|string  $oid
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
     * @param  string[]|string  $oid
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

    /**
     * @param  string[]|string  $oids
     */
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

        $this_response = new SnmpResponse($res_str, $errors, $errors ? 1 : 0);

        event(new SnmpQueryExecuted(
            method: $cmd,
            oids: (is_array($oids) ? $oids : [$oids]),
            cliCommand: [],
            response: $this_response,
            device: $this->device,
            mibs: $this->mibs,
            mibDir: implode(':', $this->mibDirs),
        ));

        return $response->append($this_response);
    }

    /**
     * @param  string[]  $oids
     * @return list<list<string>>
     */
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

    /**
     * @param  string[]|string  $oids
     * @return string[]
     */
    private function parseOid(array|string $oids): array
    {
        return is_string($oids) ? explode(' ', $oids) : $oids;
    }

    /**
     * @return string[]
     */
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

    private function hostname(): string
    {
        return Rewrite::addIpv6Brackets((string) ($this->device->overwrite_ip ?: $this->device->hostname)) ?: 'localhost';
    }

    private function snmpver(): int
    {
        return match ($this->device->snmpver) {
            'v1' => \SNMP::VERSION_1,
            'v2c' => \SNMP::VERSION_2c,
            'v3' => \SNMP::VERSION_3,
            default => null,
        };
    }

    private function netsnmp(): SnmpQueryInterface
    {
        // TODO: set options, etc
        $ret = (new NetSnmpQuery())->device($this->device);

        if ($this->cache) {
            $ret->cache();
        }

        return $ret;
    }
}
