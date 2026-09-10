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
use File;
use Illuminate\Support\Str;
use LibreNMS\Data\Store\Rrd\PhpRrd;
use LibreNMS\Data\Store\Rrd\RrdBackendInterface;
use LibreNMS\Data\Store\Rrd\RrdtoolRrd;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdFileExistsException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\Exceptions\RrdPermissionException;
use LibreNMS\Exceptions\RrdStoreException;
use LibreNMS\Util\Rewrite;
use Log;
use SplFileInfo;
use Symfony\Component\Process\Process;
use Throwable;

class Rrd extends BaseDatastore
{
    private bool $disabled = false;
    private int $updateErrorCount = 0;

    private RrdBackendInterface $backend;
    private string $rrd_dir;
    private string $version;
    private string $rrdcached;
    /** @var string[] */
    private array $rra;
    private int $step;

    public function __construct()
    {
        parent::__construct();
        $this->loadConfig();
    }

    public function getName(): string
    {
        return 'RRD';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('rrd.enable', true);
    }

    /**
     * Load the config (seaparated from the __construct function for unit tests
     */
    protected function loadConfig(): void
    {
        $this->rrdcached = LibrenmsConfig::get('rrdcached', false);
        $this->rrd_dir = LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd');
        $this->step = LibrenmsConfig::get('rrd.step', 300);
        $this->rra = preg_split('/\s+/', trim(LibrenmsConfig::get(
            'rrd_rra',
            'RRA:AVERAGE:0.5:1:2016 RRA:AVERAGE:0.5:6:1440 RRA:AVERAGE:0.5:24:1440 RRA:AVERAGE:0.5:288:1440 ' .
            ' RRA:MIN:0.5:1:2016 RRA:MIN:0.5:6:1440     RRA:MIN:0.5:24:1440     RRA:MIN:0.5:288:1440 ' .
            ' RRA:MAX:0.5:1:2016 RRA:MAX:0.5:6:1440     RRA:MAX:0.5:24:1440     RRA:MAX:0.5:288:1440 ' .
            ' RRA:LAST:0.5:1:2016 '
        )));
        $this->version = LibrenmsConfig::get('rrdtool_version', '1.4');
        $this->backend = class_exists('\RRDGraph') ? new PhpRrd() : new RrdtoolRrd();
    }

    /**
     * Close rrdtool process.
     * This should be done before exiting
     */
    public function terminate(): void
    {
        $this->backend->terminate();
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
        $step = $meta['rrd_step'] ?? $this->step;
        if (! empty($meta['rrd_oldname'])) {
            self::renameFile($device_model, $meta['rrd_oldname'], $rrd_name);
        }

        if (isset($meta['rrd_proxmox_name'])) {
            $pmxvars = $meta['rrd_proxmox_name'];
            $rrd = self::proxmoxName($pmxvars['pmxcluster'], $pmxvars['vmid'], $pmxvars['vmport']);
        } else {
            $rrd = self::name($device_model->hostname, $rrd_name);
        }

        if (isset($meta['rrd_def'])) {
            $rrd_def = $meta['rrd_def'];

            // filter out data not in the definition
            $fields = array_filter($fields, function ($key) use ($rrd_def) {
                $valid = $rrd_def->isValidDataset($key);
                if (! $valid) {
                    Log::debug("RRD warning: unused data sent $key");
                }

                return $valid;
            }, ARRAY_FILTER_USE_KEY);
        }

        try {
            try {
                $this->update($rrd, $fields);
            } catch (RrdNotFoundException) {
                if (isset($rrd_def)) {
                    $stat = Measurement::start('create');
                    $this->backend->create($rrd, ['--step', $step, ...$rrd_def->getArguments(), ...$this->rra]);
                    $this->recordStatistic($stat->end());
                    $this->update($rrd, $fields);
                }
            }
        } catch (RrdStoreException $e) {
            Log::error('RRD Error %r' . $e->getMessage() . '%n', ['color' => true]);

            if (++$this->updateErrorCount >= 3) {
                $this->disabled = true;
                Eventlog::log('RRD updates disabled, too many errors. Final error: ' . $e->getMessage(), $device_model, 'rrd', Severity::Error);
            }
        } catch (RrdException $e) {
            Log::error('RRD Error %r' . $e->getMessage() . '%n', ['color' => true]);
        }
    }

    /**
     * @throws RrdException
     */
    public function lastUpdate(string $filename): ?TimeSeriesPoint
    {
        $stat = Measurement::start('other');
        $ret = $this->backend->lastUpdate($filename);
        $this->recordStatistic($stat->end());

        return $ret;
    }

    /**
     * Updates an rrd database at $filename using $options
     * Where $options is an array, each entry which is not a number is replaced with "U"
     *
     * @param  string  $filename
     * @param  string[]  $data
     *
     * @throws RrdException
     *
     * @internal
     */
    public function update(string $filename, array $data): void
    {
        if ($this->disabled) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return;
        }

