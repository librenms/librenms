<?php

/*
 * SnmpQuery.php
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

use App\Events\SnmpQueryExecuted;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Polling\Measure\Measurement;
use DeviceCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpQueryOptions;
use LibreNMS\Data\Source\Snmp\SnmpTarget;
use LibreNMS\Data\Source\Snmp\SnmpTranslatorInterface;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Mib;
use LibreNMS\Util\Oid;
use Log;

class SnmpQuery implements SnmpQueryInterface
{
    private Device $device;
    private string $context = '';
    private readonly SnmpQueryOptions $options;
    private bool $abort = false;
    private bool $cache = false;
    private readonly SnmpBackendInterface $backend;
    private SnmpTranslatorInterface $translateBackend;

    public function __construct(
        ?SnmpBackendInterface $backend = null,
        ?SnmpTranslatorInterface $translateBackend = null,
        ?SnmpQueryOptions $options = null,
    ) {
        $this->backend = $backend ?? resolve(SnmpBackendInterface::class);

        if ($translateBackend !== null) {
            $this->translateBackend = $translateBackend;
        } elseif ($this->backend instanceof SnmpTranslatorInterface) {
            $this->translateBackend = $this->backend;
        } else {
            $this->translateBackend = resolve(SnmpTranslatorInterface::class);
        }

        $this->options = $options ?? new SnmpQueryOptions();
        $this->device = DeviceCache::getPrimary();
    }

    /**
     * Easy way to start a new instance
     */
    public static function make(): SnmpQueryInterface
    {
        return resolve(SnmpQueryInterface::class);
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
        if ($dir) {
            $this->options->mibDirs[] = $dir;
        }

        return $this;
    }

    /**
     * Set MIBs to use for this query. Base mibs are included by default.
     * They will be appended to existing mibs unless $append is set to false.
     */
    public function mibs(array $mibs, bool $append = true): SnmpQueryInterface
    {
        $this->options->mibs = $append ? array_merge($this->options->mibs, $mibs) : $mibs;

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
        $this->options->tolerateUnorderedIndexes = true;

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numeric(bool $numeric = true): SnmpQueryInterface
    {
        $this->options->numericOids = $numeric;

        return $this;
    }

    /**
     * Output all OIDs numerically
     */
    public function numericIndex(bool $numericIndex = true): SnmpQueryInterface
    {
        $this->options->numericIndexes = $numericIndex;

        return $this;
    }

    /**
     * Hide MIB in output
     */
    public function hideMib(): SnmpQueryInterface
    {
        $this->options->outputMibNames = false;

        return $this;
    }

    /**
     * Output enum values as strings instead of values. This could affect index output.
     */
    public function enumStrings(): SnmpQueryInterface
    {
        $this->options->numericEnums = false;

        return $this;
    }

    /**
     * Set option(s) for net-snmp command line. Converts net-snmp flags to SnmpQueryOptions.
     *
     * @param  array|string|null  $options
     * @return $this
     */
    public function options($options = []): SnmpQueryInterface
    {
        $this->options->parseCli($options ?: null);

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
        $target = $this->getTarget();
        $chunks = $this->limitOids($this->parseOid($oid), $target);
        $response = new SnmpResponse('');

        foreach ($chunks as $chunk) {
            $options = $this->prepareOptions($chunk);
            $res = $this->execWithCache('snmpget', $chunk, $options, fn () => $this->backend->get($target, $chunk, $options));
            $response = $response->append($res);

            // if abort on failure is set, return after first failure
            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', array_map(fn ($group) => is_array($group) ? implode(',', $group) : $group, $chunks));
                Log::debug('SNMP failed getting ' . implode(',', $chunk) . " of $oid_list aborting.");

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
        $target = $this->getTarget();
        $oids = $this->parseOid($oid);
        $response = new SnmpResponse('');

        foreach ($oids as $singleOid) {
            $options = $this->prepareOptions([$singleOid], walk: true);
            $res = $this->execWithCache('snmpwalk', [$singleOid], $options, fn () => $this->backend->walk($target, $singleOid, $options));
            $response = $response->append($res);

            // if abort on failure is set, return after first failure
            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', $oids);
                Log::debug("SNMP failed walking $singleOid of $oid_list aborting.");

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
        $target = $this->getTarget();
        $chunks = $this->limitOids($this->parseOid($oid), $target);
        $response = new SnmpResponse('');

        foreach ($chunks as $chunk) {
            $options = $this->prepareOptions($chunk);
            $res = $this->execWithCache('snmpgetnext', $chunk, $options, fn () => $this->backend->next($target, $chunk, $options));
            $response = $response->append($res);

            // if abort on failure is set, return after first failure
            if ($this->abort && ! $response->isValid()) {
                $oid_list = implode(',', array_map(fn ($group) => is_array($group) ? implode(',', $group) : $group, $chunks));
                Log::debug('SNMP failed next on ' . implode(',', $chunk) . " of $oid_list aborting.");

                return $response;
            }
        }

        return $response;
    }

    /**
     * Translate an OID.
     * call numeric() on the query to output numeric OID
     */
    public function translate(string $oid): string
    {
        $oidObj = new Oid($oid);

        if ($this->options->numericOids && $oidObj->isNumeric()) {
            return Str::start($oid, '.'); // numeric to numeric optimization
        }

        $options = clone $this->options;
        $options->mibDirs = Mib::directories($this->device, $this->options->mibDirs);

        return $this->translateBackend->translate((string) $oid, $options);
    }

    private function getTarget(): SnmpTarget
    {
        return SnmpTarget::fromDevice($this->device);
    }

    /**
     * Prepare options for an execution chunk.
     *
     * Note: tolerateUnorderedIndexes and bulk are properties of the specific OID group being queried,
     * not request-wide constants, and must be resolved per OID chunk.
     */
    private function prepareOptions(array $oids, bool $walk = false): SnmpQueryOptions
    {
        $options = clone $this->options;
        $options->context = $this->context ?: (string) ($this->device->getAttribute('context_name') ?: $this->device->getAttribute('context') ?: '');
        $options->mibDirs = Mib::directories($this->device, $this->options->mibDirs);

        if ($walk) {
            if (! empty(array_intersect($oids, LibrenmsConfig::getCombined($this->device->os, 'oids.unordered', 'snmp.')))) {
                $options->tolerateUnorderedIndexes = true;
            }

            if (! LibrenmsConfig::getOsSetting($this->device->os, 'snmp_bulk', true)
                || ! empty(array_intersect($oids, LibrenmsConfig::getCombined($this->device->os, 'oids.no_bulk', 'snmp.')))
            ) {
                $options->allowBulk = false;
            }
        }

        return $options;
    }

    private function execWithCache(string $command, array $oids, SnmpQueryOptions $options, \Closure $callback): SnmpResponse
    {
        $execute = function () use ($command, $oids, $options, $callback): SnmpResponse {
            $measure = Measurement::start($command);

            $response = $callback();

            event(new SnmpQueryExecuted(
                method: $command,
                oids: $oids,
                response: $response,
                cliCommand: $response->command,
                device: $this->device,
                context: $options->context,
                mibs: $options->mibs,
                mibDir: implode(':', $options->mibDirs),
            ));

            $measure->manager()->recordSnmp($measure->end());

            return $response;
        };

        if (! $this->cache) {
            return $execute();
        }

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

        return Cache::driver($driver)->rememberForever($key, $execute);
    }

    private function limitOids(array $oids, SnmpTarget $target): array
    {
        $max_oids = max($target->config->maxOid, 1);

        if (count($oids) > $max_oids) {
            return array_chunk($oids, $max_oids);
        }

        return [$oids];
    }

    private function parseOid(array|string $oid): array
    {
        return is_string($oid) ? explode(' ', $oid) : $oid;
    }

    private function getCacheKey(string $type, array $oids): string
    {
        $oidsStr = implode(',', $oids);
        $optionsStr = implode(',', [
            (int) $this->options->numericOids,
            (int) $this->options->numericIndexes,
            (int) $this->options->outputMibNames,
            (int) $this->options->numericEnums,
            (int) $this->options->tolerateUnorderedIndexes,
            implode(';', $this->options->mibs),
            implode(';', $this->options->mibDirs),
        ]);

        return "$type|{$this->device->hostname}|{$this->device->community}|$this->context|$oidsStr|$optionsStr";
    }
}
