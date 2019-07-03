<?php
/**
 * rrdtool.inc.php
 *
 * Helper for processing rrdtool requests efficiently
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
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Config;
use LibreNMS\Exceptions\FileExistsException;
use LibreNMS\Proc;

/**
 * Opens up a pipe to RRDTool using handles provided
 *
 * @param bool $dual_process start an additional process that's output should be read after every command
 * @return bool the process(s) have been successfully started
 */
function rrdtool_initialize($dual_process = true)
{
    global $rrd_sync_process, $rrd_async_process;

    $command = Config::get('rrdtool') . ' -';

    $descriptor_spec = array(
        0 => array('pipe', 'r'), // stdin  is a pipe that the child will read from
        1 => array('pipe', 'w'), // stdout is a pipe that the child will write to
        2 => array('pipe', 'w'), // stderr is a pipe that the child will write to
    );

    $cwd = Config::get('rrd_dir');

    if (!rrdtool_running($rrd_sync_process)) {
        $rrd_sync_process = new Proc($command, $descriptor_spec, $cwd);
    }

    if ($dual_process && !rrdtool_running($rrd_async_process)) {
        $rrd_async_process = new Proc($command, $descriptor_spec, $cwd);
        $rrd_async_process->setSynchronous(false);
    }

    return rrdtool_running($rrd_sync_process) && ($dual_process ? rrdtool_running($rrd_async_process) : true);
}

/**
 * Checks if the variable is a running rrdtool process
 *
 * @param $process
 * @return bool
 */
function rrdtool_running(&$process)
{
    return isset($process) && $process instanceof Proc && $process->isRunning();
}

/**
 * Close all open rrdtool processes.
 * This should be done before exiting a script that has called rrdtool_initilize()
 */
