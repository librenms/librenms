<?php

$name = 'haproxy';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'haproxy',
];

foreach (['haproxy_fe', 'haproxy_be', 'haproxy_srv', 'haproxy_li'] as $var) {
    if (isset($vars[$var])) {
        $vars[$var] = htmlspecialchars($vars[$var]);
    }
}

$app_data = $app->data;
$frontends = $app_data['frontends'] ?? [];
$backends = $app_data['backends'] ?? [];
$servers = $app_data['servers'] ?? [];
$listeners = $app_data['listeners'] ?? [];
ksort($frontends);
ksort($backends);

// figure out what is being viewed
$haproxy_type = 'totals';
$haproxy_info = null;
if (isset($vars['haproxy_be']) && isset($vars['haproxy_srv'])) {
    $haproxy_type = 'srv';
    $haproxy_info = $servers[$vars['haproxy_be']][$vars['haproxy_srv']] ?? null;
} elseif (isset($vars['haproxy_fe']) && isset($vars['haproxy_li'])) {
    $haproxy_type = 'li';
    $haproxy_info = $listeners[$vars['haproxy_fe']][$vars['haproxy_li']] ?? null;
} elseif (isset($vars['haproxy_be'])) {
    $haproxy_type = 'be';
    $haproxy_info = $backends[$vars['haproxy_be']] ?? null;
} elseif (isset($vars['haproxy_fe'])) {
    $haproxy_type = 'fe';
    $haproxy_info = $frontends[$vars['haproxy_fe']] ?? null;
}

print_optionbar_start();

$overview_label = 'Overview';
if ($haproxy_type == 'totals') {
    $overview_label = '<span class="pagemenu-selected">' . $overview_label . '</span>';
}
echo generate_link($overview_label, $link_array);

// each section is [title, items, var name, extra link vars]
$link_sections = [
    ['Frontends', array_keys($frontends), 'haproxy_fe', []],
    ['Backends', array_keys($backends), 'haproxy_be', []],
];

if (isset($vars['haproxy_be']) && ! empty($servers[$vars['haproxy_be']])) {
    $be_servers = array_keys($servers[$vars['haproxy_be']]);
    sort($be_servers);
    $link_sections[] = ['Servers in ' . $vars['haproxy_be'], $be_servers, 'haproxy_srv', ['haproxy_be' => $vars['haproxy_be']]];
}

if (isset($vars['haproxy_fe']) && ! empty($listeners[$vars['haproxy_fe']])) {
    $fe_listeners = array_keys($listeners[$vars['haproxy_fe']]);
    sort($fe_listeners);
    $link_sections[] = ['Listeners in ' . $vars['haproxy_fe'], $fe_listeners, 'haproxy_li', ['haproxy_fe' => $vars['haproxy_fe']]];
}

foreach ($link_sections as [$section_title, $section_items, $section_var, $section_extra]) {
    if (empty($section_items)) {
        continue;
    }
    $links = [];
    foreach ($section_items as $item) {
        $item_safe = htmlspecialchars((string) $item);
        $label = $item_safe;
        if (isset($vars[$section_var]) && $vars[$section_var] === $item_safe) {
            $label = '<span class="pagemenu-selected">' . $item_safe . '</span>';
        }
        $links[] = generate_link($label, $link_array, array_merge($section_extra, [$section_var => $item_safe]));
    }
    echo '<br>' . $section_title . ': ' . implode(', ', $links);
}

print_optionbar_end();

// status table for the overview or a backend
$status_rows = [];
if ($haproxy_type == 'totals') {
    foreach ($frontends as $px => $info) {
        $status_rows[] = ['Frontend', generate_link(htmlspecialchars((string) $px), $link_array, ['haproxy_fe' => htmlspecialchars((string) $px)]), $info];
    }
    foreach ($backends as $px => $info) {
        $status_rows[] = ['Backend', generate_link(htmlspecialchars((string) $px), $link_array, ['haproxy_be' => htmlspecialchars((string) $px)]), $info];
    }
}
if ($haproxy_type == 'totals' || $haproxy_type == 'be') {
    $show_servers = $haproxy_type == 'be' ? [$vars['haproxy_be'] => $servers[$vars['haproxy_be']] ?? []] : $servers;
    ksort($show_servers);
    foreach ($show_servers as $px => $px_servers) {
        ksort($px_servers);
        foreach ($px_servers as $sv => $info) {
            $px_safe = htmlspecialchars((string) $px);
            $sv_safe = htmlspecialchars((string) $sv);
            $status_rows[] = ['Server', generate_link($px_safe . '/' . $sv_safe, $link_array, ['haproxy_be' => $px_safe, 'haproxy_srv' => $sv_safe]), $info];
        }
    }
} elseif (isset($haproxy_info)) {
    $status_rows[] = [
        match ($haproxy_type) {
            'fe' => 'Frontend',
            'srv' => 'Server',
            'li' => 'Listener',
        },
        htmlspecialchars(implode('/', array_filter([$vars['haproxy_fe'] ?? $vars['haproxy_be'] ?? null, $vars['haproxy_srv'] ?? $vars['haproxy_li'] ?? null]))),
        $haproxy_info,
    ];
}

