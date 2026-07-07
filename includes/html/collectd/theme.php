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
    LibrenmsConfig::set('collectd_rrd_color_opts', collectd_graph_color_options($graph_params));
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

/**
 * Merge graph request vars into $_GET for collectd handlers invoked via Graph::getRrdOptions().
 *
 * @param  array<string, mixed>  $request_vars
 */
function collectd_merge_request_vars(array $request_vars): void
{
    foreach ($request_vars as $name => $value) {
        if (is_scalar($value) && $value !== '') {
            $_GET[$name] = (string) $value;
        }
    }
}

/**
 * Resolve graph time range using GraphParameters when available.
 *
 * @return array{0: int, 1: int}
 */
function collectd_graph_time_range(): array
{
    $graph_params = collectd_active_graph_parameters();
    if ($graph_params instanceof GraphParameters) {
        $to = $graph_params->to > 0 ? $graph_params->to : time();

        return [$graph_params->from, $to];
    }

    $from = (int) ($_GET['from'] ?? time() - 86400);
    $to = (int) ($_GET['to'] ?? time());
    if ($to <= 0) {
        $to = time();
    }

    return [$from, $to];
}

/**
 * True when collectd is included by Graph::getRrdOptions() (modern /graph route).
 */
function collectd_uses_rrd_options_api(): bool
{
    return defined('IGNORE_ERRORS');
}

/**
 * Convert a collectd rrdtool shell command into Rrd::graph() options.
 * Strips options graph.inc.php adds via GraphParameters::toRrdOptions().
 *
 * @return list<string>
 */
function collectd_rrd_cmd_to_options(string $rrd_cmd): array
{
    if (! preg_match("/rrdtool\s+'graph'\s+'-'\s+/i", $rrd_cmd, $match, PREG_OFFSET_CAPTURE)) {
        return [];
    }

    $args_str = substr($rrd_cmd, $match[0][1] + strlen($match[0][0]));
    preg_match_all("/'(?:[^'\\\\]|\\\\.)*'|[^\s]+/", $args_str, $matches);
    $options = array_map(static fn ($token) => trim($token, "'"), $matches[0]);

    $skip_with_arg = ['-w', '-h', '-s', '-e'];
    $skip = ['-E', '-a', 'PNG', 'SVG', '--only-graph'];
    $filtered = [];

    for ($i = 0; $i < count($options); $i++) {
        $opt = $options[$i];

        if (in_array($opt, $skip_with_arg, true)) {
            $i++;

            continue;
        }

        if (in_array($opt, $skip, true)) {
            continue;
        }

        if (str_starts_with($opt, '--font')) {
            $i++;

            continue;
        }

        $filtered[] = $opt;
    }

    return $filtered;
}