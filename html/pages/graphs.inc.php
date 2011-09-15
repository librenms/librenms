<?php

$id  = $_GET['opta'];
$graph_type = $_GET['optb'];
if (is_numeric($_GET['optc'])) { $from = $_GET['optc']; } else { $from = $config['time']['day']; }
if (is_numeric($_GET['optd'])) { $to = $_GET['optd']; } else { $to = $config['time']['now']; }

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', mres($graph_type), $graphtype);

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];

if (is_file("includes/graphs/".$type."/auth.inc.php"))
{
  include("includes/graphs/".$type."/auth.inc.php");
}

if (!$auth)
{
  include("includes/error-no-perm.inc.php");
} else {

# Do we really need to show the type? User does not have to see the type of graph (i.e. sensor_temperature)

# Yes, i think we doo, else we have graph titles of "router1". It's nice to show the type here. maybe only the pretty
# array_type?

  if (isset($config['graph_types'][$type][$subtype]['descr'])) { $title .= " :: ".$config['graph_types'][$type][$subtype]['descr']; } else { $title .= " :: ".$graph_type; }

  $graph_array['height'] = "60";
  $graph_array['width']  = "125";
  $graph_array['legend'] = "no";
  $graph_array['to']     = $now;
  $graph_array['id']     = $id;
  $graph_array['type']   = $graph_type;

  $graph_array_zoom      = $graph_array; 
  $graph_array_zoom['height'] = "150"; 
  $graph_array_zoom['width'] = "400";

  print_optionbar_start();
  echo($title);
  print_optionbar_end();

  echo("<div style='margin: 0px 0px 0px 0px'>");

  echo("<div style='margin: auto;'>");

  $graph_array['from']   = $config['time']['sixhour'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>6 Hour</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/".$graph_array['to']."/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['day'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>24 Hour</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['twoday'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>48 Hour</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['week'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>Week</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['twoweek'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>Two Week</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['month'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>Month</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['twomonth'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>Two Month</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  $graph_array['from']   = $config['time']['year'];
  echo("<div style='width: ${width}px; margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
    <span class=device-head>Year</span><br />
     <a href='".$config['base_url']."/graphs/$id/$graph_type/".$graph_array['from']."/$to/'>");
  echo(generate_graph_tag($graph_array));
  echo("   </a>
  </div>");

  echo("</div>");

  $graph_array['height'] = "300";
  $graph_array['width']  = "1075";
  $graph_array['from']   = $from;
  $graph_array['to']     = $to;
  $graph_array['legend'] = "yes";

  echo generate_graph_js_state($graph_array);

  echo("<div style='width:1150px; margin: auto;'>");
  echo(generate_graph_tag($graph_array));
  echo("</div>");
  echo("</div>");
}

?>
