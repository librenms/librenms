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
use Exception;
use Illuminate\Support\Str;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\FileExistsException;
use LibreNMS\Exceptions\RrdException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Exceptions\RrdNotFoundException;
use LibreNMS\RRD\RrdProcess;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Rewrite;
use Log;

class Rrd extends BaseDatastore
{
    private $disabled = false;

    private ?RrdProcess $rrd = null;
    /** @var string */
    private $rrd_dir;
    /** @var string */
    private $version;
    /** @var string */
    private $rrdcached;

    private array $rra;
    /** @var int */
    private $step;


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

    protected function loadConfig(): void
    {
        $this->rrdcached = LibrenmsConfig::get('rrdcached', false);
        $this->rrd_dir = LibrenmsConfig::get('rrd_dir', LibrenmsConfig::get('install_dir') . '/rrd');
        $this->step = LibrenmsConfig::get('rrd.step', 300);
        $this->rra = preg_split('/s+/', trim(LibrenmsConfig::get(
            'rrd_rra',
            'RRA:AVERAGE:0.5:1:2016 RRA:AVERAGE:0.5:6:1440 RRA:AVERAGE:0.5:24:1440 RRA:AVERAGE:0.5:288:1440 ' .
            ' RRA:MIN:0.5:1:2016 RRA:MIN:0.5:6:1440     RRA:MIN:0.5:24:1440     RRA:MIN:0.5:288:1440 ' .
            ' RRA:MAX:0.5:1:2016 RRA:MAX:0.5:6:1440     RRA:MAX:0.5:24:1440     RRA:MAX:0.5:288:1440 ' .
            ' RRA:LAST:0.5:1:2016 '
        )));
        $this->version = LibrenmsConfig::get('rrdtool_version', '1.4');
    }

    public function init(int $timeout = 600): void
    {
        $this->rrd ??= app(RrdProcess::class, ['timeout' => $timeout]);
    }

