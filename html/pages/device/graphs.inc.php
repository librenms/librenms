<?php

$bg="#ffffff";

echo('<div style="clear: both;">');

$datas = array('System','Network');

if(!$_GET['opta']) { $_GET['opta'] = strtolower($datas[0]); }

print_optionbar_start('', '');

$sep = "";
foreach ($datas as $texttype)
{
  $type = strtolower($texttype);
  echo($sep);
  if ($_GET['opta'] == $type)
  {
    echo("<strong>");
    echo('<img src="images/icons/'.$type.'.png" class="optionicon" />');
  }
  else
  {
    echo('<img src="images/icons/greyscale/'.$type.'.png" class="optionicon" />');
  }
  echo("<a href='".$config['base_url']."/device/".$device['device_id']."/graphs/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $texttype ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = " | ";
}
unset ($sep);
print_optionbar_end();

#echo('<div style="float: right;">');

  include_dir("/html/pages/device/graphs/".mres($_GET['opta']));

  #if ($config['os'][$device['os']]['group']) { $os_group = $config['os'][$device['os']]['group']; }
  #if (is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php")) {
  #  /// OS Specific
  #  include($config['install_dir'] . "/html/pages/device/graphs/os-".$device['os'].".inc.php");
  #} elseif ($os_group && is_file($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php")) {
  #  /// OS Group Specific
  #  include($config['install_dir'] . "/html/pages/device/graphs/os-".$os_group.".inc.php");
  #} else {
  #  echo("No graph definitions found for OS " . $device['os'] . "!");
  #}

#  echo("</div>");

?>

