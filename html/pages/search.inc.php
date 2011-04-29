<?php

$sections = array('ipv4' => 'IPv4 Address', 'ipv6' => 'IPv6 Address', 'mac' => 'MAC Address');

if (!$_GET['opta']) { $_GET['opta'] = "ipv4"; }

print_optionbar_start('', '');

 echo('<span style="font-weight: bold;">Search</span> &#187; ');

unset($sep);
foreach ($sections as $type => $texttype)
{
  echo($sep);
  if ($_GET['opta'] == $type)
  {
    echo("<span class='pagemenu-selected'>");
  }

  echo('<a href="search/' . $type . ($_GET['optb'] ? '/' . $_GET['optb'] : ''). '/">' . $texttype .'</a>');

  if ($_GET['opta'] == $type) { echo("</span>"); }

  $sep = ' | ';
}
unset ($sep);

print_optionbar_end('', '');

switch ($_GET['opta'])
{
  case 'ipv4':
  case 'ipv6':
  case 'mac':
    include('pages/search/'.$_GET['opta'].'.inc.php');
    break;
  default:
    echo("<h2>Error. Please report this to observium developers.</h2>");
    break;
}

?>
