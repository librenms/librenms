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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\Data\Measure\Measurement;
use LibreNMS\Exceptions\FileExistsException;
use LibreNMS\Exceptions\RrdGraphException;
use LibreNMS\Proc;
use LibreNMS\Util\Debug;
use LibreNMS\Util\Rewrite;
use Log;
use Symfony\Component\Process\Process;

class Rrd extends BaseDatastore
{
    private $disabled = false;

    /** @var Proc */
    private $sync_process;
    /** @var Proc */
    private $async_process;
    private $rrd_dir;
    private $version;
    private $rrdcached;
    private $rra;
    private $step;

    public function __construct()
    {
        parent::__construct();
        $this->rrdcached = Config::get('rrdcached', false);

        $this->init();
        $this->rrd_dir = Config::get('rrd_dir', Config::get('install_dir') . '/rrd');
        $this->step = Config::get('rrd.step', 300);
        $this->rra = Config::get(
            'rrd_rra',
            'RRA:AVERAGE:0.5:1:2016 RRA:AVERAGE:0.5:6:1440 RRA:AVERAGE:0.5:24:1440 RRA:AVERAGE:0.5:288:1440 ' .
            ' RRA:MIN:0.5:1:2016 RRA:MIN:0.5:6:1440     RRA:MIN:0.5:24:1440     RRA:MIN:0.5:288:1440 ' .
            ' RRA:MAX:0.5:1:2016 RRA:MAX:0.5:6:1440     RRA:MAX:0.5:24:1440     RRA:MAX:0.5:288:1440 ' .
            ' RRA:LAST:0.5:1:2016 '
        );
        $this->version = Config::get('rrdtool_version', '1.4');
    }

    public function getName()
    {
        return 'RRD';
    }

    public static function isEnabled()
    {
        return Config::get('rrd.enable', true);
    }

    /**
     * Opens up a pipe to RRDTool using handles provided
     *
     * @param bool $dual_process start an additional process that's output should be read after every command
     * @return bool the process(s) have been successfully started
     */
    public function init($dual_process = true)
    {
        $command = Config::get('rrdtool', 'rrdtool') . ' -';

        $descriptor_spec = [
            0 => ['pipe', 'r'], // stdin  is a pipe that the child will read from
            1 => ['pipe', 'w'], // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'], // stderr is a pipe that the child will write to
        ];

        $cwd = Config::get('rrd_dir');

        if (! $this->isSyncRunning()) {
            $this->sync_process = new Proc($command, $descriptor_spec, $cwd);
        }

        if ($dual_process && ! $this->isAsyncRunning()) {
            $this->async_process = new Proc($command, $descriptor_spec, $cwd);
            $this->async_process->setSynchronous(false);
        }

        return $this->isSyncRunning() && ($dual_process ? $this->isAsyncRunning() : true);
    }

    public function isSyncRunning()
    {
        return isset($this->sync_process) && $this->sync_process->isRunning();
    }

    public function isAsyncRunning()
    {
        return isset($this->async_process) && $this->async_process->isRunning();
    }

    /**
     * Close all open rrdtool processes.
     * This should be done before exiting
     */
    public function close()
    {
        if ($this->isSyncRunning()) {
            $this->sync_process->close('quit');
        }
        if ($this->isAsyncRunning()) {
            $this->async_process->close('quit');
        }
    }

    /**
     * rrdtool backend implementation of data_update
     *
     * Tags:
     *   rrd_def     RrdDefinition
     *   rrd_name    array|string: the rrd filename, will be processed with rrd_name()
     *   rrd_oldname array|string: old rrd filename to rename, will be processed with rrd_name()
     *   rrd_step             int: rrd step, defaults to 300
     *
     * @param array $device device array
     * @param string $measurement the name of this measurement (if no rrd_name tag is given, this will be used to name the file)
     * @param array $tags tags to pass additional info to rrdtool
     * @param array $fields data values to update
     */
    public function put($device, $measurement, $tags, $fields)
    {
        $rrd_name = isset($tags['rrd_name']) ? $tags['rrd_name'] : $measurement;
        $step = isset($tags['rrd_step']) ? $tags['rrd_step'] : $this->step;
        if (! empty($tags['rrd_oldname'])) {
            self::renameFile($device, $tags['rrd_oldname'], $rrd_name);
        }

        if (isset($tags['rrd_proxmox_name'])) {
            $pmxvars = $tags['rrd_proxmox_name'];
            $rrd = self::proxmoxName($pmxvars['pmxcluster'], $pmxvars['vmid'], $pmxvars['vmport']);
        } else {
            $rrd = self::name($device['hostname'], $rrd_name);
        }

        if (isset($tags['rrd_def'])) {
            $rrd_def = $tags['rrd_def'];

            // filter out data not in the definition
            $fields = array_filter($fields, function ($key) use ($rrd_def) {
                $valid = $rrd_def->isValidDataset($key);
                if (! $valid) {
                    Log::warning("RRD warning: unused data sent $key");
                }

                return $valid;
            }, ARRAY_FILTER_USE_KEY);

            if (! $this->checkRrdExists($rrd)) {
                $newdef = "--step $step $rrd_def $this->rra";
                $this->command('create', $rrd, $newdef);
            }
        }

        $this->update($rrd, $fields);
    }

