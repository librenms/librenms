<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package rrdtool
 * @author Adam Armstrong <adama@memetic.org>
 *
 */

/**
 * Opens up a pipe to RRDTool using handles provided
 *
 * @return boolean
 * @global config
 * @global debug
 * @param &rrd_process
 * @param &rrd_pipes
 */

function rrdtool_pipe_open(&$rrd_process, &$rrd_pipes)
{
  global $config, $debug;

  $command = $config['rrdtool'] . " -";

  $descriptorspec = array(
     0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
     1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
     2 => array("pipe", "w")   // stderr is a pipe that the child will write to
  );

  $cwd = $config['rrd_dir'];
  $env = array();

  $rrd_process = proc_open($command, $descriptorspec, $rrd_pipes, $cwd, $env);

  stream_set_blocking($rrd_pipes[1], 0);
  stream_set_blocking($rrd_pipes[2], 0);

  if (is_resource($rrd_process))
  {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt
    return TRUE;
  }
}

/**
 * Closes the pipe to RRDTool
 *
 * @return integer
 * @param resource rrd_process
 * @param array rrd_pipes
 */

function rrdtool_pipe_close(&$rrd_process, &$rrd_pipes)
{
  global $debug;

  if ($debug)
  {
    echo stream_get_contents($rrd_pipes[1]);
    echo stream_get_contents($rrd_pipes[2]);
  }

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
 * @param string graph_file
 * @param string options
 */

function rrdtool_graph($graph_file, $options)
{
  global $config, $debug;

  rrdtool_pipe_open($rrd_process, $rrd_pipes);

  if (is_resource($rrd_process))
  {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt

    if ($config['rrdcached'])
    {
      fwrite($rrd_pipes[0], "graph --daemon " . $config['rrdcached'] . " $graph_file $options");
    } else {
      fwrite($rrd_pipes[0], "graph $graph_file $options");
    }

    fclose($rrd_pipes[0]);

    while (strlen($line) < 1) {
      $line = fgets($rrd_pipes[1],1024);
      $data .= $line;
    }

    $return_value = rrdtool_pipe_close($rrd_process, $rrd_pipes);

    if ($debug)
    {
        echo("<p>");
        if ($debug) { echo("graph $graph_file $options"); }
        echo("</p><p>");
        echo "command returned $return_value ($data)\n";
        echo("</p>");
    }
    return $data;
  } else {
    return 0;
  }
}

/**
 * Generates and pipes a command to rrdtool
 *
 * @param string command
 * @param string filename
 * @param string options
 * @global config
 * @global debug
 * @global rrd_pipes
 */

function rrdtool($command, $filename, $options)
{
  global $config, $debug, $rrd_pipes;

  $cmd = "$command $filename $options";
  if ($command != "create" && $config['rrdcached'])
  {
    $cmd .= " --daemon " . $config['rrdcached'];
  }

  if ($config['norrd'])
  {
    print Console_Color::convert("%g RRD Disabled %n", false);
  } else {
    fwrite($rrd_pipes[0], $cmd."\n");
  }
  if ($debug)
  {
    echo stream_get_contents($rrd_pipes[1]);
    echo stream_get_contents($rrd_pipes[2]);

    echo("<p>");
    echo("\n".$cmd."\n");
    echo("</p>");
  } else {
    $tmp  = stream_get_contents($rrd_pipes[1]).stream_get_contents($rrd_pipes[2]);
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
  global $config, $debug;

  if ($config['norrd'])
  {
    print Console_Color::convert("%g RRD Disabled %n", false);
  } else {
    $command = $config['rrdtool'] . " create $filename $options";
  }
  if ($debug) { echo($command."\n"); }

  return shell_exec($command);

#  return rrdtool("create", $filename, $options);
}

function rrdtool_update($filename, $options)
{
  /// Do some sanitisation on the data if passed as an array.
  if(is_array($options))
  {
    $values[] = "N";
    foreach($options as $value)
    {
      if(!is_numeric($value)) { $value = U; }
      $values[] = $value;
    }
    $options = implode(':', $values);
  }

  return rrdtool("update", $filename, $options);
}

function rrdtool_fetch($filename, $options)
{
  return rrdtool("fetch", $filename, $options);
}

function rrdtool_last($filename, $options)
{
  return rrdtool("last", $filename, $options);
}

function rrdtool_lastupdate($filename, $options)
{
  return rrdtool("lastupdate", $filename, $options);
}

?>
