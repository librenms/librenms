<?php print_optionbar_start();

echo("<span style='font-weight: bold;'>Services</span> &#187; ");

$menu_options = array('basic' => 'Basic',
                      'details' => 'Details');

if (!$_GET['optc']) { $_GET['optc'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($_GET['optc'] == $option)
  {
    echo("<span class='pagemenu-selected'>");
  }
  echo('<a href="device/' . $device['device_id'] . '/services/' . $option . ($_GET['optd'] ? '/' . $_GET['optd'] : ''). '/">' . $text . '</a>');
  if ($_GET['optc'] == $option)
  {
    echo("</span>");
  }
  $sep = " | ";
}

unset($sep);

print_optionbar_end();

if (dbFetchCell("SELECT COUNT(service_id) FROM `services` WHERE device_id = ?", array($device['device_id'])) > '0')
{
  echo("<div style='margin: 5px;'><table cellpadding=7 border=0 cellspacing=0 width=100%>");
  $i = "1";
  foreach (dbFetchRows("SELECT * FROM `services` WHERE `device_id` = ? ORDER BY `service_type`", array($device['device_id'])) as $service)
  {
    include("includes/print-service.inc.php");

       if ($_GET['optc'] == "details")
       {
         $graph_array['height'] = "100";
         $graph_array['width']  = "210";
         $graph_array['to']     = $now;
         $graph_array['id']     = $service['service_id'];
         $graph_array['type']   = "service_availability";

         $periods = array('day', 'week', 'month', 'year');

         echo('<tr style="background-color: '.$bg.'; padding: 7px;"><td colspan=4>');
         foreach ($periods as $period)
         {
           $graph_array['from'] = $$period;
           $graph_array_zoom   = $graph_array; $graph_array_zoom['height'] = "150"; $graph_array_zoom['width'] = "400";
           echo(overlib_link("#", generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
         }
         echo("</td></tr>");
       }
  }
  echo("</table></div>");
}
else
{
   echo("No Services");
}

$pagetitle[] = "Services";

?>
