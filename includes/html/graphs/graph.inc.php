<?php

use LibreNMS\Config;
use LibreNMS\Data\Graphing\GraphParameters;
use LibreNMS\Enum\ImageFormat;

try {
    if (isset($vars['device'])) {
        $device = is_numeric($vars['device'])
            ? device_by_id_cache($vars['device'])
            : device_by_name($vars['device']);
        if (isset($device['device_id'])) {
            DeviceCache::setPrimary($device['device_id']);
        }
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
    $env = [];

    if (session('preferences.timezone')) {
        $env['TZ'] = session('preferences.timezone');
    }

    require Config::get('install_dir') . "/includes/html/graphs/$type/auth.inc.php";

    if ($auth && is_customoid_graph($type, $subtype)) {
        $unit = $vars['unit'];
        include Config::get('install_dir') . '/includes/html/graphs/customoid/customoid.inc.php';
    } elseif ($auth && is_file(Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php")) {
        include Config::get('install_dir') . "/includes/html/graphs/$type/$subtype.inc.php";
    } else {
        graph_error("$type*$subtype Graph Template Missing", "$type*$subtype");
    }

    if ($auth === null) {
        // We are unauthenticated :(
        graph_error('No Authorization', 'No Auth');

        return;
    }

    // check after auth
    if (isset($vars['device']) && empty($device['device_id'])) {
        throw new \LibreNMS\Exceptions\RrdGraphException('Device not found');
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
            Rrd::graph($rrd_options, $env);
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
        graph_error('Graph Definition Error', 'Def Error');

        return;
    }

    // Generating the graph!
    $image_data = Rrd::graph($rrd_options, $env);

    // output the graph
    if (\LibreNMS\Util\Debug::isEnabled()) {
        echo '<img src="data:' . ImageFormat::forGraph()->contentType() . ';base64,' . base64_encode($image_data) . '" alt="graph" />';
    } else {
        header('Content-type: ' . ImageFormat::forGraph()->contentType());
        echo (isset($vars['output']) && $vars['output'] === 'base64') ? base64_encode($image_data) : $image_data;
    }
} catch (\LibreNMS\Exceptions\RrdGraphException $e) {
    if (\LibreNMS\Util\Debug::isEnabled()) {
        throw $e;
    }

    if (isset($rrd_filename) && ! Rrd::checkRrdExists($rrd_filename)) {
        graph_error('No Data file ' . basename($rrd_filename), 'No Data');
    } else {
        graph_error('Error Drawing Graph: ' . $e->getMessage(), 'Draw Error');
    }
}
