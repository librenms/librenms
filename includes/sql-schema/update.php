<?php

/* Observium Network Management and Monitoring System
 * Copyright (C) 2006-2012, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

if (!isset($debug))
{
  # Not called from within discovery, let's load up the necessary stuff.

  include("config.php");
  include("includes/functions.php");

  $options = getopt("d");
  if (isset($options['d']))
  {
    $debug = TRUE;
  }
  else
  {
    $debug = FALSE;
  }
}

if ($db_rev = @dbFetchCell("SELECT version FROM `dbSchema`")) {} else
{
  $db_rev = 0;
}

$insert = 0;

# For transition from old system
if ($old_rev = @dbFetchCell("SELECT revision FROM `dbSchema`"))
{
  echo "-- Transitioning from old revision-based schema to database version system\n";
  $db_rev = 6;

  if ($old_rev < 1000) { $db_rev = 1; }
  if ($old_rev < 1435) { $db_rev = 2; }
  if ($old_rev < 2245) { $db_rev = 3; }
  if ($old_rev < 2804) { $db_rev = 4; }
  if ($old_rev < 2827) { $db_rev = 5; }

  $insert = 1;
}

$updating = 0;

$include_dir_regexp = "/\.sql$/";

if ($handle = opendir($config['install_dir'] . '/sql-schema'))
{
  while (false !== ($file = readdir($handle)))
  {
    if (filetype($config['install_dir'] . '/sql-schema/' . $file) == 'file' && preg_match($include_dir_regexp, $file))
    {
      $filelist[] = $file;
    }
  }
  closedir($handle);
}

asort($filelist);

foreach ($filelist as $file)
{
  list($filename,$extension) = explode('.',$file,2);
  if ($filename > $db_rev)
  {
    if (!$updating)
    {
      echo "-- Updating database schema\n";
    }

    echo sprintf("%03d",$db_rev) . " -> " . sprintf("%03d",$filename) . " ...";

    $err = 0;

    if ($fd = @fopen($config['install_dir'] . '/sql-schema/' . $file,'r'))
    {
      $data = fread($fd,4096);
      while (!feof($fd))
      {
        $data .= fread($fd,4096);
      }

      foreach (explode("\n", $data) as $line)
      {
        if (trim($line))
        {
          if ($debug) { echo("$line \n"); }
          $update = mysql_query($line);
          if (!$update)
          {
            $err++;
            if ($debug) { echo(mysql_error() . "\n"); }
          }
        }
      }

      echo " done ($err errors).\n";
    }
    else
    {
      echo " Could not open file!\n";
    }

    $updating++;
    $db_rev = $filename;
  }
}

if ($updating)
{
  if ($insert)
  {
    dbInsert(array('version' => $db_rev), 'dbSchema');
  } else {
    dbUpdate(array('version' => $db_rev), 'dbSchema');
  }
  echo "-- Done\n";
}

?>
