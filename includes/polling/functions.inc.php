<?php

function poll_sensor($device, $class, $unit)
{
  global $config, $memcache;

  foreach (dbFetchRows("SELECT * FROM `sensors` WHERE `sensor_class` = ? AND `device_id` = ? AND `poller_type` = 'snmp'", array($class, $device['device_id'])) as $sensor)
  {
    echo("Checking $class " . $sensor['sensor_descr'] . "... ");

    if ($class == "temperature")
    {
      for ($i = 0;$i < 5;$i++) # Try 5 times to get a valid temp reading
      {
        if ($debug) echo("Attempt $i ");
        $sensor_value = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");
        $sensor_value = trim(str_replace("\"", "", $sensor_value));

        if ($sensor_value < 9000) break; # TME sometimes sends 999.9 when it is right in the middle of an update;
        sleep(1); # Give the TME some time to reset
      }
    } else {
      $sensor_value = snmp_get($device, $sensor['sensor_oid'], "-OUqnv", "SNMPv2-MIB");
    }

    if ($sensor_value == -32768) { echo("Invalid (-32768) "); $sensor_value = 0; }

    if ($sensor['sensor_divisor'])    { $sensor_value = $sensor_value / $sensor['sensor_divisor']; }
    if ($sensor['sensor_multiplier']) { $sensor_value = $sensor_value * $sensor['sensor_multiplier']; }

    $rrd_file = get_sensor_rrd($device, $sensor);

    if (!is_file($rrd_file))
    {
      rrdtool_create($rrd_file,"--step 300 \
      DS:sensor:GAUGE:600:-20000:20000 ".$config['rrd_rra']);
    }

    echo("$sensor_value $unit\n");

    rrdtool_update($rrd_file,"N:$sensor_value");

    # FIXME also warn when crossing WARN level!!
    if ($sensor['sensor_limit_low'] != "" && $sensor['sensor_current'] > $sensor['sensor_limit_low'] && $sensor_value <= $sensor['sensor_limit_low'])
    {
      $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is under threshold: " . $sensor_value . "$unit (< " . $sensor['sensor_limit'] . "$unit)";
      notify($device, ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
      echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
      log_event(ucfirst($class) . ' ' . $sensor['sensor_descr'] . " under threshold: " . $sensor_value . " $unit (< " . $sensor['sensor_limit_low'] . " $unit)", $device, $class, $sensor['sensor_id']);
    }
    else if ($sensor['sensor_limit'] != "" && $sensor['sensor_current'] < $sensor['sensor_limit'] && $sensor_value >= $sensor['sensor_limit'])
    {
      $msg  = ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'] . " is over threshold: " . $sensor_value . "$unit (> " . $sensor['sensor_limit'] . "$unit)";
      notify($device, ucfirst($class) . " Alarm: " . $device['hostname'] . " " . $sensor['sensor_descr'], $msg);
      echo("Alerting for " . $device['hostname'] . " " . $sensor['sensor_descr'] . "\n");
      log_event(ucfirst($class) . ' ' . $sensor['sensor_descr'] . " above threshold: " . $sensor_value . " $unit (> " . $sensor['sensor_limit'] . " $unit)", $device, $class, $sensor['sensor_id']);
    }

    if ($config['memcached']['enable'])
    {
      $memcache->set('sensor-'.$sensor['sensor_id'].'-value', $sensor_value);
    } else {
      dbUpdate(array('sensor_current' => $sensor_value), 'sensors', '`sensor_class` = ? AND `sensor_id` = ?', array($class, $sensor['sensor_id']));
    }
  }
}

function poll_device($device, $options)
{
  global $config, $device, $polled_devices, $db_stats, $memcache;

  $attribs = get_dev_attribs($device['device_id']);

  $status = 0; unset($array);
  $device_start = utime();  // Start counting device poll time

  echo($device['hostname'] . " ".$device['device_id']." ".$device['os']." ");
  if ($config['os'][$device['os']]['group'])
  {
    $device['os_group'] = $config['os'][$device['os']]['group'];
    echo("(".$device['os_group'].")");
  }
  echo("\n");

  unset($poll_update); unset($poll_update_query); unset($poll_separator);
  $poll_update_array = array();

  $host_rrd = $config['rrd_dir'] . "/" . $device['hostname'];
  if (!is_dir($host_rrd)) { mkdir($host_rrd); echo("Created directory : $host_rrd\n"); }

  $device['pingable'] = isPingable($device['hostname']);
  if ($device['pingable'])
  {
    $device['snmpable'] = isSNMPable($device);
    if ($device['snmpable'])
    {
      $status = "1";
    } else {
      echo("SNMP Unreachable");
      $status = "0";
    }
  } else {
    echo("Unpingable");
    $status = "0";
  }

  if ($device['status'] != $status)
  {
    $poll_update .= $poll_separator . "`status` = '$status'";
    $poll_separator = ", ";

    dbUpdate(array('status' => $status), 'devices', 'device_id=?', array($device['device_id']));
    dbInsert(array('importance' => '0', 'device_id' => $device['device_id'], 'message' => "Device is " .($status == '1' ? 'up' : 'down')), 'alerts');

    log_event('Device status changed to ' . ($status == '1' ? 'Up' : 'Down'), $device, ($status == '1' ? 'up' : 'down'));
    notify($device, "Device ".($status == '1' ? 'Up' : 'Down').": " . $device['hostname'], "Device ".($status == '1' ? 'up' : 'down').": " . $device['hostname']);
  }

  if ($status == "1")
  {
    $graphs = array();
    $oldgraphs = array();

    if ($options['m'])
    {
      foreach (explode(",", $options['m']) as $module)
      {
        if (is_file("includes/polling/".$module.".inc.php"))
        {
          include("includes/polling/".$module.".inc.php");
        }
      }
    } else {
      foreach ($config['poller_modules'] as $module => $module_status)
      {
        if ($attribs['poll_'.$module] || ( $module_status && !isset($attribs['poll_'.$module])))
        {
          include('includes/polling/'.$module.'.inc.php');
        } elseif (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] == "0") {
          echo("Module [ $module ] disabled on host.\n");
        } else {
          echo("Module [ $module ] disabled globally.\n");
        }
      }
    }

    if (!$options['m'])
    {
      ## FIXME EVENTLOGGING -- MAKE IT SO WE DO THIS PER-MODULE?
      ### This code cycles through the graphs already known in the database and the ones we've defined as being polled here
      ### If there any don't match, they're added/deleted from the database.
      ### Ideally we should hold graphs for xx days/weeks/polls so that we don't needlessly hide information.

      foreach (dbFetch("SELECT `graph` FROM `device_graphs` WHERE `device_id` = ?", array($device['device_id'])) as $graph)
      {
        if (!isset($graphs[$graph["graph"]]))
        {
          dbDelete('device_graphs', "`device_id` = ? AND `graph` = ?", array($device['device_id'], $graph["graph"]));
        } else {
          $oldgraphs[$graph["graph"]] = TRUE;
        }
      }

      foreach ($graphs as $graph => $value)
      {
        if (!isset($oldgraphs[$graph]))
        {
          echo("+");
          dbInsert(array('device_id' => $device['device_id'], 'graph' => $graph), 'device_graphs');
        }
        echo($graph." ");
      }
    }

    $device_end = utime(); $device_run = $device_end - $device_start; $device_time = substr($device_run, 0, 5);

    $update_array['last_polled'] = array('NOW()');
    $update_array['last_polled_timetaken'] = $device_time;

    #echo("$device_end - $device_start; $device_time $device_run");
    echo("Polled in $device_time seconds\n");

    if ($debug) { echo("Updating " . $device['hostname'] . " - ".print_r($update_array)." \n"); }

    $updated = dbUpdate($update_array, 'devices', '`device_id` = ?', array($device['device_id']));
    if ($updated) { echo("UPDATED!\n"); }

    unset($storage_cache); // Clear cache of hrStorage ** MAYBE FIXME? **
    unset($cache); // Clear cache (unify all things here?)
  }
}

?>
