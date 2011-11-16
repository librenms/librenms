<?php

if(!isset($vars['section'])) { $vars['section'] = "eventlog"; }

print_optionbar_start();

echo("<strong>Logging</strong>  &#187; ");

if ($vars['section'] == "eventlog")
{ echo('<span class="pagemenu-selected">'); }
echo(generate_link("Event Log" , $vars, array('section'=>'eventlog')));
if ($vars['section'] == "eventlog") { echo("</span>"); }

echo(" | ");

if ($vars['section'] == "syslog")
{ echo('<span class="pagemenu-selected">'); }
echo(generate_link("Syslog" , $vars, array('section'=>'syslog')));
if ($vars['section'] == "syslog") { echo("</span>"); }

switch ($vars['section'])
{
  case 'syslog':
  case 'eventlog':
    include('pages/device/logs/'.$vars['section'].'.inc.php');
    break;
  default:
    print_optionbar_end();
    echo("<h2>Error. No section ".$vars['section'].".<br /> Please report this to observium developers.</h2>");
    break;
}

?>
