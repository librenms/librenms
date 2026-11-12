<?php

use LibreNMS\Data\Store\Rrd as RrdStore;

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'linux_pressure',
];

// Builds a option bar link, wrapped as selected if it is the current page.
//
// $label :: text for the link, already HTML escaped
// $link_vars :: vars to add to $link_array, such as ['cgroup' => 'system.slice_cron.service']
// $is_selected :: true if this link is for the page currently shown
//
// Returns the HTML for the link.
//
// $menu_link('Overview', ['view' => 'overview'], true)
$menu_link = function (string $label, array $link_vars, bool $is_selected) use ($link_array): string {
    $link = generate_link($label, $link_array, $link_vars);

    return $is_selected ? '<span class="pagemenu-selected">' . $link . '</span>' : $link;
};

// Every resource and stall type the kernel can report, in display order, keyed the
// same as the latest averages in $app->data.
$pressure_types = [
    'cpu_some' => ['resource' => 'cpu', 'stall_type' => 'some', 'label' => 'CPU some'],
    'cpu_full' => ['resource' => 'cpu', 'stall_type' => 'full', 'label' => 'CPU full'],
    'io_some' => ['resource' => 'io', 'stall_type' => 'some', 'label' => 'IO some'],
    'io_full' => ['resource' => 'io', 'stall_type' => 'full', 'label' => 'IO full'],
    'memory_some' => ['resource' => 'memory', 'stall_type' => 'some', 'label' => 'Memory some'],
    'memory_full' => ['resource' => 'memory', 'stall_type' => 'full', 'label' => 'Memory full'],
    'irq_full' => ['resource' => 'irq', 'stall_type' => 'full', 'label' => 'IRQ full'],
];

$system_pressure = $app->data['system_pressure'] ?? [];
$cgroup_pressure = $app->data['cgroup_pressure'] ?? [];

// Cgroup names have slashes in them, which does not work in a URL var, so the
// RRD safe form of the name is what gets passed around and the real one is just the label.
$cgroup_labels = [];
foreach ($app->data['cgroups'] ?? [] as $cgroup) {
    $cgroup_labels[RrdStore::safeName($cgroup)] = $cgroup;
}

// Only offer comparing the resources and stall types at least one cgroup reports.
// Data from before the averages were saved has none, so fall back to all of them.
$compare_types = [];
foreach ($pressure_types as $pressure_key => $pressure_type) {
    foreach ($cgroup_pressure as $pressure) {
        if (isset($pressure[$pressure_key])) {
            $compare_types[$pressure_key] = $pressure_type;
            break;
        }
    }
}
if (count($compare_types) === 0) {
    $compare_types = $pressure_types;
}

// The RRD safe name of the cgroup being looked at, or null for the system wide pressure.
// Used by the system, cgroup, and resources pages.
$selected_cgroup = isset($vars['cgroup'], $cgroup_labels[$vars['cgroup']]) ? $vars['cgroup'] : null;

// Which page to show, one of system, cgroup, resources, overview, or compare.
// resources compares the resources against each other, for the system or $selected_cgroup.
$page_view = $selected_cgroup === null ? 'system' : 'cgroup';
if (($vars['view'] ?? null) === 'resources') {
    $page_view = 'resources';
} elseif (count($cgroup_labels) > 0) {
    if (isset($vars['compare'], $compare_types[$vars['compare']])) {
        $page_view = 'compare';
    } elseif (($vars['view'] ?? null) === 'overview') {
        $page_view = 'overview';
    }
}

print_optionbar_start();

echo $menu_link('System', [], $page_view === 'system');

// From a cgroup page, go to the resources page for that cgroup. From anywhere else, the system one.
$resources_vars = ['view' => 'resources'];
if ($page_view === 'cgroup') {
    $resources_vars['cgroup'] = $selected_cgroup;
}
echo ' | ' . $menu_link('Resources', $resources_vars, $page_view === 'resources');

