<?php

use LibreNMS\Config;
use LibreNMS\Data\Graphing\GraphParameters;
use LibreNMS\Enum\ImageFormat;

global $debug;

if (isset($vars['device'])) {
    $device = is_numeric($vars['device'])
        ? device_by_id_cache($vars['device'])
        : device_by_name($vars['device']);
    DeviceCache::setPrimary($device['device_id']);
}

// variables for included graphs
$graph_params = new GraphParameters($vars);
// set php variables for legacy graphs
$type = $graph_params->type;
$subtype = $graph_params->subtype;
$height = $graph_params->height;
$width = $graph_params->width;
$from = $graph_params->from;
$to = $graph_params->to;
$period = $graph_params->period;
$prev_from = $graph_params->prev_from;
$inverse = $graph_params->inverse;
$in = $graph_params->in;
$out = $graph_params->out;
$float_precision = $graph_params->float_precision;
$title = $graph_params->visible('title');
$nototal = ! $graph_params->visible('total');
$nodetails = ! $graph_params->visible('details');
$noagg = ! $graph_params->visible('aggregate');
$rrd_options = '';

require Config::get('install_dir') . "/includes/html/graphs/$type/auth.inc.php";

if ($auth && is_customoid_graph($type, $subtype)) {
    $unit = $vars['unit'];
    include Config::get('install_dir') . '/includes/html/graphs/customoid/customoid.inc.php';
} elseif ($auth && is_file(Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php")) {
    include Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php";
} else {
    graph_error("$type*$subtype ");
    // Graph Template Missing");
}

if ($auth === null) {
    // We are unauthenticated :(
    graph_error($width < 200 ? 'No Auth' : 'No Authorization');

    return;
}

$rrd_options = $graph_params . ' ' . $rrd_options;

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

if (empty($rrd_options)) {
    graph_error($width < 200 ? 'Def Error' : 'Graph Definition Error');

    return;
}

// Generating the graph!
try {
    $image_data = Rrd::graph($rrd_options);

    // output the graph
    if (\LibreNMS\Util\Debug::isEnabled()) {
        echo '<img src="data:' . ImageFormat::forGraph()->contentType() . ';base64,' . base64_encode($image_data) . '" alt="graph" />';
    } else {
        header('Content-type: ' . ImageFormat::forGraph()->contentType());
        echo $output === 'base64' ? base64_encode($image_data) : $image_data;
    }
} catch (\LibreNMS\Exceptions\RrdGraphException $e) {
    if (\LibreNMS\Util\Debug::isEnabled()) {
        throw $e;
    }

    if (isset($rrd_filename) && ! Rrd::checkRrdExists($rrd_filename)) {
        graph_error($width < 200 ? 'No Data' : 'No Data file ' . basename($rrd_filename));
    } else {
        graph_error($width < 200 ? 'Draw Error' : 'Error Drawing Graph: ' . $e->getMessage());
    }
}