    /**
     * Updates an rrd database at $filename using $options
     * Where $options is an array, each entry which is not a number is replaced with "U"
     *
     * @internal
     * @param string $filename
     * @param array $data
     * @return array|string
     */
    public function update($filename, $data)
    {
        $values = [];
        // Do some sanitation on the data if passed as an array.

        if (is_array($data)) {
            $values[] = 'N';
            foreach ($data as $v) {
                if (! is_numeric($v)) {
                    $v = 'U';
                }

                $values[] = $v;
            }

            $data = implode(':', $values);

            return $this->command('update', $filename, $data);
        } else {
            return 'Bad options passed to rrdtool_update';
        }
    }

    // rrdtool_update

    /**
     * Modify an rrd file's max value and trim the peaks as defined by rrdtool
     *
     * @param string $type only 'port' is supported at this time
     * @param string $filename the path to the rrd file
     * @param int $max the new max value
     * @return bool
     */
    public function tune($type, $filename, $max)
    {
        $fields = [];
        if ($type === 'port') {
            if ($max < 10000000) {
                return false;
            }
            $max = $max / 8;
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
            $options = '--maximum ' . implode(":$max --maximum ", $fields) . ":$max";
            $this->command('tune', $filename, $options);
        }

        return true;
    }

    // rrdtool_tune

    /**
     * Generates a filename for a proxmox cluster rrd
     *
     * @param string $pmxcluster
     * @param string $vmid
     * @param string $vmport
     * @return string full path to the rrd.
     */
    public function proxmoxName($pmxcluster, $vmid, $vmport)
    {
        $pmxcdir = join('/', [$this->rrd_dir, 'proxmox', self::safeName($pmxcluster)]);
        // this is not needed for remote rrdcached
        if (! is_dir($pmxcdir)) {
            mkdir($pmxcdir, 0775, true);
        }

        return join('/', [$pmxcdir, self::safeName($vmid . '_netif_' . $vmport . '.rrd')]);
    }

    /**
     * Get the name of the port rrd file.  For alternate rrd, specify the suffix.
     *
     * @param int $port_id
     * @param string $suffix
     * @return string
     */
    public function portName($port_id, $suffix = null)
    {
        return "port-id$port_id" . (empty($suffix) ? '' : '-' . $suffix);
    }

