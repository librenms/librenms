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
use LibreNMS\Data\Store\Rrd\RrdPath;
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
use Symfony\Component\Process\Process;

class Rrd extends BaseDatastore
{
    private bool $disabled = false;
    private int $updateErrorCount = 0;

    private RrdBackendInterface $backend;
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
            self::checkDirExists(RrdPath::make('proxmox-' . $pmxvars['pmxcluster']));
        } else {
            $rrd = RrdPath::make($device_model->hostname, self::filenameString($rrd_name) . '.rrd');
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
     * Updates an rrd database at $filename using $options
     * Where $options is an array, each entry which is not a number is replaced with "U"
     *
     * @param  string[]  $data
     *
     * @throws RrdException
     *
     * @internal
     */
    public function update(RrdPath $rrd, array $data): void
    {
        if ($this->disabled) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return;
        }

        $stat = Measurement::start('update');
        $this->backend->update($rrd, $data);
        $this->recordStatistic($stat->end());
    }

    /**
     * Modify an rrd file's max value and trim the peaks as defined by rrdtool
     */
    public function tune(string $type, RrdPath $rrd, int $max): bool
    {
        // tune only works on the local filesystem - use the fully qualified path the RRD file
        $filename = $rrd->fullPath();

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
            $cmd = [LibrenmsConfig::get('rrdtool', 'rrdtool'), 'tune', $filename];
            foreach ($fields as $field) {
                array_push($cmd, '--maximum', $field . ':' . $max);
            }
            Log::debug('[%gRRD ' . implode(' ', $cmd) . '%n]', ['color' => true]);

            $stat = Measurement::start('other');
            $process = app()->make(Process::class, ['command' => $cmd]);
            $process->disableOutput();
            $process->run();

            $ret = $process->isSuccessful();

            $this->recordStatistic($stat->end());
        } else {
            return true;
        }

        return $ret;
    }

    /**
     * Generates a filename for a proxmox cluster rrd
     */
    public function proxmoxName(string $pmxcluster, string $vmid, string $vmport): RrdPath
    {
        return RrdPath::make('proxmox-' . $pmxcluster, $vmid . '_netif_' . $vmport . '.rrd');
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
     * Build a command array for rrdtool
     * Shortens the filename as needed
     * Determines if --daemon should be used
     *
     * @param  string[]  $options  Options for the command possibly including the rrd definition
     * @return string[] returns a full command array ready to be used by rrdtool
     *
     * @throws RrdFileExistsException if rrdtool <1.4.3 and the rrd file exists locally
     */
    public static function buildCommand(string $command, string $filename, array $options = []): array
    {
        if ($command == 'create') {
            // <1.4.3 doesn't support -O, so make sure the file doesn't exist
            if (version_compare(LibrenmsConfig::get('rrdtool_version', '1.4'), '1.4.3', '<')) {
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
     * Get array of all rrd files for a device,
     * via rrdached or localdisk.
     *
     * @param  string|string[]  $prefix  limit returned results to files matching this prefix
     * @return string[] array of rrd files for this host
     */
    public function getRrdFiles(string $hostname, string|array $prefix = ''): array
    {
        $prefix = self::safeName(is_array($prefix) ? implode('-', $prefix) : $prefix);
        $rrdpath = RrdPath::make($hostname);

        if ($this->rrdcached) {
            $stat = Measurement::start('other');
            $files = $this->backend->list('/' . self::safeName($hostname), $prefix);
            $this->recordStatistic($stat->end());
        } else {
            $files = glob($rrdpath . DIRECTORY_SEPARATOR . $prefix . '*.rrd') ?: [];
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
     */
    public function checkRrdExists(RrdPath $rrdpath): bool
    {
        if ($this->rrdcached && version_compare($this->version, '1.5', '>=')) {
            $stat = Measurement::start('other');
            try {
                $check_output = $this->backend->last($rrdpath);
                $this->recordStatistic($stat->end());

                return ! (str_contains($check_output, $rrdpath) && str_contains($check_output, 'No such file or directory'));
            } catch (RrdNotFoundException) {
                $this->recordStatistic($stat->end());

                return false;
            }
        } else {
            return is_file($rrdpath->fullPath());
        }
    }

    private static function checkDirExists(RrdPath $rrdpath): bool
    {
        if (LibrenmsConfig::get('rrdcached')) {
            return true;
        }

        $rrd_dir = $rrdpath->fullPath();
        if (! is_dir($rrd_dir)) {
            if (mkdir($rrd_dir, 0775, true)) {
                Log::info("Created directory : $rrd_dir");
            } else {
                Log::error("Failed to create rrd directory: $rrd_dir");

                return false;
            }
        }

        return true;
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
     * Generates a graph file at $graph_file using $options
     *
     * @param  array  $options
     * @return string
     *
     * @throws RrdGraphException
     */
    public function graph(array $options): string
    {
        try {
            return $this->backend->graph($options);
        } catch (RrdException $e) {
            throw new RrdGraphException($e->getMessage(), 'Error');
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

    /**
     * Initialise storage for a device
     */
    public function initStorage(Device $device): void
    {
        if (LibrenmsConfig::get('rrd.enable', true)) {
            self::checkDirExists(RrdPath::make($device->hostname));
        }
    }

    /**
     * Rename storage for a device
     */
    public function renameDevice(Device $device, string $oldName, string $newName): bool
    {
        $new_rrd_dir = RrdPath::make($newName)->fullPath();

        if (is_dir($new_rrd_dir)) {
            Eventlog::log("Renaming of $oldName failed due to existing RRD folder for $newName", $device, 'system', Severity::Error);

            throw new RrdPermissionException("Renaming of $oldName failed due to existing RRD folder for $newName");
        }

        return rename(RrdPath::make($oldName)->fullPath(), $new_rrd_dir);
    }

    /**
     * Delete a device
     */
    public function deleteDevice(string $hostname): void
    {
        // delete rrd files
        $host_dir = RrdPath::make($hostname)->fullPath();
        if (! File::deleteDirectory($host_dir)) {
            throw new RrdPermissionException("Could not delete RRD files for: $hostname");
        }
    }
}
