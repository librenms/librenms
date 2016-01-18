<?php

/*
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage rrdtool
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

/**
 * Opens up a pipe to RRDTool using handles provided
 *
 * @return boolean
 * @global config
 * @global debug
 * @param  &rrd_process
 * @param  &rrd_pipes
 */


function rrdtool_pipe_open(&$rrd_process, &$rrd_pipes)
{
    global $config;

    $command = $config['rrdtool'].' -';

    $descriptorspec = array(
        0 => array(
            'pipe',
            'r',
        ),
        // stdin is a pipe that the child will read from
        1 => array(
            'pipe',
            'w',
        ),
        // stdout is a pipe that the child will write to
        2 => array(
            'pipe',
            'w',
        ),
        // stderr is a pipe that the child will write to
    );

    $cwd = $config['rrd_dir'];
    $env = array();

    $rrd_process = proc_open($command, $descriptorspec, $rrd_pipes, $cwd, $env);

    stream_set_blocking($rrd_pipes[1], 0);
    stream_set_blocking($rrd_pipes[2], 0);

    if (is_resource($rrd_process)) {
        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // Any error output will be appended to /tmp/error-output.txt
        return true;
    }

}


/**
 * Closes the pipe to RRDTool
 *
 * @return integer
 * @param  resource rrd_process
 * @param  array rrd_pipes
 */


function rrdtool_pipe_close($rrd_process, &$rrd_pipes)
{
    d_echo(stream_get_contents($rrd_pipes[1]));
    d_echo(stream_get_contents($rrd_pipes[2]));

    fclose($rrd_pipes[0]);
    fclose($rrd_pipes[1]);
    fclose($rrd_pipes[2]);

    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($rrd_process);

    return $return_value;

}


/**
 * Generates a graph file at $graph_file using $options
 * Opens its own rrdtool pipe.
 *
 * @return integer
 * @param  string graph_file
 * @param  string options
 */


function rrdtool_graph($graph_file, $options)
{
    global $config, $debug;

    rrdtool_pipe_open($rrd_process, $rrd_pipes);

    if (is_resource($rrd_process)) {
        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // Any error output will be appended to /tmp/error-output.txt
        if ($config['rrdcached']) {
            if (isset($config['rrdcached_dir']) && $config['rrdcached_dir'] !== false) {
                $options = str_replace($config['rrd_dir'].'/', './'.$config['rrdcached_dir'].'/', $options);
                $options = str_replace($config['rrd_dir'], './'.$config['rrdcached_dir'].'/', $options);
            }

            fwrite($rrd_pipes[0], 'graph --daemon '.$config['rrdcached']." $graph_file $options");
        }
        else {
            fwrite($rrd_pipes[0], "graph $graph_file $options");
        }

        fclose($rrd_pipes[0]);

        while (strlen($line) < 1) {
            $line  = fgets($rrd_pipes[1], 1024);
            $data .= $line;
        }

        $return_value = rrdtool_pipe_close($rrd_process, $rrd_pipes);

        if ($debug) {
            echo '<p>';
            if ($debug) {
                echo "graph $graph_file $options";
            }

            echo '</p><p>';
            echo "command returned $return_value ($data)\n";
            echo '</p>';
        }

        return $data;
    }
    else {
        return 0;
    }

}


/**
 * Generates and pipes a command to rrdtool
 *
 * @param  string command
 * @param  string filename
 * @param  string options
 * @global config
 * @global debug
 * @global rrd_pipes
 */


function rrdtool($command, $filename, $options)
{
    global $config, $debug, $rrd_pipes, $console_color;

    if ($config['rrdcached'] &&
        (version_compare($config['rrdtool_version'], '1.5.5', '>=') ||
        (version_compare($config['rrdtool_version'], '1.5', '>=') && $command != "tune") ||
        ($command != "create" && $command != "tune"))
        ) {
        if (isset($config['rrdcached_dir']) && $config['rrdcached_dir'] !== false) {
            $filename = str_replace($config['rrd_dir'].'/', './'.$config['rrdcached_dir'].'/', $filename);
            $filename = str_replace($config['rrd_dir'], './'.$config['rrdcached_dir'].'/', $filename);
        }

        $cmd = "$command $filename $options --daemon ".$config['rrdcached'];
    }
    else {
        $cmd = "$command $filename $options";
    }

    if ($config['norrd']) {
        print $console_color->convert('[%rRRD Disabled%n]');
    }
    else {
        fwrite($rrd_pipes[0], $cmd."\n");
    }

    if ($debug) {
        echo stream_get_contents($rrd_pipes[1]);
        echo stream_get_contents($rrd_pipes[2]);
        print $console_color->convert('RRD[%g'.$cmd.'%n] ');
    }
    else {
        return array(stream_get_contents($rrd_pipes[1]),stream_get_contents($rrd_pipes[2]));
    }

}


/**
 * Generates an rrd database at $filename using $options
 *
 * @param string filename
 * @param string options
 */


