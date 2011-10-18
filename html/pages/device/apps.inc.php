<?php

print_optionbar_start();

echo("<span style='font-weight: bold;'>Apps</span> &#187; ");

unset($sep);

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'apps');

foreach (dbFetchRows("SELECT * FROM `applications` WHERE `device_id` = ?", array($device['device_id'])) as $app)
{
  echo($sep);

  if (!$vars['app']) { $vars['app'] = $app['app_type']; }

  if ($vars['app'] == $app['app_type'])
  {
    echo("<span class='pagemenu-selected'>");
    #echo('<img src="images/icons/'.$app['app_type'].'.png" class="optionicon" />');
  } else {
    #echo('<img src="images/icons/greyscale/'.$app['app_type'].'.png" class="optionicon" />');
  }
  echo(generate_link(ucfirst($app['app_type']),$link_array,array('app'=>$app['app_type'])));
  if ($vars['app'] == $app['app_type']) { echo("</span>"); }
  $sep = " | ";
}

print_optionbar_end();

$app = dbFetchRow("SELECT * FROM `applications` WHERE `device_id` = ? AND `app_type` = ?", array($device['device_id'], $vars['app']));

if (is_file("pages/device/apps/".mres($vars['app']).".inc.php"))
{
   include("pages/device/apps/".mres($vars['app']).".inc.php");
}

$pagetitle[] = "Apps";

?>
