<?php

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab' => 'edit');

if ($_SESSION['userlevel'] < '7')
{
  print_error("Insufficient Privileges");
} else {

  $panes['device']   = 'Device Settings';
  $panes['snmp']     = 'SNMP';
  $panes['ports']    = 'Port Settings';
  $panes['apps']     = 'Applications';
  $panes['alerts']   = 'Alerts';
  $panes['modules']  = 'Modules';

  if ($config['enable_services'])
  {
    $panes['services'] = 'Services';
  }

  $panes['ipmi']     = 'IPMI';

  print_optionbar_start();

  unset($sep);
  foreach ($panes as $type => $text)
  {
    if (!isset($vars['section'])) { $vars['section'] = $type; }
    echo($sep);
    if ($vars['section'] == $type)
    {
      echo("<span class='pagemenu-selected'>");
      #echo('<img src="images/icons/'.$type.'.png" class="optionicon" />');
    } else {
      #echo('<img src="images/icons/greyscale/'.$type.'.png" class="optionicon" />');
    }

    echo(generate_link($text,$link_array,array('section'=>$type)));

#    echo("<a href='device/".$device['device_id']."/edit/" . $type . ($_GET['optd'] ? "/" . $_GET['optd'] : ''). "/'> " . $text ."</a>");
    if ($vars['section'] == $type) { echo("</span>"); }
    $sep = " | ";
  }

  print_optionbar_end();

  if (is_file("pages/device/edit/".mres($vars['section']).".inc.php"))
  {
    include("pages/device/edit/".mres($vars['section']).".inc.php");
  }
}

?>
