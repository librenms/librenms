<?php

/**
 * Collectd helpers that reuse GraphParameters from graph.inc.php.
 *
 * Standard graphs merge $graph_params->toRrdOptions() into the rrdtool command.
 * Collectd builds a shell command string; these helpers extract the same options.
 */

use App\Facades\LibrenmsConfig;
use LibreNMS\CollectdColor;
use LibreNMS\Data\Graphing\GraphParameters;

/** @var GraphParameters|null */
$collectd_graph_parameters = null;

/**
 * Store GraphParameters created by graph.inc.php for this request.
 */
function collectd_bind_graph_parameters(GraphParameters $graph_params): void
{
    global $collectd_graph_parameters;
    $collectd_graph_parameters = $graph_params;

    LibrenmsConfig::set('rrd_width', $graph_params->width);
    LibrenmsConfig::set('rrd_height', $graph_params->height);
}

function collectd_active_graph_parameters(): ?GraphParameters
{
    global $collectd_graph_parameters;

    return $collectd_graph_parameters instanceof GraphParameters ? $collectd_graph_parameters : null;
}

function collectd_graph_style(): string
{
    $graph_params = collectd_active_graph_parameters();

    if ($graph_params instanceof GraphParameters) {
        return $graph_params->style ?: session('applied_site_style', 'light');
    }

    return $_GET['style'] ?? session('applied_site_style', 'light');
}

/**
 * Color (-c) options from GraphParameters::toRrdOptions() — same source as graph.inc.php.
 *
 * @return list<string>
 */
function collectd_graph_color_options(?GraphParameters $graph_params = null): array
{
    $graph_params ??= collectd_active_graph_parameters();
    if (! $graph_params instanceof GraphParameters) {
        return [];
    }

    $color_opts = [];
    $options = $graph_params->toRrdOptions();
    $count = count($options);
    for ($i = 0; $i < $count - 1; $i++) {
        if ($options[$i] === '-c') {
            $color_opts[] = '-c';
            $color_opts[] = $options[$i + 1];
        }
    }

    return $color_opts;
}

/**
 * Append GraphParameters color options to a collectd rrdtool shell command.
 */
function collectd_append_graph_color_options(string $rrd_cmd, ?GraphParameters $graph_params = null): string
{
    foreach (collectd_graph_color_options($graph_params) as $opt) {
        $rrd_cmd .= ' ' . escapeshellarg((string) $opt);
    }

    return $rrd_cmd;
}

function collectd_area_fade_background(): ?CollectdColor
{
    if (collectd_graph_style() !== 'dark') {
        return null;
    }

    $options = collectd_graph_color_options();
    $count = count($options);
    for ($i = 0; $i < $count - 1; $i++) {
        if ($options[$i] === '-c' && preg_match('/^BACK#([0-9A-Fa-f]{6})/', $options[$i + 1], $matches)) {
            return new CollectdColor($matches[1]);
        }
    }

    return new CollectdColor('2e3338');
}