function rrdtool_close()
{
    global $rrd_sync_process, $rrd_async_process;
    /** @var Proc $rrd_sync_process */
    /** @var Proc $rrd_async_process */

    if (rrdtool_running($rrd_sync_process)) {
        $rrd_sync_process->close('quit');
    }
    if (rrdtool_running($rrd_async_process)) {
        $rrd_async_process->close('quit');
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
function rrdtool_graph($graph_file, $options)
{
    global $debug, $rrd_sync_process;
    /** @var Proc $rrd_sync_process */

    if (rrdtool_initialize(false)) {
        $cmd = rrdtool_build_command('graph', $graph_file, $options);

        $output = implode($rrd_sync_process->sendCommand($cmd));

        if ($debug) {
            echo "<p>$cmd</p>";
            echo "<p>command returned ($output)</p>";
        }

        return $output;
    } else {
        return 0;
    }
}


/**
 * Generates and pipes a command to rrdtool
 *
 * @internal
 * @param string $command create, update, updatev, graph, graphv, dump, restore, fetch, tune, first, last, lastupdate, info, resize, xport, flushcached
 * @param string $filename The full patth to the rrd file
 * @param string $options rrdtool command options
 * @return array the output of stdout and stderr in an array
 * @throws FileExistsException thrown when a create command is set to rrdtool < 1.4 and the rrd already exists
 * @throws Exception thrown when the rrdtool process(s) cannot be started
 */
function rrdtool($command, $filename, $options)
{
    global $debug, $vdebug, $rrd_async_process, $rrd_sync_process;
    /** @var Proc $rrd_sync_process */
    /** @var Proc $rrd_async_process */

    $start_time = microtime(true);

    try {
        $cmd = rrdtool_build_command($command, $filename, $options);
    } catch (FileExistsException $e) {
        c_echo('RRD[%g' . $filename . " already exists%n]\n", $debug);
        return array(null, null);
    }

    c_echo("RRD[%g$cmd%n]\n", $debug);

    // do not write rrd files, but allow read-only commands
    $ro_commands = array('graph', 'graphv', 'dump', 'fetch', 'first', 'last', 'lastupdate', 'info', 'xport');
    if (!empty(Config::get('norrd')) && !in_array($command, $ro_commands)) {
        c_echo('[%rRRD Disabled%n]', !Config::get('hide_rrd_disabled'));
        return array(null, null);
    }

    // send the command!
    if ($command == 'last' && rrdtool_initialize(false)) {
        // send this to our synchronous process so output is guaranteed
        $output = $rrd_sync_process->sendCommand($cmd);
    } elseif (rrdtool_initialize()) {
        // don't care about the return of other commands, so send them to the faster async process
        $output = $rrd_async_process->sendCommand($cmd);
    } else {
        throw new Exception('rrdtool could not start');
    }

    if ($vdebug) {
        echo 'RRDtool Output: ';
        echo $output[0];
        echo $output[1];
    }

    recordRrdStatistic($command, $start_time);
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
function rrdtool_build_command($command, $filename, $options)
{
    if ($command == 'create') {
        // <1.4.3 doesn't support -O, so make sure the file doesn't exist
        if (version_compare(Config::get('rrdtool_version', '1.4'), '1.4.3', '<')) {
            if (is_file($filename)) {
                throw new FileExistsException();
            }
        } else {
            $options .= ' -O';
        }
    }

    // no remote for create < 1.5.5 and tune < 1.5
    $rrdtool_version = Config::get('rrdtool_version', '1.4');
    if (Config::get('rrdcached') &&
        !($command == 'create' && version_compare($rrdtool_version, '1.5.5', '<')) &&
        !($command == 'tune' && Config::get('rrdcached') && version_compare($rrdtool_version, '1.5', '<'))
    ) {
        // only relative paths if using rrdcached
        $filename = str_replace([Config::get('rrd_dir') . '/', Config::get('rrd_dir')], '', $filename);
        $options = str_replace([Config::get('rrd_dir') . '/', Config::get('rrd_dir')], '', $options);

        return "$command $filename $options --daemon " . Config::get('rrdcached');
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
function rrdtool_check_rrd_exists($filename)
{
    if (Config::get('rrdcached') && version_compare(Config::get('rrdtool_version', '1.4'), '1.5', '>=')) {
        $chk = rrdtool('last', $filename, '');
        $filename = str_replace([Config::get('rrd_dir') . '/', Config::get('rrd_dir')], '', $filename);
        return !str_contains(implode($chk), "$filename': No such file or directory");
    } else {
        return is_file($filename);
    }
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
function rrdtool_update($filename, $data)
{
    $values = array();
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
        return rrdtool('update', $filename, $data);
    } else {
        return 'Bad options passed to rrdtool_update';
    }
} // rrdtool_update


/**
 * Escapes strings for RRDtool
 *
 * @param string $string the string to escape
 * @param integer $length if passed, string will be padded and trimmed to exactly this length (after rrdtool unescapes it)
 * @return string
 */
function rrdtool_escape($string, $length = null)
{
    $result = shorten_interface_type($string);
    $result = str_replace("'", '', $result);            # remove quotes
    if (is_numeric($length)) {
        $extra = substr_count($string, ':', 0, $length);
        $result = substr(str_pad($result, $length), 0, ($length + $extra));
        if ($extra > 0) {
            $result = substr($result, 0, (-1 * $extra));
        }
    }

    $result = str_replace(':', '\:', $result);          # escape colons
    return $result.' ';
} // rrdtool_escape


/**
 * Generates a filename based on the hostname (or IP) and some extra items
 *
 * @param string $host Host name
 * @param array|string $extra Components of RRD filename - will be separated with "-", or a pre-formed rrdname
 * @param string $extension File extension (default is .rrd)
 * @return string the name of the rrd file for $host's $extra component
 */
function rrd_name($host, $extra, $extension = ".rrd")
{
    $filename = safename(is_array($extra) ? implode("-", $extra) : $extra);
    return implode("/", array(get_rrd_dir($host), $filename.$extension));
} // rrd_name


/**
 * Generates a path based on the hostname (or IP)
 *
 * @param string $host Host name
 * @return string the name of the rrd directory for $host
 */
function get_rrd_dir($host)
{
    $host = str_replace(':', '_', trim($host, '[]'));
    return implode("/", [Config::get('rrd_dir'), $host]);
} // rrd_dir


/**
 * Generates a filename for a proxmox cluster rrd
 *
 * @param $pmxcluster
 * @param $vmid
 * @param $vmport
 * @return string full path to the rrd.
 */
function proxmox_rrd_name($pmxcluster, $vmid, $vmport)
{
    $pmxcdir = join('/', [Config::get('rrd_dir'), 'proxmox', safename($pmxcluster)]);
    // this is not needed for remote rrdcached
    if (!is_dir($pmxcdir)) {
        mkdir($pmxcdir, 0775, true);
    }

    return join('/', array($pmxcdir, safename($vmid.'_netif_'.$vmport.'.rrd')));
}

/**
 * Modify an rrd file's max value and trim the peaks as defined by rrdtool
 *
 * @param string $type only 'port' is supported at this time
 * @param string $filename the path to the rrd file
 * @param integer $max the new max value
 * @return bool
 */
function rrdtool_tune($type, $filename, $max)
{
    $fields = array();
    if ($type === 'port') {
        if ($max < 10000000) {
            return false;
        }
        $max = $max / 8;
        $fields = array(
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
        );
    }
    if (count($fields) > 0) {
        $options = "--maximum " . implode(":$max --maximum ", $fields) . ":$max";
        rrdtool('tune', $filename, $options);
    }
    return true;
} // rrdtool_tune


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
function rrdtool_data_update($device, $measurement, $tags, $fields)
{
    $rrd_name = $tags['rrd_name'] ?: $measurement;
    $step = $tags['rrd_step'] ?: Config::get('rrd.step');
    $oldname = $tags['rrd_oldname'];
    if (!empty($oldname)) {
        rrd_file_rename($device, $oldname, $rrd_name);
    }

    if (isset($tags['rrd_proxmox_name'])) {
        $pmxvars = $tags['rrd_proxmox_name'];
        $rrd = proxmox_rrd_name($pmxvars['pmxcluster'], $pmxvars['vmid'], $pmxvars['vmport']);
    } else {
        $rrd = rrd_name($device['hostname'], $rrd_name);
    }

    if (isset($tags['rrd_def']) && !rrdtool_check_rrd_exists($rrd)) {
        $newdef = "--step $step " . $tags['rrd_def'] . Config::get('rrd_rra');
        rrdtool('create', $rrd, $newdef);
    }

    rrdtool_update($rrd, $fields);
} // rrdtool_data_update


/**
 * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
 *
 * @param array $device Device object
 * @param string|array $oldname RRD name array as used with rrd_name()
 * @param string|array $newname RRD name array as used with rrd_name()
 * @return bool indicating rename success or failure
 */
function rrd_file_rename($device, $oldname, $newname)
{
    $oldrrd = rrd_name($device['hostname'], $oldname);
    $newrrd = rrd_name($device['hostname'], $newname);
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
} // rrd_file_rename
