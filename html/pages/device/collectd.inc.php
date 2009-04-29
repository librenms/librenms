<?php // vim:fenc=utf-8:filetype=php:ts=4
/*
 * Copyright (C) 2099  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02150-1301, USA.
 */

error_reporting(E_ALL | E_NOTICE | E_WARNING);

#require('config.php');
#require('functions.php');
#require('definitions.php');

load_graph_definitions();

/**
 * Send back new list content
 * @items Array of options values to return to browser
 * @method Name of Javascript method that will be called to process data
 */
function dhtml_response_list(&$items, $method) {
	header("Content-Type: text/xml");

	print('<?xml version="1.0" encoding="utf-8" ?>'."\n");
	print("<response>\n");
	printf(" <method>%s</method>\n", htmlspecialchars($method));
	print(" <result>\n");
	foreach ($items as &$item)
		printf('  <option>%s</option>'."\n", htmlspecialchars($item));
	print(" </result>\n");
	print("</response>");
}


    echo("<div style='width: auto; text-align: right; padding: 10px; display:block; background-color: #eeeeee;'>");
    $plugins = collectd_list_plugins($device['hostname']);
    foreach ($plugins as &$plugin) {
       if(!$_GET['opta']) { $_GET['opta'] = $plugin; }
       echo($sep);
       if($_GET['opta'] == $plugin) { echo("<strong>"); }
       echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/collectd/" . $plugin . "/'>" . htmlspecialchars($plugin) ."</a>\n");
       if($_GET['opta'] == $plugin) { echo("</strong>"); }
       $sep = ' | ';
    }
    unset ($sep);
    echo("</div>");    

    $pinsts = collectd_list_pinsts($device['hostname'], $_GET['opta']);
    foreach ($pinsts as &$instance) {

     $types = collectd_list_types($device['hostname'], $_GET['opta'], $instance);
     foreach ($types as &$type) {

     $typeinstances = collectd_list_tinsts($device['hostname'], $_GET['opta'], $instance, $type);
 
     if($MetaGraphDefs[$type]) { $typeinstances = array($MetaGraphDefs[$type]); }

     foreach ($typeinstances as &$tinst) {
       echo("<div><h3>".$_GET['opta']." $instance - $type - $tinst</h3>");

       $daily_traffic   = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=day&to=$now&width=215&height=100";
       $daily_traffic  .= $args;
       $daily_url       = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=day&to=$now&width=400&height=150";
       $daily_url      .= $args;

       $weekly_traffic   = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=week&to=$now&width=215&height=100";
       $weekly_traffic  .= $args;
       $weekly_url       = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=week&to=$now&width=400&height=150";
       $weekly_url      .= $args; 

       $monthly_traffic   = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=month&to=$now&width=215&height=100";
       $monthly_traffic  .= $args;
       $monthly_url       = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=month&to=$now&width=400&height=150";
       $monthly_url      .= $args;

       $yearly_traffic   = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=year&to=$now&width=215&height=100";
       $yearly_traffic  .= $args;
       $yearly_url       = $config['base_url'] . "/collectd-graph.php?host=" . $device['hostname'] . "&plugin=".$_GET['opta']."&type=".$_GET['opta']."&plugin_instance=".$instance."&type=".$type."&type_instance=".$tinst."&timespan=year&to=$now&width=400&height=150";
       $yearly_url      .= $args;
 

       echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
         <img src='$daily_traffic' border=0></a> ");
       echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
         <img src='$weekly_traffic' border=0></a> ");
       echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
         <img src='$monthly_traffic' border=0></a> ");
       echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\">
         <img src='$yearly_traffic' border=0></a>");
 
       echo("</div>");
 
      }
     }

    }




?>
