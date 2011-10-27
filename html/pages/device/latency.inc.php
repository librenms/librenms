<?php

print_optionbar_start();

echo("<span style='font-weight: bold;'>Latency</span> &#187; ");

if(count($smokeping_files['in'][$device['hostname']]))
      $menu_options['incoming'] = 'Incoming';

if(count($smokeping_files['out'][$device['hostname']]))
      $menu_options['outgoing'] = 'Outgoing';

$sep = "";
foreach ($menu_options as $option => $text)
{
  if(!$vars['view']) { $vars['view'] = $option; }
  echo($sep);
  if ($vars['view'] == $option)
  {
    echo("<span class='pagemenu-selected'>");
  }
  echo(generate_link($text,$vars,array('view'=>$option)));
  if ($vars['view'] == $option)
  {
    echo("</span>");
  }
  $sep = " | ";
}

unset($sep);

print_optionbar_end();


if($vars['view'] == "incoming")
{

    if (count($smokeping_files['in'][$device['hostname']]))
    {

       $graph_array['type']                    = "device_smokeping_in_all";
       $graph_array['id']                      = $device['device_id'];

       include("includes/print-quadgraphs.inc.php");

    }

} elseif ($vars['view'] == "outgoing") {

    if (count($smokeping_files['out'][$device['hostname']]))
    {

       $graph_array['type']                    = "device_smokeping_out_all";
       $graph_array['id']                      = $device['device_id'];

       include("includes/print-quadgraphs.inc.php");


       foreach($smokeping_files['out'][$device['hostname']] AS $host) 
       {
         print_r($);
       }

    }

}

$pagetitle[] = "Latency";

?>
