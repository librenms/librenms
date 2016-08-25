<?php
/**
 * rrdtool.inc.php
 *
 * Helper for processing rrd requests efficiently
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
 * @author     Adam Armstrong <adama@memetic.org>
 * @author     Tony Murray <murraytony@gmail.com>
 */

use LibreNMS\Exceptions\FileExistsException;

/**
 * Opens up a pipe to RRDTool using handles provided
 *
 * @param bool $dual_process start an additional process that's output should be read after every command
 * @return bool the process(s) have been successfully started
 */
function rrdtool_initialize($dual_process = true)
{
    global $config, $rrd_async_process, $rrd_async_pipes, $rrd_sync_process, $rrd_sync_pipes;

    $command = $config['rrdtool'] . ' -';

    $descriptor_spec = array(
        0 => array('pipe', 'r'), // stdin  is a pipe that the child will read from
        1 => array('pipe', 'w'), // stdout is a pipe that the child will write to
        2 => array('pipe', 'w'), // stderr is a pipe that the child will write to
    );

    $cwd = $config['rrd_dir'];
    $env = array();

    if(!is_resource($rrd_async_process)) {
        $rrd_async_process = proc_open($command, $descriptor_spec, $rrd_async_pipes, $cwd, $env);
        stream_set_blocking($rrd_async_pipes[1], false);
        stream_set_blocking($rrd_async_pipes[2], false);
    }

    if ($dual_process && !is_resource($rrd_sync_process)) {
        $rrd_sync_process = proc_open($command, $descriptor_spec, $rrd_sync_pipes, $cwd, $env);
        stream_set_blocking($rrd_sync_pipes[1], false);
        stream_set_blocking($rrd_sync_pipes[2], false);
    }

    return is_resource($rrd_async_process) && ($dual_process ? is_resource($rrd_sync_process) : true);
}

/**
 * Close all open rrdtool processes.
 * This should be done before exiting a script that has called rrdtool_initilize()
 *
 * @return bool indicates success
 */
function rrdtool_terminate() {
    global $rrd_async_process, $rrd_async_pipes, $rrd_sync_process, $rrd_sync_pipes;

    $ret = rrdtool_pipe_close($rrd_async_process, $rrd_async_pipes);
    if ($rrd_sync_pipes) {
        $ret = rrdtool_pipe_close($rrd_sync_process, $rrd_sync_pipes) && $ret;
    }

    return $ret;
}

/**
 * Closes the pipe to RRDTool
 *
 * @internal
 * @param  resource $rrd_process
 * @param  array $rrd_pipes
 * @return integer
 */
