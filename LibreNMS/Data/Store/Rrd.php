<?php

/**
 * Rrd.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Eventlog;
use App\Polling\Measure\Measurement;
use Carbon\Carbon;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\Exceptions\RrdStoreException;
use LibreNMS\RRD\Backend\RrdBackendInterface;
use LibreNMS\RRD\RrdPath;
use LibreNMS\Util\Rewrite;
use Log;

class Rrd extends BaseDatastore
{
    private const FAILURE_THRESHOLD = 3;
    private const BACKOFF_BASE = 30; // seconds
    private const BACKOFF_MAX = 600; // seconds

    private int $consecutiveErrors = 0;
    private int $trips = 0;
    private int $suspendedUntil = 0;

    private ?RrdBackendInterface $backend = null;

    public function getName(): string
    {
        return 'RRD';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('rrd.enable', true);
    }

    /**
     * Resolved on first use, so requests that only build rrd paths don't start a process or connect
     */
    private function backend(): RrdBackendInterface
    {
        return $this->backend ??= resolve(RrdBackendInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        if ($this->disabled) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return;
        }

        $device_model = $this->getDevice($meta);

        $rrd_name = $meta['rrd_name'] ?? $measurement;
        if (! empty($meta['rrd_oldname'])) {
            self::renameFile($device_model, $meta['rrd_oldname'], $rrd_name);
        }

        if (isset($meta['rrd_proxmox_name'])) {
            $pmxvars = $meta['rrd_proxmox_name'];
            $rrd = self::proxmoxName($pmxvars['pmxcluster'], $pmxvars['vmid'], $pmxvars['vmport']);
            self::checkDirExists(RrdPath::proxmox($pmxvars['pmxcluster']));
        } else {
            $rrd = RrdPath::make($device_model->hostname, self::filenameString($rrd_name) . '.rrd');
        }

        if (isset($meta['rrd_def'])) {
            $rrd_def = $meta['rrd_def'];
            if (isset($meta['rrd_step'])) {
                $rrd_def = (clone $rrd_def)->setStep((int) $meta['rrd_step']);
            }

            foreach (array_keys($fields) as $key) {
                if (! $rrd_def->isValidDataset($key)) {
                    Log::debug("RRD warning: unused data sent $key");
                }
            }

            // values must be in the same order as the data sources in the file
            $values = $rrd_def->orderValues($fields);
        } else {
            $values = array_values($fields);
        }

        if ($this->isSuspended()) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return;
        }

        try {
            try {
                $this->update($rrd, $values);
            } catch (RrdNotFoundException) {
                if (isset($rrd_def)) {
                    $stat = Measurement::start('create');
                    $this->backend()->create($rrd, $rrd_def);
                    $this->recordStatistic($stat->end());
                    $this->update($rrd, $values);
                }
            }
            $this->recordSuccess($device_model);
        } catch (RrdStoreException $e) {
            Log::error('RRD Error %r' . $e->getMessage() . '%n', ['color' => true]);
            $this->recordStoreFailure($e, $device_model);
        } catch (RrdException $e) {
            Log::error('RRD Error %r' . $e->getMessage() . '%n', ['color' => true]);
        }
    }

    private function isSuspended(): bool
    {
        return Carbon::now()->getTimestamp() < $this->suspendedUntil;
    }

    private function recordSuccess(Device $device): void
    {
        if ($this->trips > 0) {
            Eventlog::log('RRD updates resumed', $device, 'rrd', Severity::Ok);
        }

        $this->consecutiveErrors = 0;
        $this->trips = 0;
    }

    private function recordStoreFailure(RrdStoreException $e, Device $device): void
    {
        // once tripped, a single failed retry re-trips with a longer backoff
        if (++$this->consecutiveErrors < self::FAILURE_THRESHOLD && $this->trips === 0) {
            return;
        }

        $backoff = min(self::BACKOFF_BASE * 2 ** $this->trips, self::BACKOFF_MAX);
        $this->trips++;
        $this->suspendedUntil = Carbon::now()->getTimestamp() + $backoff;

        Eventlog::log("RRD updates suspended for {$backoff}s, too many errors. Final error: " . $e->getMessage(), $device, 'rrd', Severity::Error);
    }

    /**
     * Updates an rrd database with the given values in data source order
     * Non-numeric values are stored as unknown
     *
     * @param  array<int|float|string|null>  $values
     * @param  int|null  $timestamp  unix time of the values, defaults to now
     *
     * @throws RrdException
     *
     * @internal
     */
    public function update(RrdPath $rrd, array $values, ?int $timestamp = null): void
    {
        if ($this->disabled) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return;
        }

        $stat = Measurement::start('update');
        $this->backend()->update($rrd, array_values($values), $timestamp);
        $this->recordStatistic($stat->end());
    }

    /**
     * Change the minimum and/or maximum values of data sources in an existing rrd file
     *
     * @param  array<string, array{min?: int|float|null, max?: int|float|null}>  $limits  data source name => limits
     * @return bool true if the file was tuned
     */
    public function tune(RrdPath $rrd, array $limits): bool
    {
        if ($this->disabled || empty($limits)) {
            return false;
        }

        $stat = Measurement::start('other');
        try {
            $this->backend()->tune($rrd, $limits);

            return true;
        } catch (RrdException $e) {
            if (! $e instanceof RrdNotFoundException) {
                Log::debug('RRD tune failed: ' . $e->getMessage());
            }

            return false;
        } finally {
            $this->recordStatistic($stat->end());
        }
    }

    /**
     * Generates a filename for a proxmox cluster rrd
     */
    public function proxmoxName(string $pmxcluster, string $vmid, string $vmport): RrdPath
    {
        return RrdPath::proxmox($pmxcluster, $vmid . '_netif_' . $vmport . '.rrd');
    }

    /**
     * Get the name of the port rrd file.  For alternate rrd, specify the suffix.
     */
    public function portName(int $port_id, ?string $suffix = null): string
    {
        return "port-id$port_id" . (empty($suffix) ? '' : '-' . $suffix);
    }

    /**
     * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
     *
     * @param  Device  $device  Device model
     * @param  string|string[]  $oldname  RRD name array as used with rrd_name()
     * @param  string|string[]  $newname  RRD name array as used with rrd_name()
     * @return bool indicating rename success or failure
     */
    public function renameFile(Device $device, $oldname, $newname): bool
    {
        $oldrrd = RrdPath::make($device->hostname, self::filenameString($oldname))->fullPath();
        $newrrd = RrdPath::make($device->hostname, self::filenameString($newname))->fullPath();
        if (is_file($oldrrd) && ! is_file($newrrd)) {
            if (rename($oldrrd, $newrrd)) {
                Eventlog::log("Renamed $oldrrd to $newrrd", $device, 'poller', Severity::Ok);

                return true;
            } else {
                Eventlog::log("Failed to rename $oldrrd to $newrrd", $device, 'poller', Severity::Error);

                return false;
            }
        } else {
            // we don't need to rename the file
            return true;
        }
    }

    /**
     * Public function to return the RRD filename for a given host and file
     */
    public function name(string $hostname, array|string $filename): RrdPath
    {
        return RrdPath::make($hostname, self::filenameString($filename) . '.rrd');
    }

    /**
     * Get array of all rrd files for a device
     *
     * @param  string|string[]  $prefix  limit returned results to files matching this prefix
     * @return string[] array of rrd files for this host, in the same form as RrdPath::defaultPath()
     */
    public function getRrdFiles(string $hostname, string|array $prefix = ''): array
    {
        $prefix = self::safeName(is_array($prefix) ? implode('-', $prefix) : $prefix);

        $stat = Measurement::start('other');
        $files = array_map(strval(...), $this->backend()->list($hostname, $prefix));
        $this->recordStatistic($stat->end());

        sort($files);

        return $files;
    }

    /**
     * Get array of rrd files for specific application.
     *
     * @param  array  $device  device for which we get the rrd's
     * @param  int  $app_id  application id on the device
     * @param  string  $app_name  name of app to be searched
     * @param  string  $category  which category of graphs are searched
     * @return array array of rrd files for this host
     */
    public function getRrdApplicationArrays($device, $app_id, $app_name, $category = null): array
    {
        $entries = [];
        $separator = '-';

        $rrdfile_array = $this->getRrdFiles($device['hostname']);
        if ($category) {
            $pattern = sprintf('%s-%s-%s-%s', 'app', $app_name, $app_id, $category);
        } else {
            $pattern = sprintf('%s-%s-%s', 'app', $app_name, $app_id);
        }

        // app_name contains a separator character? consider it
        $offset = substr_count($app_name, $separator);

        foreach ($rrdfile_array as $rrd) {
            if (str_contains((string) $rrd, $pattern)) {
                $filename = basename((string) $rrd, '.rrd');
                $entry = explode($separator, $filename, 4 + $offset)[3 + $offset];
                if ($entry) {
                    array_push($entries, $entry);
                }
            }
        }

        return $entries;
    }

    /**
     * Checks if the rrd file exists on the server
     */
    public function checkRrdExists(RrdPath $rrdpath): bool
    {
        if (! $rrdpath->usesRemoteCached()) {
            return is_file($rrdpath->fullPath());
        }

        $stat = Measurement::start('other');
        try {
            return is_int($this->backend()->last($rrdpath));
        } catch (RrdNotFoundException) {
            return false;
        } finally {
            $this->recordStatistic($stat->end());
        }
    }

    /**
     * Make sure the rrd directory exists locally.
     * rrdcached does not create directories, so this is attempted even when rrdcached is in use.
     * With a remote rrdcached, failure is not an error.
     */
    public static function checkDirExists(RrdPath $rrdpath): bool
    {
        $rrd_dir = $rrdpath->fullPath();
        if (is_dir($rrd_dir)) {
            return true;
        }

        if (@mkdir($rrd_dir, 0775, true) || is_dir($rrd_dir)) {
            Log::info("Created directory : $rrd_dir");

            return true;
        }

        if (LibrenmsConfig::get('rrdcached')) {
            Log::debug("Could not create local rrd directory: $rrd_dir");

            return true;
        }

        Log::error("Failed to create rrd directory: $rrd_dir");

        return false;
    }

    /**
     * Remove RRD file(s).  Use with care as this permanently deletes rrd data.
     *
     * @param  string|null  $hostname  rrd subfolder (hostname)
     * @param  string  $prefix  start of rrd file name all files matching will be deleted
     */
    public function purge(?string $hostname, string $prefix): void
    {
        if (empty($hostname)) {
            Log::error("Could not purge rrd $prefix, empty hostname");

            return;
        }

        foreach (glob(RrdPath::make($hostname, $prefix)->fullPath() . '*.rrd') as $rrd) {
            unlink($rrd);
        }
    }

    /**
     * Remove invalid characters from the rrd file name
     */
    public static function safeName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
    }

    /**
     * Remove invalid characters from the rrd description
     *
     * @param  string  $descr
     * @return string
     */
    public static function safeDescr($descr): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9,._\-\/\ ]/', ' ', $descr);
    }

    /**
     * Escapes strings and sets them to a fixed length for use with RRDtool
     *
     * @param  string  $descr  the string to escape
     * @param  int  $length  if passed, string will be padded and trimmed to exactly this length (after rrdtool unescapes it)
     * @return string
     */
    public static function fixedSafeDescr($descr, $length): string
    {
        $result = Rewrite::shortenIfName($descr);
        $result = str_replace("'", '', $result);            // remove quotes

        if (is_numeric($length)) {
            // preserve original $length for str_pad()

            // determine correct strlen() for substr_count()
            $substr_count_length = $length <= 0 ? null : min(strlen($descr), $length);

            $extra = substr_count($descr, ':', 0, $substr_count_length);
            $result = substr(str_pad($result, $length), 0, $length + $extra);
            if ($extra > 0) {
                $result = substr($result, 0, -1 * $extra);
            }
        }

        $result = str_replace(':', '\:', $result);          // escape colons

        return $result . ' ';
    }

    /**
     * @param  string|string[]  $filename
     */
    private static function filenameString(string|array $filename): string
    {
        return is_array($filename) ? implode('-', $filename) : $filename;
    }
}
