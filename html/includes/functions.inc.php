<?php

function print_percentage_bar ($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background) 
{
  $output = '
<div style="font-size:11px;">
  <div style=" width:'.$width.'px; height:'.$height.'px; background-color:#'.$right_background.';">
    <div style="width:'.$percent.'%; height:'.$height.'px; background-color:#'.$left_background.'; border-right:0px white solid;"></div>
    <div style="vertical-align: center;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$left_colour .'; padding-left :4px;"><b>'.$left_text.'</b></div>
    <div style="vertical-align: center;height: '.$height.'px;margin-top:-'.($height).'px; color:#'.$right_colour.'; padding-right:4px;text-align:right;"><b>'.$right_text.'</b></div>
  </div>
</div>';
  return $output;
}

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

function print_optionbar_start ($height = 20, $width = 0) {
  echo("
    <div style='margin:auto; text-align: center; margin-top: 0px; margin-bottom: 10px; " . ($width ? 'max-width: ' . $width . 'px; ' : '') . "'>
    <b class='rounded'>
    <b class='rounded1'><b></b></b>
    <b class='rounded2'><b></b></b>
    <b class='rounded3'></b>
    <b class='rounded4'></b>
    <b class='rounded5'></b></b>
    <div class='roundedfg' style='padding: 0px 5px;'>
    <div style='margin: auto; text-align: left; padding: 2px 5px; padding-left: 11px; clear: both; display:block; height:" . $height . "px;'>");
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
