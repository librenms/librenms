<?php

function generate_if_link($args)
{
  global $twoday; global $now; global $config; global $day; global $month;
  $args = ifNameDescr($args);
  if(!$args['content']) { $args['content'] = fixIfName($args['label']); }
  if(!$args['graph_type']) { $args['graph_type'] = 'bits'; }
  $class = ifclass($args['ifOperStatus'], $args['ifAdminStatus']);
  $graph_url = $config['base_url'] . "/graph.php?if=" . $args['interface_id'] . "&from=$day&to=$now&width=400&height=100&type=" . $args['graph_type'];
  $graph_url_month = $config['base_url'] . "/graph.php?if=" . $args['interface_id'] . "&from=$month&to=$now&width=400&height=100&type=" . $args['graph_type'];
  $device_id = getifhost($args['interface_id']);
  $link = "<a class=$class href='".$config['base_url']."/device/$device_id/interface/" . $args['interface_id'] . "/' ";
  $link .= "onmouseover=\" return overlib('";
  $link .= "<img src=\'$graph_url\'><br /><img src=\'$graph_url_month\'>', CAPTION, '<span class=list-large>" . $args['hostname'] . " - " . fixifName($args['label']) . "</span>";
  if($args['ifAlias']) { $link .= "<br />" . $args['ifAlias']; }
  $link .= "' ";
  $link .= $config['overlib_defaults'].");\" onmouseout=\"return nd();\" >".$args['content']."</a>";
  return $link;
}

function generate_port_thumbnail($args) {
    if(!$args['bg']) { $args['bg'] = "FFFFF"; }
    $args['content'] = "<img src='graph.php?type=".$args['graph_type']."&if=".$args['interface_id']."&from=".$args['from']."&to=".$args['to']."&width=".$args['width']."&height=".$args['height']."&legend=no&bg=".$args['bg']."'>";
    $output = generate_if_link($args);
    echo $output;
}

function print_optionbar_start () {
  echo("
    <div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px;'>
    <b class='rounded'>
    <b class='rounded1'><b></b></b>
    <b class='rounded2'><b></b></b>
    <b class='rounded3'></b>
    <b class='rounded4'></b>
    <b class='rounded5'></b></b>
    <div class='roundedfg' style='padding: 0px 5px;'>
    <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:20px;'>");
}


function print_optionbar_end () {
  echo("  </div>
    </div>
    <b class='rounded'>
    <b class='rounded5'></b>
    <b class='rounded4'></b>
    <b class='rounded3'></b>
    <b class='rounded2'><b></b></b>
    <b class='rounded1'><b></b></b></b>
  </div>");
}

?>
