<?php

use App\Models\Port;

/** @var Port $port */
/** @var \App\Models\Device $device */
$port = $port instanceof Port ? $port : Port::find($port['port_id']);
$rrd_list = [];

foreach ($port->transceivers as $transceiver) {
    $metrics = $transceiver->metrics()
        ->when($metric_type ?? null, fn ($q, $type) => $q->where('type', $type))
        ->when($vars['channel'] ?? null, fn ($q, $channel) => $q->where('channel', $channel))
        ->get();

    foreach ($metrics as $metric) {
        $rrd_filename = Rrd::name($device['hostname'], ['transceiver', $metric->type, $transceiver->index, $metric->channel]);
        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd_list[] = [
                'filename' => $rrd_filename,
                'descr' => trans_choice('port.transceivers.metrics.' . $metric->type, $transceiver->channels, ['channel' => $metric->channel]),
                'ds' => 'value',
            ];
        }
    }
}

$colours = 'mixed';
$nototal = 1;
$unit_text = empty($metric_type) ? '' : __('port.transceivers.units.' . $metric_type);
$divider = 1;
//$scale_min = 0;

require 'includes/html/graphs/generic_v3_multiline_float.inc.php';
