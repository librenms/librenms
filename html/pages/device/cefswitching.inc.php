<?php

print_optionbar_start();

$menu_options = array('basic' => 'Basic',
		      'graphs' => 'Graphs',
                      );

if (!$_GET['opta']) { $_GET['opta'] = "basic"; }

$sep = "";
foreach ($menu_options as $option => $text)
{
  echo($sep);
  if ($_GET['opta'] == $option) { echo("<span class='pagemenu-selected'>"); }
  echo('<a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/cefswitching/' . $option . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $text
 . '</a>');
  if ($_GET['opta'] == $option) { echo("</span>"); }
  $sep = " | ";
}

unset($sep);

print_optionbar_end();


echo("<table width=100%>");

$cef_query = mysql_query("SELECT * FROM `cef_switching` WHERE `device_id` = '".$device['device_id']."' ORDER BY `entPhysicalIndex`, `afi`, `cef_index`");

echo("<tr><th>Entity</th><th>AFI</th><th>Path</th><th>Drop</th><th>Punt</th><th>Punt2Host</th>
</tr>");

$i=0;

while ($cef = mysql_fetch_assoc($cef_query))
{

  $entity_query = mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$device['device_id']."' AND `entPhysicalIndex` = '".$cef['entPhysicalIndex']."'");
  $entity = mysql_fetch_assoc($entity_query);

  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

  $interval = $cef['updated'] - $cef['updated_prev'];

  echo("<tr bgcolor=$bg_colour><td>".$entity['entPhysicalName']." - ".$entity['entPhysicalModelName']."</td>
            <td>".$cef['afi']."</td>
            <td>".$cef['cef_path']."</td>");

  echo("<td>".format_si($cef['drop']));
  if($cef['drop'] > $cef['drop_prev']) { echo(" <span style='color:red;'>(".round(($cef['drop']-$cef['drop_prev'])/$interval,2)."/sec)</span>"); }
  echo("</td>");
  echo("<td>".format_si($cef['punt']));
  if($cef['punt'] > $cef['punt_prev']) { echo(" <span style='color:red;'>(".round(($cef['punt']-$cef['punt_prev'])/$interval,2)."/sec)</span>"); }
  echo("</td>");
  echo("<td>".format_si($cef['punt']));
  if($cef['punt2host'] > $cef['punt2host_prev']) { echo(" <span style='color:red;'>(".round(($cef['punt2host']-$cef['punt2host_prev'])/$interval,2)."/sec)</span>"); }
  echo("</td>");

        echo("</tr>
       ");
  $i++;
}

echo("</table>");

?>