    /**
     * Close rrdtool process.
     * This should be done before exiting
     */
    public function terminate(): void
    {
        $this->rrd?->stop();
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
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
            $this->update($rrd, $fields);
        } catch (RrdNotFoundException) {
            if (isset($rrd_def)) {
                $this->command('create', $rrd, ['--step', $step, ...$rrd_def->getArguments(), ...$this->rra]);
                $this->update($rrd, $fields);
            }
        }
    }

    public function lastUpdate(string $filename): ?TimeSeriesPoint
    {
        $output = $this->command('lastupdate', $filename);

        if (preg_match('/((?: \w+)+)\n\n(\d+):((?: [\d.-]+)+)\nOK/', $output, $matches)) {
            $data = array_combine(
                explode(' ', ltrim($matches[1])),
                explode(' ', ltrim($matches[3])),
            );

            return new TimeSeriesPoint((int) $matches[2], $data);
        }

        return null;
    }

    /**
     * Updates an rrd database at $filename using $options
     * Where $options is an array, each entry which is not a number is replaced with "U"
     *
     * @param string $filename
     * @param array $data
     *
     * @throws RrdException
     * @throws Exception
     *
     * @internal
     */
    public function update(string $filename, array $data): void
    {
        $data = 'N:' . implode(':', array_map(fn($v) => is_numeric($v) ? $v : 'U', $data));

        $this->command('update', $filename, [$data]);
    }

    // rrdtool_update

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
            $this->command('tune', $filename, $options);
        }

        return true;
    }

    // rrdtool_tune

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
        $pmxcdir = implode('/', [$this->rrd_dir, 'proxmox', self::safeName($pmxcluster)]);
        // this is not needed for remote rrdcached
        if (! is_dir($pmxcdir)) {
            mkdir($pmxcdir, 0775, true);
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
        $oldrrd = self::name($device->hostname, $oldname);
        $newrrd = self::name($device->hostname, $newname);
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
     * Generates a filename based on the hostname (or IP) and some extra items
     *
     * @param  string  $host  Host name
     * @param  array|string  $extra  Components of RRD filename - will be separated with "-", or a pre-formed rrdname
     * @param  string  $extension  File extension (default is .rrd)
     * @return string the name of the rrd file for $host's $extra component
     */
    public function name($host, $extra, $extension = '.rrd'): string
    {
        $filename = self::safeName(is_array($extra) ? implode('-', $extra) : $extra);

        return implode('/', [$this->dirFromHost($host), $filename . $extension]);
    }

    /**
     * Generates a path based on the hostname (or IP)
     *
     * @param  string  $host  Host name
     * @return string the name of the rrd directory for $host
     */
    public function dirFromHost($host): string
    {
        $host = self::safeName(trim($host, '[]'));

        return Str::finish($this->rrd_dir, '/') . $host;
    }

    /**
     * Generates and pipes a command to rrdtool
     *
     * @internal
     *
     * @param  string  $command  create, update, updatev, graph, graphv, dump, restore, fetch, tune, first, last, lastupdate, info, resize, xport, flushcached
     * @param  string  $filename  The full patth to the rrd file
     * @param  array  $options  rrdtool command options
     * @return string the output of the command
     *
     * @throws Exception thrown when the rrdtool process(s) cannot be started
     */
    private function command(string $command, string $filename, array $options = []): string
    {
        $stat = Measurement::start($this->coalesceStatisticType($command));
        $output = '';

        try {
            $cmd = self::buildCommand($command, $filename, $options);
        } catch (FileExistsException) {
            Log::debug("RRD[%g$filename already exists%n]", ['color' => true]);

            return $output;
        }

        $commandLine = implode(' ', $cmd);
        Log::debug('RRD[%g' . $commandLine . '%n]', ['color' => true]);

        // do not write rrd files, but allow read-only commands
        $ro_commands = ['graph', 'graphv', 'dump', 'fetch', 'first', 'last', 'lastupdate', 'info', 'xport'];
        if ($this->disabled && ! in_array($command, $ro_commands)) {
            if (! LibrenmsConfig::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return $output;
        }

        // send the command!
        $this->init();
        if (in_array($command, ['last', 'list', 'lastupdate', 'update'])) {
            // send and wait for completion
            $output = $this->rrd->run($commandLine);

            if (Debug::isVerbose()) {
                echo 'RRDtool Output: ' . $output;
            }
        } else {
            // don't care about the output
            $this->rrd->runAsync($commandLine);
        }

        $this->recordStatistic($stat->end());

        return $output;
    }

    /**
     * Build a command array for rrdtool
     * Shortens the filename as needed
     * Determines if --daemon should be used
     *
     * @param  string  $command  The base rrdtool command.  Usually create, update, last.
     * @param  string  $filename  The full path to the rrd file
     * @param  array  $options  Options for the command possibly including the rrd definition
     * @return array returns a full command array ready to be used by rrdtool
     *
     * @throws FileExistsException if rrdtool <1.4.3 and the rrd file exists locally
     */
    public function buildCommand(string $command, string $filename, array $options = []): array
    {
        if ($command == 'create') {
            // <1.4.3 doesn't support -O, so make sure the file doesn't exist
            if (version_compare($this->version, '1.4.3', '<')) {
                if (is_file($filename)) {
                    throw new FileExistsException();
                }
            } else {
                $options[] = '-O';
            }
        }

        if ($this->rrdcached &&
            ! ($command == 'create' && version_compare($this->version, '1.5.5', '<')) &&
            ! ($command == 'tune' && version_compare($this->version, '1.5', '<'))
        ) {
            // only relative paths if using rrdcached
            $filename = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $filename);
            $options = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $options);

            return [$command, $filename, '--daemon', $this->rrdcached, ...$options];
        }

        return [$command, $filename, ...$options];
    }

    /**
     * Get array of all rrd files for a device,
     * via rrdached or localdisk.
     *
     * @param  string  $hostname  hostname of the device
     * @return string[] array of rrd files for this host
     */
    public function getRrdFiles(string $hostname): array
    {
        if ($this->rrdcached) {
            $output = $this->command('list', '/' . self::safeName($hostname));
            $files = explode("\n", trim($output));
            array_pop($files); // remove rrdcached status line
        } else {
            $files = glob($this->dirFromHost($hostname) . '/*.rrd') ?: [];
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
            $check_output = $this->command('last', $filename);
            $filename = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $filename);

            return ! (str_contains($check_output, $filename) && str_contains($check_output, 'No such file or directory'));
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

        foreach (glob($this->name($hostname, $prefix, '*.rrd')) as $rrd) {
            unlink($rrd);
        }
    }

    /**
     * Generates a graph file at $graph_file using $options
     * Graphs are a single command per run, so this just runs rrdtool
     *
     * @param  array  $options
     * @return string
     *
     * @throws RrdGraphException
     */
    public function graph(array $options): string
    {
        try {
            $command = $this->buildCommand('graph', '-', $options);
            $this->init(300);
            return $this->rrd->run('"' . implode('" "', $command) . "\"\nquit");
        } catch (RrdException $e) {
            throw new RrdGraphException($e->getMessage(), 'Error');
        }
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
     * Run rrdtool and parse the version from the output.
     *
     * @return string
     */
    public static function version(): string
    {
        try {
            $rrd = app(RrdProcess::class, ['timeout' => 10]);
            $output = $rrd->run('--version');
            $parts = explode(' ', $output, 3);

            if (isset($parts[1])) {
                return str_replace('1.7.01.7.0', '1.7.0', $parts[1]);
            }
        } catch (Exception) {
            //
        }

        return '';
    }

    /**
     * Only track update and create primarily, just put all others in an "other" bin
     *
     * @param  string  $type
     * @return string
     */
    private function coalesceStatisticType($type): string
    {
        return ($type == 'update' || $type == 'create') ? $type : 'other';
    }
}
