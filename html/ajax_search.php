<?php

if (isset($_REQUEST['debug']))
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include_once("includes/functions.inc.php");
include_once("../includes/functions.php");
include_once("includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

$device = Array();
$ports = Array();

if (isset($_REQUEST['search']))
{
  $search = mres($_REQUEST['search']);

  if (strlen($search) >0)
  {
    $found = 0;

    if($_REQUEST['type'] == 'device') {

      // Device search
      $results = dbFetchRows("SELECT * FROM `devices` WHERE `hostname` LIKE '%" . $search . "%' OR `location` LIKE '%" . $search . "%' ORDER BY hostname LIMIT 8");
      if (count($results))
      {
        $found = 1;
        $devices = count($results);

        foreach ($results as $result)
        {
          $name = $result['hostname'];
          if (strlen($name) > 36) { $name = substr($name, 0, 36) . "..."; }
          if($result['disabled'] == 1)
          {
            $highlight_colour = '#808080';
          }
          elseif($result['ignored'] == 1 && $result['disabled'] == 0)
          {
            $highlight_colour = '#000000';
          }
          elseif($result['status'] == 0 && $result['ignore'] == 0 && $result['disabled'] == 0)
          {
            $highlight_colour = '#ff0000';
          }
          elseif($result['status'] == 1 && $result['ignore'] == 0 && $result['disabled'] == 0)
          {
            $highlight_colour = '#008000';
          }
          $num_ports = dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE device_id = ?", array($result['device_id']));
          $device[]=array('name'=>$name,
          'url'=> generate_device_url($result),
          'colours'=>$highlight_colour,
          'device_ports'=>$num_ports,
	  'device_image'=>getImageSrc($result),
	  'device_hardware'=>$result['hardware'],
	  'device_os'=>$config['os'][$result['os']]['text'],
          'version'=>$result['version'],
          'location'=>$result['location']);
        }
      }
      $json = json_encode($device);
      print_r($json);
      exit;
    
    } elseif($_REQUEST['type'] == 'ports') {
      // Search ports
      $results = dbFetchRows("SELECT `ports`.* AS P,`devices`.* AS D FROM `ports` LEFT JOIN `devices` ON  `ports`.`device_id` =  `devices`.`device_id` WHERE `ifAlias` LIKE '%" . $search . "%' OR `ifDescr` LIKE '%" . $search . "%' ORDER BY ifDescr LIMIT 8");

      if (count($results))
      {
        $found = 1;

        foreach ($results as $result)
        {
          $name = $result['ifDescr'];
          if (strlen($name) > 36)
          {
            $name = substr($name, 0, 36) . "...";
          }
          $description = $result['ifAlias'];
          if (strlen($description) > 50)
          {
            $description = substr($description, 0, 50) . "...";
          }
  
          if($result['deleted'] == 0 && ($result['ignore'] == 0 || $result['ignore'] == 0) && ($result['ifInErrors_delta'] > 0 || $result['ifOutErrors_delta'] > 0))
          {
            // Errored ports
            $port_colour = '#ffa500';
          }
          elseif($result['deleted'] == 0 && ($result['ignore'] == 1 || $result['ignore']  == 1))
          {
            // Ignored ports
            $port_colour = '#000000';
          
          }
          elseif($result['deleted'] == 0 && $result['ifAdminStatus'] == 'down' && $result['ignore'] == 0 && $result['ignore'] == 0)
          {
            // Shutdown ports
            $port_colour = '#808080';
          }
          elseif($result['deleted'] == 0 && $result['ifOperStatus'] == 'down' && $result['ifAdminStatus'] == 'up' && $result['ignore'] == 0 && $result['ignore'] == 0)
          {
            // Down ports
            $port_colour = '#ff0000';
          }
          elseif($result['deleted'] == 0 && $result['ifOperStatus'] == 'up' && $result['ignore'] == 0 && $result['ignore'] == 0)
          {
            // Up ports
            $port_colour = '#008000';
          }

          $ports[]=array('count'=>count($results),
          'url'=>generate_port_url($result),
          'name'=>$name,
          'description'=>$description,
          'colours'=>$highlight_colour,
          'hostname'=>$result['hostname']);

        }
      }
      $json = json_encode($ports);
      print_r($json);
      exit;

    }
  }
}
?>
