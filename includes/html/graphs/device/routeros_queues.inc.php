<?php
// File: /opt/librenms/includes/graphs/routeros_queues.inc.php

// Ensure this script is included in LibreNMS
if (!is_array($config['graphs']['routeros_queues'])) {
    $config['graphs']['routeros_queues'] = [];
}

include_once($config['install_dir'] . "/includes/graphs/common.inc.php");

// Define graph parameters
$graph_title = "Aggregated RouterOS Queues";
$graph_type = "line";
$unit_text = "Bytes / Packets";

// Define RRD file path
$rrd_filename = $device['rrd_dir'] . "/routeros_queues.rrd";

// Check if the RRD file exists
if (!file_exists($rrd_filename)) {
    echo "No data available.\n";
    return;
}

// Define data sources
$def[] = "DEF:bytes_in={$rrd_filename}:total_bytes_in:AVERAGE";
$def[] = "DEF:bytes_out={$rrd_filename}:total_bytes_out:AVERAGE";
$def[] = "DEF:packets_in={$rrd_filename}:total_packets_in:AVERAGE";
$def[] = "DEF:packets_out={$rrd_filename}:total_packets_out:AVERAGE";

// Define graph lines and colors
$def[] = "LINE1:bytes_in#00FF00:Bytes In";
$def[] = "LINE1:bytes_out#0000FF:Bytes Out";
$def[] = "LINE1:packets_in#FF0000:Packets In";
$def[] = "LINE1:packets_out#FFA500:Packets Out";

// Add legends
$def[] .= "GPRINT:bytes_in:LAST:Current In\: %6.2lf%s";
$def[] .= "GPRINT:bytes_out:LAST:Current Out\: %6.2lf%s\\n";
$def[] .= "GPRINT:packets_in:LAST:Current Packets In\: %6.2lf%s";
$def[] .= "GPRINT:packets_out:LAST:Current Packets Out\: %6.2lf%s\\n";

// Optional: Add additional graph settings as needed
?>