if (count($cgroup_labels) > 0) {
    echo ' | ' . $menu_link('Overview', ['view' => 'overview'], $page_view === 'overview');

    $compare_links = [];
    foreach ($compare_types as $pressure_key => $pressure_type) {
        $compare_links[] = $menu_link($pressure_type['label'], ['compare' => $pressure_key], $page_view === 'compare' && $vars['compare'] === $pressure_key);
    }
    echo "<br>\nCompare: " . implode(', ', $compare_links);

    // on the resources page, picking a cgroup stays on the resources page
    $cgroup_links = [];
    foreach ($cgroup_labels as $safe_cgroup => $cgroup) {
        $cgroup_vars = $page_view === 'resources' ? ['view' => 'resources', 'cgroup' => $safe_cgroup] : ['cgroup' => $safe_cgroup];
        $cgroup_links[] = $menu_link(htmlspecialchars($cgroup), $cgroup_vars, in_array($page_view, ['cgroup', 'resources']) && $selected_cgroup === $safe_cgroup);
    }
    echo "<br>\nCgroups: " . implode(', ', $cgroup_links);
}

print_optionbar_end();

if ($page_view === 'overview') {
    $average = in_array($vars['average'] ?? null, ['avg10', 'avg60', 'avg300']) ? $vars['average'] : 'avg300';
    // highest sorts by the highest pressure a cgroup has of any type, name sorts by the cgroup name,
    // and a pressure type key sorts by that column
    $sort = $vars['sort'] ?? 'highest';
    if ($sort !== 'name' && ! isset($compare_types[$sort])) {
        $sort = 'highest';
    }

    $average_links = [];
    foreach (['avg10', 'avg60', 'avg300'] as $average_option) {
        $average_links[] = $menu_link($average_option, ['view' => 'overview', 'average' => $average_option, 'sort' => $sort], $average === $average_option);
    }

    // one row per cgroup, with the highest value saved for sorting
    $table_rows = [];
    foreach ($cgroup_labels as $safe_cgroup => $cgroup) {
        $row_values = [];
        foreach ($compare_types as $pressure_key => $pressure_type) {
            $row_values[$pressure_key] = $cgroup_pressure[$cgroup][$pressure_key][$average] ?? null;
        }
        $table_rows[] = [
            'safe_cgroup' => $safe_cgroup,
            'cgroup' => $cgroup,
            'values' => $row_values,
            'highest' => max([0, ...array_values(array_filter($row_values, 'is_numeric'))]),
        ];
    }

    usort($table_rows, function ($row_a, $row_b) use ($sort) {
        if ($sort === 'name') {
            return strcmp($row_a['cgroup'], $row_b['cgroup']);
        }
        $value_a = $sort === 'highest' ? $row_a['highest'] : ($row_a['values'][$sort] ?? -1);
        $value_b = $sort === 'highest' ? $row_b['highest'] : ($row_b['values'][$sort] ?? -1);

        return $value_b <=> $value_a ?: strcmp($row_a['cgroup'], $row_b['cgroup']);
    });

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Percent Stalled, ' . $average . ' &nbsp; <small>' . implode(' | ', $average_links) . '</small></h3>
    </div>
    <div class="panel-body">
    <table class="table table-condensed table-hover table-striped">
    <thead><tr><th>' . $menu_link('Cgroup', ['view' => 'overview', 'average' => $average, 'sort' => 'name'], $sort === 'name') . '</th>';
    foreach ($compare_types as $pressure_key => $pressure_type) {
        echo '<th>' . $menu_link($pressure_type['label'], ['view' => 'overview', 'average' => $average, 'sort' => $pressure_key], $sort === $pressure_key) . '</th>';
    }
    echo '<th>' . $menu_link('Highest', ['view' => 'overview', 'average' => $average, 'sort' => 'highest'], $sort === 'highest') . "</th></tr></thead>\n<tbody>\n";

    // the system wide pressure goes first as a point of reference and is not sorted
    echo '<tr class="info"><td>' . generate_link('System', $link_array) . '</td>';
    foreach ($compare_types as $pressure_key => $pressure_type) {
        $value = $system_pressure[$pressure_key][$average] ?? null;
        echo '<td>' . (is_numeric($value) ? number_format($value, 2) : '-') . '</td>';
    }
    echo "<td></td></tr>\n";

    foreach ($table_rows as $table_row) {
        echo '<tr><td>' . generate_link(htmlspecialchars($table_row['cgroup']), $link_array, ['cgroup' => $table_row['safe_cgroup']]) . '</td>';
        foreach ($table_row['values'] as $value) {
            echo '<td>' . (is_numeric($value) ? number_format($value, 2) : '-') . '</td>';
        }
        echo '<td>' . number_format($table_row['highest'], 2) . "</td></tr>\n";
    }

    echo "</tbody></table>\n</div>\n</div>\n";

    return;
}

