<?php // vim:fenc=utf-8:filetype=php:ts=4

 echo("<div style='background-color: ".$list_colour_b."; margin: auto; margin-bottom: 5px; text-align: left; padding: 7px; padding-left: 11px; clear: both; display:block; height:20px;'>");
 unset ($sep);
 $query = mysql_query("SELECT `entSensorType` FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND entSensorType != '' AND entSensorType NOT LIKE 'No%' GROUP BY `entSensorType` ORDER BY `entSensorType`");
 while($data = mysql_fetch_array($query)) {
   $type = $data['entSensorType'];
   if(!$_GET['opta']) { $_GET['opta'] = $type; }
   echo($sep);
   if($_GET['opta'] == $type) { echo("<strong>"); }
   echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ciscosensors/" . $type . "/'>" . htmlspecialchars($type) ."</a>\n");
   if($_GET['opta'] == $type) { echo("</strong>"); }
   $sep = ' | ';
 }
 unset ($sep);
 echo("</div>");    

 $query = mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$device['device_id']."' and entSensorType = '".$_GET['opta']."' ORDER BY `entPhysicalName`");
 while($data = mysql_fetch_array($query)) {

       if($data['entSensorMeasuredEntity']) {
         $measured = mysql_fetch_array(mysql_query("SELECT * FROM entPhysical WHERE device_id = '".$device['device_id']."' AND entPhysicalIndex = '".$data['entSensorMeasuredEntity']."'"));
       }

       echo("<div><h3>".$measured['entPhysicalName']." ".$data['entPhysicalName']."</h3>");
       $graph_type = "cisco_entity_sensor";
       $args = "&a=".$data['entPhysical_id'];

       include("includes/print-device-graph.php");

       echo("</div>");


 }

?>
