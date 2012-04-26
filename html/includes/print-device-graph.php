<?php

if(empty($graph_array['type'])) { $graph_array['type'] = $graph_type; }
if(empty($graph_array['id']))   { $graph_array['id'] = $device['device_id']; }

# FIXME not css alternating yet
if (!is_integer($g_i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

echo('<div style="background-color: '.$row_colour.';">');
echo('<div style="padding:4px 0px 0px 8px;" class=graphhead>'.$graph_title.'</div>');

include("includes/print-graphrow.inc.php");

echo('</div>');

$g_i++;

?>