    /**
     * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
     *
     * @param array $device Device object
     * @param string|array $oldname RRD name array as used with rrd_name()
     * @param string|array $newname RRD name array as used with rrd_name()
     * @return bool indicating rename success or failure
     */
    public function renameFile($device, $oldname, $newname)
    {
        $oldrrd = self::name($device['hostname'], $oldname);
        $newrrd = self::name($device['hostname'], $newname);
        if (is_file($oldrrd) && ! is_file($newrrd)) {
            if (rename($oldrrd, $newrrd)) {
                log_event("Renamed $oldrrd to $newrrd", $device, 'poller', 1);

                return true;
            } else {
                log_event("Failed to rename $oldrrd to $newrrd", $device, 'poller', 5);

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
     * @param string $host Host name
     * @param array|string $extra Components of RRD filename - will be separated with "-", or a pre-formed rrdname
     * @param string $extension File extension (default is .rrd)
     * @return string the name of the rrd file for $host's $extra component
     */
    public function name($host, $extra, $extension = '.rrd')
    {
        $filename = self::safeName(is_array($extra) ? implode('-', $extra) : $extra);

        return implode('/', [$this->dirFromHost($host), $filename . $extension]);
    }

    /**
     * Generates a path based on the hostname (or IP)
     *
     * @param string $host Host name
     * @return string the name of the rrd directory for $host
     */
    public function dirFromHost($host)
    {
        $host = str_replace(':', '_', trim($host, '[]'));

        return implode('/', [$this->rrd_dir, $host]);
    }

    /**
     * Generates and pipes a command to rrdtool
     *
     * @internal
     * @param string $command create, update, updatev, graph, graphv, dump, restore, fetch, tune, first, last, lastupdate, info, resize, xport, flushcached
     * @param string $filename The full patth to the rrd file
     * @param string $options rrdtool command options
     * @return array the output of stdout and stderr in an array
     * @throws \Exception thrown when the rrdtool process(s) cannot be started
     */
    private function command($command, $filename, $options)
    {
        $stat = Measurement::start($this->coalesceStatisticType($command));
        $output = null;

        try {
            $cmd = self::buildCommand($command, $filename, $options);
        } catch (FileExistsException $e) {
            Log::debug("RRD[%g$filename already exists%n]", ['color' => true]);

            return [null, null];
        }

        Log::debug("RRD[%g$cmd%n]", ['color' => true]);

        // do not write rrd files, but allow read-only commands
        $ro_commands = ['graph', 'graphv', 'dump', 'fetch', 'first', 'last', 'lastupdate', 'info', 'xport'];
        if ($this->disabled && ! in_array($command, $ro_commands)) {
            if (! Config::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }

            return [null, null];
        }

        // send the command!
        if (in_array($command, ['last', 'list']) && $this->init(false)) {
            // send this to our synchronous process so output is guaranteed
            $output = $this->sync_process->sendCommand($cmd);
        } elseif ($this->init()) {
            // don't care about the return of other commands, so send them to the faster async process
            $output = $this->async_process->sendCommand($cmd);
        } else {
            Log::error('rrdtool could not start');
        }

        if (Debug::isVerbose()) {
            echo 'RRDtool Output: ';
            echo $output[0];
            echo $output[1];
        }

        $this->recordStatistic($stat->end());

        return $output;
    }

    /**
     * Build a command for rrdtool
     * Shortens the filename as needed
     * Determines if --daemon and -O should be used
     *
     * @internal
     * @param string $command The base rrdtool command.  Usually create, update, last.
     * @param string $filename The full path to the rrd file
     * @param string $options Options for the command possibly including the rrd definition
     * @return string returns a full command ready to be piped to rrdtool
     * @throws FileExistsException if rrdtool <1.4.3 and the rrd file exists locally
     */
    public function buildCommand($command, $filename, $options)
    {
        if ($command == 'create') {
            // <1.4.3 doesn't support -O, so make sure the file doesn't exist
            if (version_compare($this->version, '1.4.3', '<')) {
                if (is_file($filename)) {
                    throw new FileExistsException();
                }
            } else {
                $options .= ' -O';
            }
        }

        // no remote for create < 1.5.5 and tune < 1.5
        if ($this->rrdcached &&
            ! ($command == 'create' && version_compare($this->version, '1.5.5', '<')) &&
            ! ($command == 'tune' && $this->rrdcached && version_compare($this->version, '1.5', '<'))
        ) {
            // only relative paths if using rrdcached
            $filename = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $filename);
            $options = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $options);

            return "$command $filename $options --daemon " . $this->rrdcached;
        }

        return "$command $filename $options";
    }

    /**
     * Get array of all rrd files for a device,
     * via rrdached or localdisk.
     *
     * @param array $device device for which we get the rrd's
     * @return array array of rrd files for this host
     */
    public function getRrdFiles($device)
    {
        if ($this->rrdcached) {
            $filename = sprintf('/%s', $device['hostname']);
            $rrd_files = $this->command('list', $filename, '');
            // Command output is an array, create new array with each filename as a item in array.
            $rrd_files_array = explode("\n", trim($rrd_files[0]));
            // Remove status line from response
            array_pop($rrd_files_array);
        } else {
            $rrddir = $this->dirFromHost($device['hostname']);
            $pattern = sprintf('%s/*.rrd', $rrddir);
            $rrd_files_array = glob($pattern);
        }

        sort($rrd_files_array);

        return $rrd_files_array;
    }

    /**
     * Get array of rrd files for specific application.
     *
     * @param array $device device for which we get the rrd's
     * @param int   $app_id application id on the device
     * @param string  $app_name name of app to be searched
     * @param string  $category which category of graphs are searched
     * @return array  array of rrd files for this host
     */
    public function getRrdApplicationArrays($device, $app_id, $app_name, $category = null)
    {
        $entries = [];
        $separator = '-';

        $rrdfile_array = $this->getRrdFiles($device);
        if ($category) {
            $pattern = sprintf('%s-%s-%s-%s', 'app', $app_name, $app_id, $category);
        } else {
            $pattern = sprintf('%s-%s-%s', 'app', $app_name, $app_id);
        }

        // app_name contains a separator character? consider it
        $offset = substr_count($app_name, $separator);

        foreach ($rrdfile_array as $rrd) {
            if (str_contains($rrd, $pattern)) {
                $filename = basename($rrd, '.rrd');
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
     * @param string $filename full path to the rrd file
     * @return bool whether or not the passed rrd file exists
     */
    public function checkRrdExists($filename)
    {
        if ($this->rrdcached && version_compare($this->version, '1.5', '>=')) {
            $chk = $this->command('last', $filename, '');
            $filename = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $filename);

            return ! Str::contains(implode($chk), "$filename': No such file or directory");
        } else {
            return is_file($filename);
        }
    }

    /**
     * Remove RRD file(s).  Use with care as this permanently deletes rrd data.
     * @param string $hostname rrd subfolder (hostname)
     * @param string $prefix start of rrd file name all files matching will be deleted
     */
    public function purge($hostname, $prefix)
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
     * @param  string  $options
     * @return string
     * @throws \LibreNMS\Exceptions\FileExistsException
     * @throws \LibreNMS\Exceptions\RrdGraphException
     */
    public function graph(string $options): string
    {
        $process = new Process([Config::get('rrdtool', 'rrdtool'), '-'], $this->rrd_dir);
        $process->setTimeout(300);
        $process->setIdleTimeout(300);

        $command = $this->buildCommand('graph', '-', $options);
        $process->setInput($command . "\nquit");
        $process->run();

        $feedback_position = strrpos($process->getOutput(), 'OK ');
        if ($feedback_position !== false) {
            return substr($process->getOutput(), 0, $feedback_position);
        }

        // if valid image is returned with error, extract image and feedback
        $image_type = Config::get('webui.graph_type', 'png');
        $search = $this->getImageEnd($image_type);
        if (($position = strrpos($process->getOutput(), $search)) !== false) {
            $position += strlen($search);
            throw new RrdGraphException(
                substr($process->getOutput(), $position),
                $process->getExitCode(),
                substr($process->getOutput(), 0, $position)
            );
        }

        // only error text was returned
        $error = trim($process->getOutput() . PHP_EOL . $process->getErrorOutput());
        throw new RrdGraphException($error, $process->getExitCode(), '');
    }

    private function getImageEnd(string $type): string
    {
        $image_suffixes = [
            'png' => hex2bin('0000000049454e44ae426082'),
            'svg' => '</svg>',
        ];

        return $image_suffixes[$type] ?? '';
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Remove invalid characters from the rrd file name
     *
     * @param string $name
     * @return string
     */
    public static function safeName($name)
    {
        return (string) preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
    }

    /**
     * Remove invalid characters from the rrd description
     *
     * @param string $descr
     * @return string
     */
    public static function safeDescr($descr)
    {
        return (string) preg_replace('/[^a-zA-Z0-9,._\-\/\ ]/', ' ', $descr);
    }

    /**
     * Escapes strings and sets them to a fixed length for use with RRDtool
     *
     * @param string $descr the string to escape
     * @param int $length if passed, string will be padded and trimmed to exactly this length (after rrdtool unescapes it)
     * @return string
     */
    public static function fixedSafeDescr($descr, $length)
    {
        $result = Rewrite::shortenIfType($descr);
        $result = str_replace("'", '', $result);            // remove quotes

        if (is_numeric($length)) {
            // preserve original $length for str_pad()

            // determine correct strlen() for substr_count()
            $substr_count_length = $length <= 0 ? null : min(strlen($descr), $length);

            $extra = substr_count($descr, ':', 0, $substr_count_length);
            $result = substr(str_pad($result, $length), 0, ($length + $extra));
            if ($extra > 0) {
                $result = substr($result, 0, (-1 * $extra));
            }
        }

        $result = str_replace(':', '\:', $result);          // escape colons

        return $result . ' ';
    }

    /**
     * Only track update and create primarily, just put all others in an "other" bin
     *
     * @param string $type
     * @return string
     */
    private function coalesceStatisticType($type)
    {
        return ($type == 'update' || $type == 'create') ? $type : 'other';
    }
}
