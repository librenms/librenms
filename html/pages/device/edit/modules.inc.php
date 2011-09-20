<?php

if($_POST['toggle_poller'] && isset($config['poller_modules'][$_POST['toggle_poller']]))
{
  $module = mres($_POST['toggle_poller']);
  if (isset($attribs['poll_'.$module]) && $attribs['poll_'.$module] != $config['poller_modules'][$_POST['toggle_poller']])
  {
    del_dev_attrib($device, 'poll_' . $module);
  } elseif ($config['poller_modules'][$_POST['toggle_poller']] == 0) {
    set_dev_attrib($device, 'poll_' . $module, "1");
  } else {
    set_dev_attrib($device, 'poll_' . $module, "0");
  }
  $attribs = get_dev_attribs($device['device_id']);
}

if($_POST['toggle_discovery'] && isset($config['discovery_modules'][$_POST['toggle_discovery']]))
{
  $module = mres($_POST['toggle_discovery']);
  if (isset($attribs['discover_'.$module]) && $attribs['discover_'.$module] != $config['discovery_modules'][$_POST['toggle_discovery']])
  {
    del_dev_attrib($device, 'discover_' . $module);
  } elseif ($config['discovery_modules'][$_POST['toggle_discovery']] == 0) {
    set_dev_attrib($device, 'discover_' . $module, "1");
  } else {
    set_dev_attrib($device, 'discover_' . $module, "0");
  }
  $attribs = get_dev_attribs($device['device_id']);
}

echo('<div style="margin: 0px 10px; width: 500px; float: left;">');
$i=0;

echo('<div style="padding:4px 0px 4px 8px;" class=graphhead>Poller Modules</div>');

echo('<table width="100%" cellpadding=5>');
echo('<tr><th>Module</th><th>Global</th><th>Device</th></tr>');
foreach($config['poller_modules'] as $module => $module_status)
{
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }

  echo('<tr bgcolor="'.$bg_colour.'"><td><b>'.$module.'</b></td><td>');

  echo(($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ));

  echo('</td><td>');

  if (isset($attribs['poll_'.$module]))
  {
    if ($attribs['poll_'.$module]) {echo("<span class=green>enabled</span>");} else { echo('<span class=red>disabled</span>'); }
  } else {
    echo(($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ));
  }


  echo('</td><td>');

    echo('<form id="toggle_poller" name="toggle_poller" method="post" action="">
  <input type=hidden name="toggle_poller" value="'.$module.'">
  <input type="submit" name="Submit" value="Toggle" />
  </label>
</form>');


  echo('</td></tr>');
  $i++;
}
echo('</table>');
echo('</div>');

echo('<div style="margin: 0px 10px; width: 500px; float: right;">');
$i=0;
echo('<div style="padding:4px 0px 4px 8px;" class=graphhead>Discovery Modules</div>');
echo('<table width="100%" cellpadding=5>');
echo('<tr><th>Module</th><th>Global</th><th>Device</th></tr>');
foreach($config['discovery_modules'] as $module => $module_status)
{
  if (!is_integer($i/2)) { $bg_colour = $list_colour_a; } else { $bg_colour = $list_colour_b; }
  echo('<tr bgcolor="'.$bg_colour.'"><td><b>'.$module.'</b></td><td>');

  echo(($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ));

  echo('</td><td>');

  if (isset($attribs['discover_'.$module]))
  {
    if ($attribs['discover_'.$module]) {echo("<span class=green>enabled</span>");} else { echo('<span class=red>disabled</span>'); }
  } else {
    echo(($module_status ? '<span class=green>enabled</span>' : '<span class=red>disabled</span>' ));
  }


  echo('</td><td>');

    echo('<form id="toggle_discovery" name="toggle_discovery" method="post" action="">
  <input type=hidden name="toggle_discovery" value="'.$module.'">
  <input type="submit" name="Submit" value="Toggle" />
  </label>
</form>');


  echo('</td></tr>');


  $i++;
}
echo('</table>');
echo('</div>');


?>
