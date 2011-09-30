<?php

#if(!$vars['view']) { $vars['view'] = "incoming"; }
if(!$vars['view']) { $vars['view'] = "outgoing"; }

    $files      = array();

    if ($handle = opendir($config['smokeping']['dir'])) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") {
                if (eregi(".rrd", $file)) {
                   if (eregi("~", $file)) {
                      list($target,$slave) = explode("~", str_replace(".rrd", "", $file));
                      $files[$target][$slave] = $file;
                      $files_rev[$slave][$target] = $file;
                   } else {
                      $target = str_replace(".rrd", "", $file);
                      $files[$target]['observium'] = $file;
                   }
                }
            }
        }
    }


if($vars['view'] == "incoming")
{

    if(count($files_rev[$device['hostname']]))
    {

       $graph_array['type']                    = "device_smokeping_in_all";
       $graph_array['id']                      = $device['device_id'];

       include("includes/print-quadgraphs.inc.php");

    }

} else {

    if(count($files[$device['hostname']]))
    {

       $graph_array['type']                    = "device_smokeping_out_all";
       $graph_array['id']                      = $device['device_id'];

       include("includes/print-quadgraphs.inc.php");

    }

}


echo("<pre>");
print_r($files);
echo("</pre>");


?>