        $stat = Measurement::start('update');
        $this->backend->update($filename, $data);
        $this->recordStatistic($stat->end());
    }

    /**
     * Modify an rrd file's max value and trim the peaks as defined by rrdtool
     *
     * @param  string  $type  only 'port' is supported at this time
     * @param  string  $filename  the path to the rrd file
     * @param  int  $max  the new max value
     * @return bool
     */
    public function tune($type, $filename, $max): bool
    {
        if ($this->disabled) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return false;
        }

        $fields = [];
        if ($type === 'port') {
            if ($max < 10000000) {
                return false;
            }
            $max /= 8;
            $fields = [
                'INOCTETS',
                'OUTOCTETS',
                'INERRORS',
                'OUTERRORS',
                'INUCASTPKTS',
                'OUTUCASTPKTS',
                'INNUCASTPKTS',
                'OUTNUCASTPKTS',
                'INDISCARDS',
                'OUTDISCARDS',
                'INUNKNOWNPROTOS',
                'INBROADCASTPKTS',
                'OUTBROADCASTPKTS',
                'INMULTICASTPKTS',
                'OUTMULTICASTPKTS',
            ];
        }
        if (count($fields) > 0) {
            $options = [];
            foreach ($fields as $field) {
                array_push($options, '--maximum', $field . ':' . $max);
            }
            try {
                $stat = Measurement::start('other');
                $this->backend->tune($filename, $options);
                $this->recordStatistic($stat->end());
            } catch (RrdException $e) {
                if (! $e instanceof RrdNotFoundException) {
                    Log::debug('RRD tune failed: ' . $e->getMessage());
                }
            }
        }

        return true;
    }

    /**
     * Generates a filename for a proxmox cluster rrd
     *
     * @param  string  $pmxcluster
     * @param  string  $vmid
     * @param  string  $vmport
     * @return string full path to the rrd.
     */
    public function proxmoxName($pmxcluster, $vmid, $vmport): string
    {
        if ($this->rrdcached) {
            $pmxcdir = implode('/', ['proxmox', self::safeName($pmxcluster)]);
        } else {
            $pmxcdir = implode('/', [$this->rrd_dir, 'proxmox', self::safeName($pmxcluster)]);
            // this is not needed for remote rrdcached
            if (! is_dir($pmxcdir)) {
                mkdir($pmxcdir, 0775, true);
            }
        }

        return implode('/', [$pmxcdir, self::safeName($vmid . '_netif_' . $vmport . '.rrd')]);
    }

    /**
     * Get the name of the port rrd file.  For alternate rrd, specify the suffix.
     *
     * @param  int  $port_id
     * @param  string  $suffix
     * @return string
     */
    public function portName($port_id, $suffix = null): string
    {
        return "port-id$port_id" . (empty($suffix) ? '' : '-' . $suffix);
    }

    /**
     * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
     *
     * @param  Device  $device  Device model
     * @param  string|array  $oldname  RRD name array as used with rrd_name()
     * @param  string|array  $newname  RRD name array as used with rrd_name()
     * @return bool indicating rename success or failure
     */
    public function renameFile(Device $device, $oldname, $newname): bool
    {
        $oldrrd = self::_name($device->hostname, $oldname, true);
        $newrrd = self::_name($device->hostname, $newname, true);
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
     * Generates a partial filename based on the hostname (or IP) and some extra items
     *
     * @param  string  $host  Host name
     * @param  array|string  $extra  Components of RRD filename - will be separated with "-", or a pre-formed rrdname
     * @param  bool  $forceabsolute  Do we always want an absolute filename
     * @return string the name of the rrd file for $host's $extra component
     */
    private function partname($host, $extra, $forceabsolute = false): string
    {
        $partname = self::safeName(is_array($extra) ? implode('-', $extra) : $extra);

        return implode('/', [$this->dirFromHost($host, $forceabsolute), $partname]);
    }

    /**
     * Generates a filename based on the hostname (or IP) and some extra items
     *
     * @param  string  $host  Host name
     * @param  array|string  $extra  Components of RRD filename - will be separated with "-", or a pre-formed rrdname
     * @param  bool  $forceabsolute  Do we always want an absolute filename
     * @return string the name of the rrd file for $host's $extra component
     */
    private function _name($host, $extra, $forceabsolute = false): string
    {
        return $this->partname($host, $extra, $forceabsolute) . '.rrd';
    }

    /**
     * Public interface to the _name() function - doesn't allow forcing absolute paths
     *
     * @param  string  $host  Host name
     * @param  array|string  $extra  Components of RRD filename - will be separated with "-", or a pre-formed rrdname
     * @return string the name of the rrd file for $host's $extra component
     */
    public function name($host, $extra): string
    {
        return $this->_name($host, $extra);
    }

    /**
     * Build a command array for rrdtool
     * Shortens the filename as needed
     * Determines if --daemon should be used
     *
     * @param  string  $command  The base rrdtool command.  Usually create, update, last.
     * @param  string  $filename  The full path to the rrd file
     * @param  string[]  $options  Options for the command possibly including the rrd definition
     * @return string[] returns a full command array ready to be used by rrdtool
     */
    public static function buildCommand(string $command, string $filename, array $options = []): array
    {
        if ($command == 'create') {
            // <1.4.3 doesn't support -O, so make sure the file doesn't exist
            $version = LibrenmsConfig::get('rrdtool_version', '1.4');
            if (version_compare($version, '1.4.3', '<')) {
                if (is_file($filename)) {
                    throw new RrdFileExistsException();
                }
            } else {
                $options[] = '-O';
            }
        }

        return [$command, $filename, ...$options];
    }

    /**
     * Generates a path based on the hostname (or IP)
     *
     * @param  string  $host  Host name
     * @param  bool  $forceabsolute  Do we always want an absolute directory name
     * @return string the name of the rrd directory for $host
     */
    private function dirFromHost($host, $forceabsolute = false): string
    {
        $host = self::safeName(trim((string) $host, '[]'));

        if ($this->rrdcached && ! $forceabsolute) {
            return $host;
        }

        return Str::finish($this->rrd_dir, '/') . $host;
    }

    /**
     * Get array of all rrd files for a device,
     * via rrdached or localdisk.
     *
     * @param  string  $hostname  hostname of the device
     * @return string[] array of rrd files for this host
     */
    public function getRrdFiles(string $hostname, string|array $prefix = ''): array
    {
        $prefix = self::safeName(is_array($prefix) ? implode('-', $prefix) : $prefix);

        if ($this->rrdcached) {
            $stat = Measurement::start('other');
            $files = $this->backend->list('/' . self::safeName($hostname), $prefix);
            $this->recordStatistic($stat->end());
        } else {
            $files = glob($this->dirFromHost($hostname) . '/' . $prefix . '*.rrd') ?: [];
        }

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
     * This will perform a remote check if using rrdcached and rrdtool >= 1.5
     *
     * @param  string  $filename  full path to the rrd file
     * @return bool whether or not the passed rrd file exists
     */
    public function checkRrdExists($filename): bool
    {
        if ($this->rrdcached && version_compare($this->version, '1.5', '>=')) {
            $stat = Measurement::start('other');
            try {
                $filename = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $filename);
                $check_output = $this->backend->last($filename);
                $this->recordStatistic($stat->end());

                return ! (str_contains($check_output, $filename) && str_contains($check_output, 'No such file or directory'));
            } catch (RrdNotFoundException) {
                $this->recordStatistic($stat->end());

                return false;
            }
        } else {
            return is_file($filename);
        }
    }

    /**
     * Remove RRD file(s).  Use with care as this permanently deletes rrd data.
     *
     * @param  string  $hostname  rrd subfolder (hostname)
     * @param  string  $prefix  start of rrd file name all files matching will be deleted
     */
    public function purge($hostname, $prefix): void
    {
        if (empty($hostname)) {
            Log::error("Could not purge rrd $prefix, empty hostname");

            return;
        }

        foreach (glob($this->partname($hostname, $prefix, true) . '*.rrd') as $rrd) {
            unlink($rrd);
        }
    }

    /**
     * Generates a graph file at $graph_file using $options
     *
     * @param  array  $options
     * @return string
     *
     * @throws RrdGraphException
     */
    public function graph(array $options): string
    {
        return $this->backend->graph($options);
    }

    /**
     * Remove invalid characters from the rrd file name
     *
     * @param  string  $name
     * @return string
     */
    public static function safeName($name): string
    {
        return (string) preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
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
     * Initialise storage for a device
     */
    public function initStorage(Device $device): void
    {
        $device_dir = $this->dirFromHost($device->hostname, true);

        if (LibrenmsConfig::get('rrdcached', false) && LibrenmsConfig::get('rrd.enable', true) && ! is_dir($device_dir)) {
            mkdir($device_dir);
            Log::info("Created directory : $device_dir");
        }
    }

    /**
     * Rename storage for a device
     */
    public function renameDevice(string $oldName, string $newName): bool
    {
        $new_rrd_dir = $this->dirFromHost($newName, true);

        if (is_dir($new_rrd_dir)) {
            throw new RrdPermissionException("Renaming of $oldName failed due to existing RRD folder for $newName");
        }

        if (rename($this->dirFromHost($oldName, true), $new_rrd_dir) === true) {
            return true;
        }

        return false;
    }

    /**
     * Delete a device
     */
    public function deleteDevice(string $hostname): void
    {
        // delete rrd files
        $host_dir = $this->dirFromHost($hostname, true);
        if (! File::deleteDirectory($host_dir)) {
            throw new RrdPermissionException("Could not delete RRD files for: $hostname");
        }
    }

    /**
     * Get storage stats for a device
     */
    public function getStorageSize(Device $device): array
    {
        $directory = $this->dirFromHost($device->hostname, true);

        if (! File::isDirectory($directory) || ! File::isReadable($directory)) {
            return [0, 0];
        }

        try {
            $files = collect(File::allFiles($directory));

            $size = $files->sum(function (SplFileInfo $file): int {
                try {
                    return $file->getSize();
                } catch (Throwable) {
                    return 0;
                }
            });

            return [$size, $files->count()];
        } catch (Throwable) {
            return [0, 0];
        }
    }
}
