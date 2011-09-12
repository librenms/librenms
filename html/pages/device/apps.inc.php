<?php

print_optionbar_start();

echo("<span style='font-weight: bold;'>Apps</span> &#187; ");

unset($sep);

foreach (dbFetchRows("SELECT * FROM `applications` WHERE `device_id` = ?", array($device['device_id'])) as $app)
{
  echo($sep);

  if (!$_GET['optc']) { $_GET['optc'] = $app['app_type']; }

  if ($_GET['optc'] == $app['app_type'])
  {
    echo("<span class='pagemenu-selected'>");
    #echo('<img src="images/icons/'.$app['app_type'].'.png" class="optionicon" />');
  } else {
    #echo('<img src="images/icons/greyscale/'.$app['app_type'].'.png" class="optionicon" />');
  }
  echo("<a href='device/".$device['device_id']."/apps/" . $app['app_type'] . ($_GET['optd'] ? "/" . $_GET['optd'] : ''). "/'> " . $app['app_type'] ."</a>");
  if ($_GET['optc'] == $app['app_type']) { echo("</span>"); }
  $sep = " | ";
}

print_optionbar_end();

$app = dbFetchRow("SELECT * FROM `applications` WHERE `device_id` = ? AND `app_type` = ?", array($device['device_id'], $_GET['optc']));

if (is_file("pages/device/apps/".mres($_GET['optc']).".inc.php"))
{
   include("pages/device/apps/".mres($_GET['optc']).".inc.php");
}

?>