function rrdtool_create($filename, $options)
{
    global $config;
    if( $config['rrdcached'] && $config['rrdtool_version'] >= 1.5 ) {
        $chk = rrdtool('info', $filename, '');
        if (!empty($chk[0])) {
            return true;
        }
    }
    return rrdtool('create', $filename,  str_replace(array("\r", "\n"), '', $options));
}


/**
 * Updates an rrd database at $filename using $options
 * Where $options is an array, each entry which is not a number is replaced with "U"
 *
 * @param string filename
 * @param array options
 */


function rrdtool_update($filename, $options)
{
    $values = array();
    // Do some sanitisation on the data if passed as an array.

    if (is_array($options)) {
        $values[] = 'N';
        foreach ($options as $k => $v) {
            if (!is_numeric($v)) {
                $v = U;
            }

            $values[] = $v;
        }

        $options = implode(':', $values);
        return rrdtool('update', $filename, $options);
    }
    else {
        return 'Bad options passed to rrdtool_update';
    }
} // rrdtool_update


function rrdtool_fetch($filename, $options)
{
    return rrdtool('fetch', $filename, $options);
} // rrdtool_fetch


function rrdtool_last($filename, $options)
{
    return rrdtool('last', $filename, $options);
} // rrdtool_last


function rrdtool_lastupdate($filename, $options)
{
    return rrdtool('lastupdate', $filename, $options);
} // rrdtool_lastupdate


/**
 * Escapes strings for RRDtool,
 *
 * @return string
 *
 * @param string string to escape
 * @param integer if passed, string will be padded and trimmed to exactly this length (after rrdtool unescapes it)
 */
function rrdtool_escape($string, $maxlength=null){
    $result = shorten_interface_type($string);
    $result = str_replace("'", '', $result);            # remove quotes
    $result = str_replace('%', '%%', $result);          # double percent signs
    if (is_numeric($maxlength)) {
        $extra  = substr_count($string, ':', 0, $maxlength);
        $result = substr(str_pad($result, $maxlength), 0, ($maxlength + $extra));
        if ($extra > 0) {
            $result = substr($result, 0, (-1 * $extra));
        }
    }

    $result = str_replace(':', '\:', $result);          # escape colons
    return $result.' ';
} // rrdtool_escape


/*
 * @return the name of the rrd file for $host's $extra component
 * @param host Host name
 * @param extra Components of RRD filename - will be separated with "-"
 */
function rrd_name($host, $extra, $exten = ".rrd")
{
    global $config;
    $filename = safename(is_array($extra) ? implode("-", $extra) : $extra);
    return implode("/", array($config['rrd_dir'], $host, $filename.$exten));
} // rrd_name

function rrdtool_tune($type, $filename, $max) {
    $fields = array();
    if ($type === 'port') {
        if ($max < 10000000) {
            return false;
        }
        $max = $max / 8;
        $fields = array(
'INOCTETS','OUTOCTETS','INERRORS','OUTERRORS','INUCASTPKTS','OUTUCASTPKTS','INNUCASTPKTS','OUTNUCASTPKTS','INDISCARDS','OUTDISCARDS','INUNKNOWNPROTOS','INBROADCASTPKTS','OUTBROADCASTPKTS','INMULTICASTPKTS','OUTMULTICASTPKTS'
        );
    }
    if (count($fields) > 0) {
        $options = "--maximum " . implode(":$max --maximum ", $fields). ":$max";
        rrdtool('tune', $filename, $options);
    }
} // rrdtool_tune

/*
 * Please use this instead of creating & updating RRD files manually.
 * @param device Device object - only 'hostname' is used at present
 * @param name Array of rrdname components
 * @param def Array of data definitions
 * @param val Array of value definitions
 *
 */
function rrd_create_update($device, $name, $def, $val, $step=300)
{
    global $config;
    $rrd = rrd_name($device['hostname'], $name);

    if (!is_file($rrd) && $def != null) {
        // add the --step and the rra definitions to the array
        $newdef = "--step $step ".implode(' ', $def).$config['rrd_rra'];
        rrdtool_create($rrd, $newdef);
    }

    rrdtool_update($rrd, $val);
} // rrd_create_update


/*
 * @return bool indicating existence of RRD file
 * @param device Device object as used with rrd_create_update()
 * @param name RRD name array as used with rrd_create_update() and rrd_name()
 */
function rrd_file_exists($device, $name)
{
    return is_file(rrd_name($device['hostname'], $name));
} // rrd_file_exists


/*
 * @return bool indicating rename success or failure
 * @param device Device object as used with rrd_create_update()
 * @param oldname RRD name array as used with rrd_create_update() and rrd_name()
 * @param newname RRD name array as used with rrd_create_update() and rrd_name()
 */
function rrd_file_rename($device, $oldname, $newname)
{
    $oldrrd = rrd_name($device['hostname'], $oldname);
    $newrrd = rrd_name($device['hostname'], $newname);
    return rename($oldrrd, $newrrd);
} // rrd_file_rename
