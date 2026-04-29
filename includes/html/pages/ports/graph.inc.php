<?php

use App\Models\Port;
use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Util\Url;

/** @var string $displayLists */
/** @var bool $hideSearch */
/** @var array $filterFields */
/** @var string $subformat */

echo '<div class="panel panel-default panel-condensed">';
echo '<div class="panel-heading">';
echo $displayLists;
echo '</div>';
echo '<div class="panel-body">';

echo Blade::render('<x-filter :fields="$fields" id="port-filter" :hide="$hide" :reload="true" class="tw:mb-3"/>', [
    'hide' => $hideSearch,
    'fields' => $filterFields,
]);

$ports = Port::hasAccess(request()->user())
    ->with(['device', 'device.location'])
    ->isValid()
    ->when(request()->array('filter'), fn(Builder $query, $filters) => $query->applyFilters($filters))
    ->leftJoin('devices', 'ports.device_id', 'devices.device_id')
    ->orderBy('hostname')
    ->orderBy('ifIndex')
    ->get(['ports.*']);

$ports = match ($vars['sort'] ?? '') {
    'traffic' => $ports->sortBy('ifOctets_rate', descending: true),
    'traffic_in' => $ports->sortBy('ifInOctets_rate', descending: true),
    'traffic_out' => $ports->sortBy('ifOutOctets_rate', descending: true),
    'packets' => $ports->sortBy('ifUcastPkts_rate', descending: true),
    'packets_in' => $ports->sortBy('ifInUcastOctets_rate', descending: true),
    'packets_out' => $ports->sortBy('ifOutUcastOctets_rate', descending: true),
    'errors' => $ports->sortBy('ifErrors_rate', descending: true),
    'speed' => $ports->sortBy('ifSpeed', descending: true),
    'port' => $ports->sortBy('ifDescr'),
    'media' => $ports->sortBy('ifType'),
    'descr' => $ports->sortBy('ifAlias'),
    default => $ports,
};

foreach ($ports as $port) {
    $graph_type = 'port_' . $subformat;

    if (session('widescreen')) {
        $width = 357;
        $width_div = 438;
    } else {
        $width = 315;
        $width_div = 393;
    }

    $graph_array = [
        'height' => 100,
        'width' => $width,
        'id' => $port->port_id,
        'type' => $graph_type,
        'from' => '-1d',
        'legend' => 'no',
        'title' => 'yes',
    ];

    $link = Url::graphPageUrl($graph_type, Arr::except($graph_array, ['height', 'width', 'legend', 'title']));
    $graph = Url::lazyGraphTag($graph_array);

    echo "<div class='graph-all-common'>";
    echo Url::portLink($port, $graph, $graph_type, url: $link);
    echo '</div>';
}

echo '</div>';
