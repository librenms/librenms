<?php

if($device['os_group'] == "unix")
{

  echo("Observium UNIX Agent: ");

  $port='6556';

  $agent = fsockopen($device['hostname'], $port, $errno, $errstr, 10);

  if (!$agent)
  {
    echo "Connection to UNIX agent failed on port ".$port.".";
  } else {
    while (!feof($agent))
    {
      $agent_raw .= fgets($agent, 128);
    }
  }

  if (!empty($agent_raw))
  {
    foreach (explode("<<<", $agent_raw) as $section)
    {

      list($section, $data) = explode(">>>", $section);
      list($sa, $sb) = explode("-", $section, 2);
      if(!empty($sa) && !empty($sb)) 
      {
        $agent_data[$sa][$sb] = trim($data);
      } else {
        $agent_data[$section] = trim($data);
      }
    }

    #print_r($agent_data);

    include("unix-agent/packages.inc.php");
    include("unix-agent/munin-plugins.inc.php");

    ### Processes
    if (!empty($agent_data['ps']))
    {
      echo("\nProcesses: ");
      foreach (explode("\n", $agent_data['ps']) as $process)
      {
        $process = preg_replace("/\((.*),([0-9]*),([0-9]*),([0-9\.]*)\)\ (.*)/", "\\1|\\2|\\3|\\4|\\5", $process);
        list($user, $vsz, $rss, $pcpu, $command) = explode("|", $process, 5);
          $processlist[] = array('user' => $user, 'vsz' => $vsz, 'rss' => $rss, 'pcpu' => $pcpu, 'command' => $command);
      }
      #print_r($processlist);
    }

    ### Apache
    if (!empty($agent_data['apache']))
    {
      $app_found['apache'] = TRUE;
      if (dbFetchCell("SELECT COUNT(*) FROM `applications` WHERE `device_id` = ? AND `app_type` = ?", array($device['device_id'], 'apache')) == "0")
      {
        echo("Found new application 'Apache'\n");
        dbInsert(array('device_id' => $device['device_id'], 'app_type' => 'apache'), 'applications');
      }
    }

    ### MySQL
    if (!empty($agent_data['mysql']))
    {
      $app_found['mysql'] = TRUE;
      if (dbFetchCell("SELECT COUNT(*) FROM `applications` WHERE `device_id` = ? AND `app_type` = ?", array($device['device_id'], 'mysql')) == "0")
      {
        echo("Found new application 'MySQL'\n");
        dbInsert(array('device_id' => $device['device_id'], 'app_type' => 'mysql'), 'applications');
      }
    }

    ### DRBD
    if (!empty($agent_data['drbd']))
    {
      $agent_data['drbd_raw'] = $agent_data['drbd'];
      $agent_data['drbd'] = array();
      foreach (explode("\n", $agent_data['drbd_raw']) as $drbd_entry)
      {
        list($drbd_dev, $drbd_data) = explode(":", $drbd_entry);
        if (preg_match("/^drbd/", $drbd_dev))
        {
          $agent_data['drbd'][$drbd_dev] = $drbd_data;
          if (dbFetchCell("SELECT COUNT(*) FROM `applications` WHERE `device_id` = ? AND `app_type` = ? AND `app_instance` = ?", array($device['device_id'], 'drbd', $drbd_dev)) == "0")
          {
            echo("Found new application 'DRBd' $drbd_dev\n");
            dbInsert(array('device_id' => $device['device_id'], 'app_type' => 'drbd', 'app_instance' => $drbd_dev), 'applications');
          }
        }
      }
    }
  }

  echo("\n");
}

?>