if (! empty($status_rows)) {
    echo '<div class="panel panel-default"><div class="panel-body"><div class="table-responsive">';
    echo '<table class="table table-condensed table-hover"><thead><tr>';
    echo '<th>Type</th><th>Name</th><th>Status</th><th>Mode</th><th>Check</th><th>Check Description</th><th>Check Health</th><th>Weight</th><th>Last Change</th><th>Last Session</th>';
    echo '</tr></thead><tbody>';
    foreach ($status_rows as [$row_type, $row_name, $info]) {
        // only the first word matters, as status may be like "UP 1/3"
        $status_label_class = match (preg_replace('/\s.*$/', '', (string) ($info['status'] ?? ''))) {
            'UP', 'OPEN' => 'label-success',
            'DOWN' => 'label-danger',
            'MAINT', 'DRAIN', 'NOLB' => 'label-warning',
            default => 'label-default',
        };
        echo '<tr><td>' . $row_type . '</td><td>' . $row_name . '</td>';
        echo '<td><span class="label ' . $status_label_class . '">' . htmlspecialchars((string) ($info['status'] ?? '')) . '</span></td>';
        echo '<td>' . htmlspecialchars((string) ($info['mode'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($info['check_status'] ?? '')) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($info['check_desc'] ?? '')) . '</td>';
        // health goes from 0 to rise+fall-1, being UP at rise or more
        $check_health = '';
        if (isset($info['check_health'], $info['check_rise'], $info['check_fall'])) {
            $check_health = $info['check_health'] . '/' . ($info['check_rise'] + $info['check_fall'] - 1)
                . ' (rise ' . $info['check_rise'] . ', fall ' . $info['check_fall'] . ')';
        }
        echo '<td>' . htmlspecialchars($check_health) . '</td>';
        echo '<td>' . htmlspecialchars((string) ($info['weight'] ?? '')) . '</td>';
        echo '<td>' . (isset($info['lastchg']) ? \LibreNMS\Util\Time::formatInterval($info['lastchg']) : '') . '</td>';
        // -1 means never
        $last_sess = '';
        if (isset($info['lastsess'])) {
            $last_sess = $info['lastsess'] < 0 ? 'never' : \LibreNMS\Util\Time::formatInterval($info['lastsess']);
        }
        echo '<td>' . $last_sess . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div></div></div>';
}

$haproxy_shared = require base_path('includes/haproxy-shared.inc.php');
$haproxy_graphs = require 'includes/html/graphs/application/haproxy-definitions.inc.php';

// http only graphs are skipped if it is known to not be http
// for the totals, that is if no frontends or backends are http
if ($haproxy_type == 'totals') {
    $is_http = false;
    foreach (array_merge($frontends, $backends) as $info) {
        if (($info['mode'] ?? null) == 'http') {
            $is_http = true;
            break;
        }
    }
} else {
    $is_http = ! isset($haproxy_info['mode']) || $haproxy_info['mode'] == 'http';
}

// RRD name for the item being viewed, used for checking if gated groups are active
$haproxy_item_rrd = match ($haproxy_type) {
    'fe' => 'fe_-_' . ($vars['haproxy_fe'] ?? ''),
    'be' => 'be_-_' . ($vars['haproxy_be'] ?? ''),
    'srv' => 'srv_-_' . ($vars['haproxy_be'] ?? '') . '_-_' . ($vars['haproxy_srv'] ?? ''),
    'li' => 'li_-_' . ($vars['haproxy_fe'] ?? '') . '_-_' . ($vars['haproxy_li'] ?? ''),
    default => null,
};

foreach ($haproxy_graphs as $key => $def) {
    if (! in_array($haproxy_type, $def['types']) || ($def['http'] && ! $is_http)) {
        continue;
    }

    // gated groups, such as QUIC, are only shown once they have been seen active
    $group = $def['group'] ?? null;
    if (isset($group) && $haproxy_shared['groups'][$group]['gated']) {
        $active = $app_data['gated'][$group] ?? [];
        if ($haproxy_type == 'totals' ? empty($active) : ! isset($active[$haproxy_item_rrd])) {
            continue;
        }
    }

    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_haproxy_' . $key;

    foreach (['haproxy_fe', 'haproxy_be', 'haproxy_srv', 'haproxy_li'] as $var) {
        if (isset($vars[$var])) {
            $graph_array[$var] = $vars[$var];
        } else {
            unset($graph_array[$var]);
        }
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $def['title'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
