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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Store;

use LibreNMS\Config;
use LibreNMS\Data\Measure\Measurement;
use LibreNMS\Exceptions\FileExistsException;
use LibreNMS\Proc;
use Log;

class Rrd extends BaseDatastore
{
    private $disabled = false;

    /** @var Proc $sync_process */
    private $sync_process;
    /** @var Proc $async_process */
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
            ' RRA:MIN:0.5:1:720 RRA:MIN:0.5:6:1440     RRA:MIN:0.5:24:775     RRA:MIN:0.5:288:797 ' .
            ' RRA:MAX:0.5:1:720 RRA:MAX:0.5:6:1440     RRA:MAX:0.5:24:775     RRA:MAX:0.5:288:797 ' .
            ' RRA:LAST:0.5:1:1440 '
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

        if (!$this->isSyncRunning()) {
            $this->sync_process = new Proc($command, $descriptor_spec, $cwd);
        }

        if ($dual_process && !$this->isAsyncRunning()) {
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
        if (!empty($tags['rrd_oldname'])) {
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
                if (!$valid) {
                    Log::warning("RRD warning: unused data sent $key");
                }
                return $valid;
            }, ARRAY_FILTER_USE_KEY);

            if (!$this->checkRrdExists($rrd)) {
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
                if (!is_numeric($v)) {
                    $v = 'U';
                }

                $values[] = $v;
            }

            $data = implode(':', $values);
            return $this->command('update', $filename, $data);
        } else {
            return 'Bad options passed to rrdtool_update';
        }
    } // rrdtool_update

    /**
     * Modify an rrd file's max value and trim the peaks as defined by rrdtool
     *
     * @param string $type only 'port' is supported at this time
     * @param string $filename the path to the rrd file
     * @param integer $max the new max value
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
                'OUTMULTICASTPKTS'
            ];
        }
        if (count($fields) > 0) {
            $options = "--maximum " . implode(":$max --maximum ", $fields) . ":$max";
            $this->command('tune', $filename, $options);
        }
        return true;
    } // rrdtool_tune

    /**
     * Generates a filename for a proxmox cluster rrd
     *
     * @param $pmxcluster
     * @param $vmid
     * @param $vmport
     * @return string full path to the rrd.
     */
    public function proxmoxName($pmxcluster, $vmid, $vmport)
    {
        $pmxcdir = join('/', [$this->rrd_dir, 'proxmox', self::safeName($pmxcluster)]);
        // this is not needed for remote rrdcached
        if (!is_dir($pmxcdir)) {
            mkdir($pmxcdir, 0775, true);
        }

        return join('/', [$pmxcdir, self::safeName($vmid . '_netif_' . $vmport . '.rrd')]);
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
        if (is_file($oldrrd) && !is_file($newrrd)) {
            if (rename($oldrrd, $newrrd)) {
                log_event("Renamed $oldrrd to $newrrd", $device, "poller", 1);
                return true;
            } else {
                log_event("Failed to rename $oldrrd to $newrrd", $device, "poller", 5);
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
    public function name($host, $extra, $extension = ".rrd")
    {
        $filename = self::safeName(is_array($extra) ? implode("-", $extra) : $extra);
        return implode("/", [$this->dirFromHost($host), $filename . $extension]);
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
        return implode("/", [$this->rrd_dir, $host]);
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
        global $vdebug;
        $stat = Measurement::start($this->coalesceStatisticType($command));

        try {
            $cmd = self::buildCommand($command, $filename, $options);
        } catch (FileExistsException $e) {
            Log::debug("RRD[%g$filename already exists%n]", ['color' => true]);
            return [null, null];
        }

        Log::debug("RRD[%g$cmd%n]", ['color' => true]);

        // do not write rrd files, but allow read-only commands
        $ro_commands = ['graph', 'graphv', 'dump', 'fetch', 'first', 'last', 'lastupdate', 'info', 'xport'];
        if ($this->disabled && !in_array($command, $ro_commands)) {
            if (!Config::get('hide_rrd_disabled')) {
                Log::debug('[%rRRD Disabled%n]', ['color' => true]);
            }
            return [null, null];
        }

        // send the command!
        if ($command == 'last' && $this->init(false)) {
            // send this to our synchronous process so output is guaranteed
            $output = $this->sync_process->sendCommand($cmd);
        } elseif ($this->init()) {
            // don't care about the return of other commands, so send them to the faster async process
            $output = $this->async_process->sendCommand($cmd);
        } else {
            Log::error('rrdtool could not start');
        }

        if ($vdebug) {
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
            !($command == 'create' && version_compare($this->version, '1.5.5', '<')) &&
            !($command == 'tune' && $this->rrdcached && version_compare($this->version, '1.5', '<'))
        ) {
            // only relative paths if using rrdcached
            $filename = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $filename);
            $options = str_replace([$this->rrd_dir . '/', $this->rrd_dir], '', $options);

            return "$command $filename $options --daemon " . $this->rrdcached;
        }

        return "$command $filename $options";
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
            return !str_contains(implode($chk), "$filename': No such file or directory");
        } else {
            return is_file($filename);
        }
    }

    /**
     * Generates a graph file at $graph_file using $options
     * Opens its own rrdtool pipe.
     *
     * @param string $graph_file
     * @param string $options
     * @return integer
     */
    public function graph($graph_file, $options)
    {
        if ($this->init(false)) {
            $cmd = $this->buildCommand('graph', $graph_file, $options);

            $output = implode($this->sync_process->sendCommand($cmd));

            d_echo("<p>$cmd</p>\n<p>command returned ($output)</p>");

            return $output;
        } else {
            return 0;
        }
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
        return (string)preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
    }

    /**
     * Remove invalid characters from the rrd description
     *
     * @param string $descr
     * @return string
     */
    public static function safeDescr($descr)
    {
        return (string)preg_replace('/[^a-zA-Z0-9,._\-\/\ ]/', ' ', $descr);
    }

    /**
     * Only track update and create primarily, just put all others in an "other" bin
     *
     * @param $type
     * @return string
     */
    private function coalesceStatisticType($type)
    {
        return ($type == 'update' || $type == 'create') ? $type : 'other';
    }
}
