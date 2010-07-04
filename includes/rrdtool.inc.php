<?php

function rrdtool_update($rrdfile, $rrdupdate)
{
  return rrdtool("update", $rrdfile, $rrdupdate);
}

function rrdtool_create($rrdfile, $rrdupdate)
{
  return rrdtool("create", $rrdfile, $rrdupdate);
}

function rrdtool_fetch($rrdfile, $rrdupdate)
{
  return rrdtool("fetch", $rrdfile, $rrdupdate);
}

function rrdtool_graph($rrdfile, $rrdupdate)
{
  return rrdtool("graph", $rrdfile, $rrdupdate);
}

function rrdtool_last($rrdfile, $rrdupdate)
{
  return rrdtool("last", $rrdfile, $rrdupdate);
}

function rrdtool_lastupdate($rrdfile, $rrdupdate)
{
  return rrdtool("lastupdate", $rrdfile, $rrdupdate);
}

function rrdtool($command, $file, $options)
{
  global $config; global $debug;

  $command = $config['rrdtool'] . " $command $file $options";
  if($config['rrdcached']) 
  {
    $command .= " --daemon " . $config['rrdcached'];
  }

  if ($debug) { echo($command."\n"); }
  return shell_exec($command);
}

?>
