<?php // vim:fenc=utf-8:filetype=php:ts=4
/*
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
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
 * 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

function load_graph_definitions_local($logarithmic = false, $tinylegend = false) {
	global $GraphDefs, $MetaGraphDefs;

	// Define 1-rrd Graph definitions here
	$GraphDefs['local_type'] = array(
		'-v', 'Commits',
		'DEF:avg={file}:value:AVERAGE',
		'DEF:min={file}:value:MIN',
		'DEF:max={file}:value:MAX',
		"AREA:max#B7B7F7",
		"AREA:min#FFFFFF",
		"LINE1:avg#0000FF:Commits",
		'GPRINT:min:MIN:%6.1lf Min,',
		'GPRINT:avg:AVERAGE:%6.1lf Avg,',
		'GPRINT:max:MAX:%6.1lf Max,',
		'GPRINT:avg:LAST:%6.1lf Last\l');

	// Define MetaGraph definition type -> function mappings here
	$MetaGraphDefs['local_meta'] = 'meta_graph_local';
}

function meta_graph_local($host, $plugin, $plugin_instance, $type, $type_instances, $opts = array()) {
	global $config;
	$sources = array();

	$title = "$host/$plugin".(!is_null($plugin_instance) ? "-$plugin_instance" : '')."/$type";
	if (!isset($opts['title']))
		$opts['title'] = $title;
	$opts['rrd_opts'] = array('-v', 'Events');

	$files = array();
/*	$opts['colors'] = array(
		'ham'     => '00e000',
		'spam'    => '0000ff',
		'malware' => '990000',

		'sent'     => '00e000',
		'deferred' => 'a0e000',
		'reject'   => 'ff0000',
		'bounced'  => 'a00050'
	);

	$type_instances = array('ham', 'spam', 'malware',  'sent', 'deferred', 'reject', 'bounced'); */
	foreach ($type_instances as $inst) {
		$file  = '';
		foreach ($config['datadirs'] as $datadir)
			if (is_file($datadir.'/'.$title.'-'.$inst.'.rrd')) {
				$file = $datadir.'/'.$title.'-'.$inst.'.rrd';
				break;
			}
		if ($file == '')
			continue;

		$sources[] = array('name'=>$inst, 'file'=>$file);
	}

//	return collectd_draw_meta_stack($opts, $sources);
	return collectd_draw_meta_line($opts, $sources);
}

?>
