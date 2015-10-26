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


function rrdtool_pipe_open(&$rrd_process, &$rrd_pipes) {
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


function rrdtool_pipe_close($rrd_process, &$rrd_pipes) {
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


function rrdtool_graph($graph_file, $options) {
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


function rrdtool($command, $filename, $options) {
    global $config, $debug, $rrd_pipes, $console_color;

    if ($config['rrdcached'] && ($config['rrdtool_version'] >= 1.5 || $command != "create")) {
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


function rrdtool_create($filename, $options) {
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


function rrdtool_update($filename, $options) {
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

}


function rrdtool_fetch($filename, $options) {
    return rrdtool('fetch', $filename, $options);

}


function rrdtool_last($filename, $options) {
    return rrdtool('last', $filename, $options);

}


function rrdtool_lastupdate($filename, $options){
    return rrdtool('lastupdate', $filename, $options);

}


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
    $result = str_replace('%', '%%', $result);
    if (is_numeric($maxlength)) {
        $extra  = substr_count($string, ':', 0, $maxlength);
        $result = substr(str_pad($result, $maxlength), 0, ($maxlength + $extra));
        if ($extra > 0) {
            $result = substr($result, 0, (-1 * $extra));
        }
    }

    $result = str_replace(':', '\:', $result);
    return $result.' ';

}
