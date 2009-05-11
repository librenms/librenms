<?

### Connect to database

$nagios_host = mysql_fetch_array( mysql_query("SELECT * FROM nagios_hosts WHERE address = '".$device['hostname']."'", $nagios_link) ); 
$nagios_hoststatus = mysql_fetch_array( mysql_query("SELECT * FROM nagios_hoststatus WHERE '".$nagios_host['host_object_id']."'", $nagios_link) );

$i = 0;

$service_text = array ('0' => 'Up', '1' => 'Down', '2' => 'Critical', '3' => 'Unknown');
$host_text = array ('0' => 'Up', '1' => 'Down', '2' => 'Unreachable');

$host_colour = array ('0' => '#99ff99', '1' => '#ff9999', '2' => '#ff6666');
$service_colour = array ('0' => '#99ff99', '1' => '#ff9999', '2' => '#ff6666', '3' => '#ffaa99');


echo("<div style='font-size: 20px; padding:5px; margin:5px; background: ".$host_colour[$nagios_hoststatus[current_state]]."'>Status : " . $host_text[$nagios_hoststatus[current_state]] . " ".$nagios_hoststatus['output']."</div>");


echo("<table cellspacing=0 cellpadding=3>");
$nagios_services = mysql_query("SELECT * FROM nagios_services AS N, nagios_servicestatus AS S WHERE N.host_object_id = '".$nagios_host['host_object_id']."' AND S.service_object_id = N.service_object_id", $nagios_link);
while ($nagios_service = mysql_fetch_array($nagios_services)) {
  if(!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
  echo("<tr bgcolor=$bg_colour>");
  $service_state = $nagios_service['current_state'];
  echo("<td>" . $nagios_service['display_name'] . "</td><td bgcolor=".$service_colour[$service_state].">".$service_text[$service_state]."</td><td>" . $nagios_service['output'] . "</td>");
  echo("</tr>");
  $i++;
}

echo("</table>");


?>