// Each graph is a list of graph_array vars and the panel title.
$graphs = [];
if ($page_view === 'compare') {
    $compare_type = $compare_types[$vars['compare']];
    $averages = [
        'total' => 'Stall Time',
        'avg10' => 'Percent Stalled, avg10',
        'avg60' => 'Percent Stalled, avg60',
        'avg300' => 'Percent Stalled, avg300',
    ];
    foreach ($averages as $average => $average_title) {
        $graphs[] = [
            'title' => $compare_type['label'] . ' ' . $average_title,
            'graph_vars' => [
                'type' => 'application_linux_pressure_cgroups',
                'resource' => $compare_type['resource'],
                'stall_type' => $compare_type['stall_type'],
                'average' => $average,
            ],
        ];
    }
} elseif ($page_view === 'resources') {
    // one graph per stall type and average, with a line per resource
    foreach (['some', 'full'] as $stall_type) {
        foreach (['avg10', 'avg60', 'avg300'] as $average) {
            $graphs[] = [
                'title' => ucfirst($stall_type) . ' ' . $average,
                'graph_vars' => [
                    'type' => 'application_linux_pressure_resources',
                    'stall_type' => $stall_type,
                    'average' => $average,
                ],
            ];
        }
    }
} else {
    $source_graphs = [
        'linux_pressure_cpu' => 'CPU Pressure',
        'linux_pressure_io' => 'IO Pressure',
        'linux_pressure_memory' => 'Memory Pressure',
        'linux_pressure_irq' => 'IRQ Pressure',
        'linux_pressure_stall_time' => 'Stall Time',
    ];

    // irq pressure is only reported by 6.1+ kernels with CONFIG_IRQ_TIME_ACCOUNTING
    $rrd_prefix = $selected_cgroup === null ? 'system' : 'cgroup___' . $selected_cgroup;
    $irq_rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_pressure', $app['app_id'], $rrd_prefix . '___irq___full']);
    if (! Rrd::checkRrdExists($irq_rrd_filename)) {
        unset($source_graphs['linux_pressure_irq']);
    }

    foreach ($source_graphs as $graph_name => $graph_title) {
        $graphs[] = ['title' => $graph_title, 'graph_vars' => ['type' => 'application_' . $graph_name]];
    }
}

// the system, cgroup, and resources pages graph a single cgroup when one is selected
if ($page_view !== 'compare' && $selected_cgroup !== null) {
    foreach (array_keys($graphs) as $graph_index) {
        $graphs[$graph_index]['graph_vars']['cgroup'] = $selected_cgroup;
    }
}

foreach ($graphs as $graph) {
    $graph_array = [
        'height' => '100',
        'width' => '215',
        'to' => App\Facades\LibrenmsConfig::get('time.now'),
        'id' => $app['app_id'],
        ...$graph['graph_vars'],
    ];

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph['title'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
