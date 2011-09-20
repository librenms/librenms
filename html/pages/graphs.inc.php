<?php

unset($vars['page']);

### Setup here

if($_SESSION['widescreen'])
{
  $graph_width=1700;
  $thumb_width=180;
} else {
  $graph_width=1075;
  $thumb_width=113;
}

if (!is_numeric($vars['from'])) { $vars['from'] = $config['time']['day']; }
if (!is_numeric($vars['to']))   { $vars['to']   = $config['time']['now']; }

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', mres($vars['type']), $graphtype);

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];
$id = $vars['id'];

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
  $graph_array['width']  = $thumb_width;
  $graph_array['legend'] = "no";
  $graph_array['to']     = $now;
  $graph_array['id']     = $vars['id'];
  $graph_array['type']   = $vars['type'];

  print_optionbar_start();
  echo($title);
  print_optionbar_end();

  echo("<div style='margin: auto;'>");

  $thumb_array = array('sixhour' => '6 Hours', 'day' => '24 Hours', 'twoday' => '48 Hours', 'week' => 'One Week', 'twoweek' => 'Two Weeks',
                       'month' => 'One Month', 'twomonth' => 'Two Months','year' => 'One Year', 'twoyear' => 'Two Years');


  foreach ($thumb_array as $period => $text)
  {
    $graph_array['from']   = $config['time'][$period];

    $link_array = $vars;
    $link_array['from'] = $graph_array['from'];
    $link_array['to'] = $graph_array['to'];
    $link_array['page'] = "graphs";
    $link = generate_url($link_array);

    echo("<div style='margin: 0px 10px 5px 0px; padding:5px; background: #e5e5e5; float: left;' class='rounded-3px'>
      <span class=device-head>".$text."</span><br />
       <a href='".$link."'>");
    echo(generate_graph_tag($graph_array));
    echo("   </a>
    </div>");
  }
  echo("</div>");

  $graph_array = $vars;

  $graph_array['height'] = "300";
  $graph_array['width']  = $graph_width;

  echo generate_graph_js_state($graph_array);

  echo("<div style='width: ".$graph_array['width']."; margin: auto;'>");
  echo(generate_graph_tag($graph_array));
  echo("</div>");
}

?>
