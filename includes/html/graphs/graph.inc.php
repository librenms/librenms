<?php

use LibreNMS\Config;

// Push $_GET into $vars to be compatible with web interface naming
foreach ($_GET as $name => $value) {
    $vars[$name] = $value;
}

[$type, $subtype] = extract_graph_type($vars['type']);

if (is_numeric($vars['device'])) {
    $device = device_by_id_cache($vars['device']);
} elseif (! empty($vars['device'])) {
    $device = device_by_name($vars['device']);
}

// FIXME -- remove these
$width = $vars['width'];
$height = $vars['height'];
$title = $vars['title'];
$vertical = $vars['vertical'];
$legend = $vars['legend'];
$output = (! empty($vars['output']) ? $vars['output'] : 'default');
$from = parse_at_time($_GET['from']) ?: Config::get('time.day');
$to = parse_at_time($_GET['to']) ?: Config::get('time.now');
$graph_type = (isset($vars['graph_type']) ? $vars['graph_type'] : Config::get('webui.graph_type'));

$period = ($to - $from);
$base64_output = '';
$prev_from = ($from - $period);

$graphfile = Config::get('temp_dir') . '/' . strgen();

require Config::get('install_dir') . "/includes/html/graphs/$type/auth.inc.php";

//set default graph title
$graph_title = format_hostname($device);

if ($auth && is_customoid_graph($type, $subtype)) {
    $unit = $vars['unit'];
    include Config::get('install_dir') . '/includes/html/graphs/customoid/customoid.inc.php';
} elseif ($auth && is_file(Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php")) {
    include Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php";
} else {
    graph_error("$type*$subtype ");
    // Graph Template Missing");
}

if ($error_msg) {
    // We have an error :(
    graph_error($graph_error);
} elseif ($auth === null) {
    // We are unauthenticated :(
    graph_error($width < 200 ? 'No Auth' : 'No Authorization');
} else {
    // $rrd_options .= " HRULE:0#999999";
    if ($graph_type === 'svg') {
        $rrd_options .= ' --imgformat=SVG';
        if ($width < 350) {
            $rrd_options .= ' -m 0.75 -R light';
        }
    }

    if ($command_only) {
        echo "<div class='infobox'>";
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>";
        echo "<pre class='rrd-pre'>";
        echo 'rrdtool ' . Rrd::buildCommand('graph', $graphfile, $rrd_options);
        echo '</pre>';
        $return = Rrd::graph($graphfile, $rrd_options);
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>";
        echo "<pre class='rrd-pre'>";
        echo "$return";
        echo '</pre>';
        unlink($graphfile);
        echo '</div>';
    } elseif ($no_file) {
        graph_error($width < 200 ? 'No Data' : 'No Data file');
    } elseif ($rrd_options) {
        Rrd::graph($graphfile, $rrd_options);
        d_echo($rrd_cmd);
        if (is_file($graphfile)) {
            if (! $debug) {
                set_image_type();
                if ($output === 'base64') {
                    $imagedata = file_get_contents($graphfile);
                    $base64_output = base64_encode($imagedata);
                } else {
                    $fd = fopen($graphfile, 'r');
                    fpassthru($fd);
                    fclose($fd);
                }
            } else {
                echo `ls -l $graphfile`;
                echo '<img src="' . data_uri($graphfile, 'image/svg+xml') . '" alt="graph" />';
            }
            unlink($graphfile);
        } elseif (isset($rrd_filename) && ! Rrd::checkRrdExists($rrd_filename)) {
            graph_error($width < 200 ? 'No Data' : 'No Data file');
        } else {
            graph_error($width < 200 ? 'Draw Error' : 'Error Drawing Graph');
        }
    } else {
        graph_error($width < 200 ? 'Def Error' : 'Graph Definition Error');
    }
}
