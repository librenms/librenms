<?php

use LibreNMS\Config;

global $debug;

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
$period = ($to - $from);
$prev_from = ($from - $period);

$graph_image_type = $vars['graph_type'] ?? Config::get('webui.graph_type');
$rrd_options = '';

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

if (! empty($error_msg)) {
    // We have an error :(
    graph_error($error_msg);

    return;
}

if ($auth === null) {
    // We are unauthenticated :(
    graph_error($width < 200 ? 'No Auth' : 'No Authorization');

    return;
}

if ($graph_image_type === 'svg') {
    $rrd_options .= ' --imgformat=SVG';
    if ($width < 350) {
        $rrd_options .= ' -m 0.75 -R light';
    }
}

// command output requested
if (! empty($command_only)) {
    echo "<div class='infobox'>";
    echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Command</p>";
    echo "<pre class='rrd-pre'>";
    echo escapeshellcmd('rrdtool ' . Rrd::buildCommand('graph', Config::get('temp_dir') . '/' . strgen(), $rrd_options));
    echo '</pre>';
    try {
        Rrd::graph($rrd_options);
    } catch (\LibreNMS\Exceptions\RrdGraphException $e) {
        echo "<p style='font-size: 16px; font-weight: bold;'>RRDTool Output</p>";
        echo "<pre class='rrd-pre'>";
        echo $e->getMessage();
        echo '</pre>';
    }
    echo '</div>';

    return;
}

// graph sent file not found flag
if (! empty($no_file)) {
    graph_error($width < 200 ? 'No Data' : 'No Data file ' . $no_file);

    return;
}

if (empty($rrd_options)) {
    graph_error($width < 200 ? 'Def Error' : 'Graph Definition Error');

    return;
}

// Generating the graph!
try {
    $image_data = Rrd::graph($rrd_options);

    // output the graph
    if (\LibreNMS\Util\Debug::isEnabled()) {
        echo '<img src="data:' . get_image_type($graph_image_type) . ';base64,' . base64_encode($image_data) . '" alt="graph" />';
    } else {
        header('Content-type: ' . get_image_type(Config::get('webui.graph_type')));
        echo $output === 'base64' ? base64_encode($image_data) : $image_data;
    }
} catch (\LibreNMS\Exceptions\RrdGraphException $e) {
    if (isset($rrd_filename) && ! Rrd::checkRrdExists($rrd_filename)) {
        graph_error($width < 200 ? 'No Data' : 'No Data file ' . basename($rrd_filename));
    } else {
        graph_error($width < 200 ? 'Draw Error' : 'Error Drawing Graph: ' . $e->getMessage());
    }
}
