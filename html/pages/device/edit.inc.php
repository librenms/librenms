<?php

if($_SESSION['userlevel'] < '7') { 
  print_error("Insufficient Privileges");
} else {


$panes =  array('device'   => 'Device Settings',
		'ports'    => 'Port Settings',
                'apps'     => 'Applications',
		'services' => 'Services',
		'ipmi'     => 'IPMI');

print_optionbar_start();

unset($sep);
foreach($panes as $type => $text) {

  if(!isset($_GET['opta'])) { $_GET['opta'] = $type; }
  echo($sep);
  if ($_GET['opta'] == $type)
  {
    echo("<strong>");
    echo('<img src="images/icons/'.$type.'.png" class="optionicon" />');
  } else {
    echo('<img src="images/icons/greyscale/'.$type.'.png" class="optionicon" />');
  }
  echo("<a href='".$config['base_url']."/device/".$device['device_id']."/edit/" . $type . ($_GET['optb'] ? "/" . $_GET['optb'] : ''). "/'> " . $text ."</a>\n");
  if ($_GET['opta'] == $type) { echo("</strong>"); }
  $sep = " | ";
}

print_optionbar_end();

if (is_file("pages/device/edit/".mres($_GET['opta']).".inc.php"))
{
   include("pages/device/edit/".mres($_GET['opta']).".inc.php");
} else {
}

}

