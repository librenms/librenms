<?php print_optionbar_start();

if(!$_GET['opta']) { $_GET['opta'] = "basic"; }

echo("<a href='".$config['base_url']."/services/basic/'>Basic</a> | ");
echo("<a href='".$config['base_url']."/services/details/'>Details</a>");

print_optionbar_end();

if($_GET['status'] == '0') { $where = " AND service_status = '0'"; } else { unset ($where); }

echo("<div style='margin: 5px;'><table cellpadding=7 border=0 cellspacing=0 width=100%>");
//echo("<tr class=interface-desc bgcolor='#e5e5e5'><td>Device</td><td>Service</td><td>Status</td><td>Changed</td><td>Checked</td><td>Message</td></tr>");

if ($_SESSION['userlevel'] >= '5') {
  $host_sql = "SELECT * FROM devices AS D, services AS S WHERE D.device_id = S.device_id GROUP BY D.hostname ORDER BY D.hostname";
} else {
  $host_sql = "SELECT * FROM devices AS D, services AS S, devices_perms AS P WHERE D.device_id = S.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' $where GROUP BY D.hostname ORDER BY D.hostname";
}
  $host_query = mysql_query($host_sql);
  while($host_data = mysql_fetch_array($host_query)) {
    $device_id = $host_data['device_id'];
    $device_hostname = $host_data['hostname'];
    $service_query = mysql_query("SELECT * FROM `services` WHERE `device_id` = '" . $host_data['device_id'] . "' $where");
    while($service = mysql_fetch_array($service_query)) 
    {
       include("includes/print-service.inc");
#       $samehost = 1;
       if($_GET['opta'] == "details") 
       {

         $graph_array['height'] = "100";
         $graph_array['width']  = "215";
         $graph_array['to']     = $now;
         $graph_array['id']     = $service['service_id'];
         $graph_array['type']   = "service_availability";

         $periods = array('day', 'week', 'month', 'year');

         echo('<tr style="background-color: '.$bg.'; padding: 5px;"><td colspan=6>');

         foreach($periods as $period) {
           $graph_array['from']     = $$period;
           $graph_array_zoom   = $graph_array; $graph_array_zoom['height'] = "150"; $graph_array_zoom['width'] = "400";
           echo(overlib_link($_SERVER['REQUEST_URI'], generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
         }
         echo("</td></tr>");
       }
    }
    unset ($samehost);
  }	

  echo("</table></div>");

?>
