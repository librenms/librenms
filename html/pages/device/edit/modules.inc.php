<?php

echo('<div style="margin: 0px 10px; width: 500px; float: left;">');
$i=0;

echo('<div style="padding:4px 0px 4px 8px;" class=graphhead>Poller Modules</div>');

echo('<table width="100%" cellpadding=5>');
foreach($config['poller_modules'] as $module => $module_status)
{
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
  echo('<tr bgcolor="'.$bg_colour.'"><td><b>'.$module.'</b></td><td>'.($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ).'</td></tr>');
  $i++;
}
echo('</table>');
echo('</div>');

echo('<div style="margin: 0px 10px; width: 500px; float: right;">');
$i=0;
echo('<div style="padding:4px 0px 4px 8px;" class=graphhead>Discovery Modules</div>');
echo('<table width="100%" cellpadding=5>');
foreach($config['discovery_modules'] as $module => $module_status)
{
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
  echo('<tr bgcolor="'.$bg_colour.'"><td><b>'.$module.'</b></td><td>'.($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ).'</td></tr>');
  $i++;
}
echo('</table>');
echo('</div>');


?>