function rrdtool_pipe_close($rrd_process, &$rrd_pipes)
{
    global $vdebug;
    if ($vdebug) {
        d_echo(stream_get_contents($rrd_pipes[1]));
        d_echo(stream_get_contents($rrd_pipes[2]));
    }

    fclose($rrd_pipes[0]);
    fclose($rrd_pipes[1]);
    fclose($rrd_pipes[2]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    return proc_terminate($rrd_process);
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
    global $config, $debug, $rrd_async_pipes;

    if (rrdtool_initialize(false)) {
        if ($config['rrdcached']) {
            $options = str_replace(array($config['rrd_dir'].'/', $config['rrd_dir']), '', $options);
            fwrite($rrd_async_pipes[0], 'graph --daemon ' . $config['rrdcached'] . " $graph_file $options");
        } else {
            fwrite($rrd_async_pipes[0], "graph $graph_file $options");
        }

        fclose($rrd_async_pipes[0]);

        $line = "";
        $data = "";
        while (strlen($line) < 1) {
            $line = fgets($rrd_async_pipes[1], 1024);
            $data .= $line;
        }

        $return_value = rrdtool_terminate();

        if ($debug) {
            echo '<p>';
            echo "graph $graph_file $options";

            echo '</p><p>';
            echo "command returned $return_value ($data)\n";
            echo '</p>';
        }

        return $data;
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
 * @global $config
 * @global $debug
 * @global $rrd_pipes
 */
function rrdtool($command, $filename, $options)
{
    global $config, $debug, $vdebug, $rrd_async_pipes, $rrd_sync_pipes;

    try {
        $cmd = rrdtool_build_command($command, $filename, $options);
    } catch (FileExistsException $e) {
        c_echo('RRD[%g' . $filename . " already exists%n]\n", $debug);
        return array(null, null);
    }

    c_echo("RRD[%g$cmd%n]\n", $debug);

    // do not write rrd files, but allow read-only commands
    $ro_commands = array('graph', 'graphv', 'dump', 'fetch', 'first', 'last', 'lastupdate', 'info', 'xport');
    if ($config['norrd'] && !in_array($command, $ro_commands)) {
        c_echo('[%rRRD Disabled%n]');
        return array(null, null);
    }

    // send the command!
    if($command == 'last' && $rrd_sync_pipes) {
        fwrite($rrd_sync_pipes[0], $cmd . "\n");

        // this causes us to block until we receive output for up to the timeout in seconds
        stream_select($r = $rrd_sync_pipes, $w = null, $x = null, 10);
        $output = array(stream_get_contents($rrd_sync_pipes[1]), stream_get_contents($rrd_sync_pipes[2]));

    } else {
        fwrite($rrd_async_pipes[0], $cmd . "\n");
        $output = array(stream_get_contents($rrd_async_pipes[1]), stream_get_contents($rrd_async_pipes[2]));
    }

    if ($vdebug) {
        echo 'RRDtool Output: ';
        echo $output[0];
        echo $output[1];
    }

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
    global $config;

    if ($command == 'create') {
        // <1.4.3 doesn't support -O, so make sure the file doesn't exist
        if (version_compare($config['rrdtool_version'], '1.4.3', '<')) {
            if (is_file($filename)) {
                throw new FileExistsException();
            }
        } else {
            $options .= ' -O';
        }
    }

    // no remote for create < 1.5.5 and tune < 1.5
    if ($config['rrdcached'] &&
        !($command == 'create' && version_compare($config['rrdtool_version'], '1.5.5', '<')) &&
        !($command == 'tune' && $config['rrdcached'] && version_compare($config['rrdtool_version'], '1.5', '<'))
    ) {
        // only relative paths if using rrdcached
        $filename = str_replace(array($config['rrd_dir'].'/', $config['rrd_dir']), '', $filename);

        return "$command $filename $options --daemon " . $config['rrdcached'];
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
    global $config;
    if ($config['rrdcached'] && version_compare($config['rrdtool_version'], '1.5', '>=')) {
        $chk = rrdtool('last', $filename, '');
        $filename = str_replace(array($config['rrd_dir'].'/', $config['rrd_dir']), '', $filename);
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
    $result = str_replace('%', '%%', $result);          # double percent signs
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
    global $config;
    $filename = safename(is_array($extra) ? implode("-", $extra) : $extra);
    return implode("/", array($config['rrd_dir'], $host, $filename.$extension));
} // rrd_name

/**
 * Generates a filename for a proxmox cluster rrd
 *
 * @param $pmxcluster
 * @param $vmid
 * @param $vmport
 * @return string full path to the rrd.
 */
function proxmox_rrd_name($pmxcluster, $vmid, $vmport) {
    global $config;

    $pmxcdir = join('/', array($config['rrd_dir'], 'proxmox', safename($pmxcluster)));
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
 *   rrd_def     array|string: (required) an array of rrd field definitions example: "DS:dataName:COUNTER:600:U:100000000000"
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
    global $config;

    $rrd_name = $tags['rrd_name'] ?: $measurement;
    $step = $tags['rrd_step'] ?: 300;
    $oldname = $tags['rrd_oldname'];
    if (isset($oldname) && !empty($oldname)) {
        rrd_file_rename($device, $oldname, $rrd_name);
    }

    if (isset($tags['rrd_proxmox_name'])) {
        $pmxvars = $tags['rrd_proxmox_name'];
        $rrd = proxmox_rrd_name($pmxvars['pmxcluster'], $pmxvars['vmid'], $pmxvars['vmport']);
    } else {
        $rrd = rrd_name($device['hostname'], $rrd_name);
    }

    if ($tags['rrd_def'] && !rrdtool_check_rrd_exists($rrd)) {
        $rrd_def = is_array($tags['rrd_def']) ? $tags['rrd_def'] : array($tags['rrd_def']);
        // add the --step and the rra definitions to the command
        $newdef = "--step $step " . implode(' ', $rrd_def) . $config['rrd_rra'];
        rrdtool('create', $rrd, $newdef);
    }

    rrdtool_update($rrd, $fields);
} // rrdtool_data_update


/**
 * rename an rrdfile, can only be done on the LibreNMS server hosting the rrd files
 *
 * @param array $device Device object
 * @param string $oldname RRD name array as used with rrd_name()
 * @param string $newname RRD name array as used with rrd_name()
 * @return bool indicating rename success or failure
 */
function rrd_file_rename($device, $oldname, $newname)
{
    $oldrrd = rrd_name($device['hostname'], $oldname);
    $newrrd = rrd_name($device['hostname'], $newname);
    if (is_file($oldrrd) && !is_file($newrrd)) {
        if (rename($oldrrd, $newrrd)) {
            log_event("Renamed $oldrrd to $newrrd", $device, "poller");
            return true;
        } else {
            log_event("Failed to rename $oldrrd to $newrrd", $device, "poller");
            return false;
        }
    } else {
        // we don't need to rename the file
        return true;
    }
} // rrd_file_rename
