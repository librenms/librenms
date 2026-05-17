# 2.1 App Pages

This chapter covers UI patterns for rendering application data on the device Apps tab. Choose a pattern based on how your data is structured: a flat list of graphs, tabbed sections, per-instance drill-down, or a combination.

## App Pages (UI)

LibreNMS renders app pages from template files in `includes/html/pages/device/apps/`. The filename must match your app type.

### Page Structure

App pages can range from a single flat page to a multi-section hierarchy with tabs, format toggles, and per-instance views. Here are the common structures:

**Single page** (all content in one file):

```text
device/device=2/tab=apps/app=myapp
+-----------------------------------------------------+
| myapp                                              |
+-----------------------------------------------------+
| [graphs and content]                                |
+-----------------------------------------------------+
```

**Overview + Focused views** (main page + sub-pages via URL params):

```text
device/device=2/tab=apps/app=borgbackup
+-----------------------------------------------------+
| borgbackup                                          |
+-----------------------------------------------------+
| [overview graphs and summary]                        |
+-----------------------------------------------------+

device/device=2/tab=apps/app=borgbackup/borgrepo=Alfader
+-----------------------------------------------------+
| borgbackup / Alfader                                |
+-----------------------------------------------------+
| [focused graphs for this specific instance]          |
+-----------------------------------------------------+
```

**Multi-section with tabs, format toggles, and per-item views** (Ports-style):

```text
device/device=2/tab=ports
+-----------------------------------------------------+
| Ports                                              |
+-----------------------------------------------------+
| [Overview] | [ARP Table] | [IPv6 ND Table]          |  <- section tabs
|                                                     |
| View: [Basic] | [Detail]    Graphs: [Bits] | ...   |  <- format + graph toggle
|                                                     |
| +-----------------------------------------------+   |
| | Interface 1    | Status | In    | Out          |  |
| +-----------------------------------------------+   |
| | eth0           | up     | 1.2G  | 800M         |  |
    ...
+-----------------------------------------------------+

device/device=2/tab=ports/port=1
+-----------------------------------------------------+
| Ports / eth0                                        |
+-----------------------------------------------------+
| [Graphs] | [Real time] | [Eventlog] | [Notes]     |  <- port sub-tabs
|                                                     |
| [Bits] | [Packets] | [Errors]                      |  <- graph selector
|                                                     |
| +-----------------------------------------------+   |
| |           [~~~~~graph~~~~~]                    |  |
| +-----------------------------------------------+   |
+-----------------------------------------------------+
```

This structure combines:

- **Section tabs** (Overview, ARP Table) switch between different data sections
- **Format toggle** (Basic, Detail) changes the table layout
- **Graph selector** (Bits, Errors) switches which graph is shown
- **Per-item sub-tabs** (Graphs, Real time, Eventlog) appear when an item is selected

Use `$vars` to read URL parameters and switch rendering accordingly.

### App Overview

Each file receives these variables:

| Variable | Description |
| --- | --- |
| `$device` | Device array |
| `$app` | Application model (has `app_id`, `app_type`, `data`, etc.) |
| `$vars` | URL query parameters (used for instance selection) |
| `$graph_array` | Build this to pass to `print-graphrow.inc.php` |

All app pages live at `includes/html/pages/device/apps/{app_name}.inc.php`.

As a developer, you should sketch how your app page will look before writing code. Here is a minimal example rendered as a user sees it:

???+ info "Pattern 1 rendered (Flat Graphs)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > appname                  |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Metric 1                                      |   |
    | +-----------------------------------------------+   |
    | |                                               |   |
    | |           [~~~~~graph~~~~~]                   |   |
    | |                                               |   |
    | +-----------------------------------------------+   |
    |                                                     |
    | +-----------------------------------------------+   |
    | | Metric 2                                      |   |
    | +-----------------------------------------------+   |
    | |                                               |   |
    | |           [~~~~~graph~~~~~]                   |   |
    | |                                               |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Each panel wraps one graph. The title comes from the `$graphs` map key.


The option bar lists instances as clickable links. Selecting one swaps to instance graphs.

### Pattern 1: Flat Graphs

Simple apps with no per-instance breakdown just loop over a list of graphs.

??? example "Pattern 1: Flat Graphs"
    ```php
    <?php

    $graphs = [
        'appname_metric1' => 'Metric 1',
        'appname_metric2' => 'Metric 2',
    ];

    foreach ($graphs as $key => $text) {
        $graph_type = $key;
        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app['app_id'],
            'type' => 'application_' . $key,
        ];

        echo '<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $text . '</h3>
        </div>
        <div class="panel-body">
        <div class="row">';
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
    ```

### Pattern 2: Tabbed Sections

Use `print_optionbar_start()/end()` to create a navigation bar between sections (e.g., system vs queries vs InnoDB).

???+ info "Pattern 2 rendered (Tabbed Sections)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > mysql                    |
    +-----------------------------------------------------+
    | [System] | [Queries] | [InnoDB]                     |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Query Duration                                |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Tabs switch which set of graphs is displayed. State is stored in `$vars['app_section']`.

??? example "Pattern 2: Tabbed Sections"
    ```php
    <?php

    print_optionbar_start();

    $app_sections = ['system' => 'System', 'queries' => 'Queries'];
    $sep = '';
    foreach ($app_sections as $section => $label) {
        echo $sep;
        $vars['app_section'] ??= $section; // default to first

        if ($vars['app_section'] == $section) {
            echo "<span class='pagemenu-selected'>";
        }
        echo generate_link($label, $vars, ['app_section' => $section]);
        if ($vars['app_section'] == $section) {
            echo '</span>';
        }
        $sep = ' | ';
    }

    print_optionbar_end();

    $graphs['system'] = ['app_metric1' => 'Metric 1'];
    $graphs['queries'] = ['app_metric2' => 'Metric 2'];

    foreach ($graphs[$vars['app_section']] as $key => $text) {
        $graph_type = $key;
        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app['app_id'],
            'type' => 'application_' . $key,
        ];

        echo '<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $text . '</h3>
        </div>
        <div class="panel-body">
        <div class="row">';
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
    ```

### Pattern 3: Per-Instance Breakdown

Use an option bar to list instances (sources, disks, containers). Show aggregated overview graphs when no instance is selected, and instance-specific graphs when one is selected.

When expected count of instances is high, I a network ports, disks, databases, don't use Instance in header.

???+ info "Pattern 3 rendered (Per-Instance Breakdown)"
    ```
+---------------------------------------------------------+
    | [Device: router01]  Apps > chronyd                  |
    +-----------------------------------------------------+
    | Overview | Instances: NTP, PTP, GPS                 |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Overview 1                                    |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                   |   |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

??? example "Pattern 3: Per-Instance Breakdown"
    ```php
    <?php

    $link_array = [
        'page' => 'device',
        'device' => $device['device_id'],
        'tab' => 'apps',
        'app' => 'appname',
    ];

    print_optionbar_start();

    echo generate_link('Overview', $link_array);
    echo ' | Instances: ';

    $instances = $app->data['instances'] ?? [];
    sort($instances);
    foreach ($instances as $index => $instance) {
        $label = $vars['instance'] == $instance
            ? '<span class="pagemenu-selected">' . $instance . '</span>'
            : $instance;

        echo generate_link($label, $link_array, ['instance' => $instance]);
        if ($index < count($instances) - 1) {
            echo ', ';
        }
    }

    print_optionbar_end();

    if (! isset($vars['instance'])) {
        $graphs = [
            'appname_overview1' => 'Overview 1',
            'appname_overview2' => 'Overview 2',
        ];
    } else {
        $graphs = [
            'appname_instance1' => 'Instance Metric 1',
            'appname_instance2' => 'Instance Metric 2',
        ];
    }

    foreach ($graphs as $key => $text) {
        $graph_type = $key;
        $graph_array = [
            'height' => '100',
            'width' => '215',
            'to' => \App\Facades\LibrenmsConfig::get('time.now'),
            'id' => $app['app_id'],
            'type' => 'application_' . $key,
        ];

        if (isset($vars['instance'])) {
            $graph_array['instance'] = $vars['instance'];
        }

        echo '<div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">' . $text . '</h3>
        </div>
        <div class="panel-body">
        <div class="row">';
        include 'includes/html/print-graphrow.inc.php';
        echo '</div></div></div>';
    }
    ```

### Pattern 4: Mix of Text/Table + Graphs

Show instance details (name, serial, status) alongside graphs. Useful when you have metadata that does not fit in a graph.

???+ info "Pattern 4 rendered (Text + Graphs)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > smart                    |
    +-----------------------------------------------------+
    | All drives | Graphs                                 |
    +-----------------------------------------------------+
    |                                                     |
    | Model: Samsung SSD 870 QVO              [selected]  |
    | Serial: S5EWNX0N123456                              |
    | Vendor: Samsung                                     |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+   |
    | | Temperature                                    |  |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                    |  |
    | +-----------------------------------------------+   |
    |                                                     |
    | +-----------------------------------------------+   |
    | | Reallocated Sectors                           |   |
    | +-----------------------------------------------+   |
    | |           [~~~~~graph~~~~~]                    |  |
    | +-----------------------------------------------+   |
    +-----------------------------------------------------+
    ```

Instance metadata is printed as text labels above the graphs. Use when you have attributes like serial numbers, model names, or health status that graphs cannot show.

??? example "Pattern 4: Mix of Text/Table + Graphs"
    ```php
    <?php
    // ... option bar for instance selection (see Pattern 3) ...

    if (isset($vars['disk'])) {
        $currentDisk = $app->data['disks'][$vars['disk']] ?? [];

        print_optionbar_start();

        $diskFields = [
            'model' => 'Model',
            'serial' => 'Serial',
            'vendor' => 'Vendor',
        ];

        foreach ($diskFields as $field => $label) {
            if (isset($currentDisk[$field])) {
                echo "{$label}: {$currentDisk[$field]}<br>\n";
            }
        }

        print_optionbar_end();

        $graphs = [
            'app_metric1' => 'Metric 1',
            'app_metric2' => 'Metric 2',
        ];
    }
    // ... render graphs (see Pattern 1) ...
    ```


### Pattern 5: Graph Selector

Use an option bar to let users switch between different graph types (e.g., Bits vs Packets vs Errors). The Ports page uses this to show different traffic graphs.

???+ info "Pattern 5 rendered (Graph Selector)"
    ```text
    +-----------------------------------------------------+
    | [Device: router01]  Apps > myapp                   |
    +-----------------------------------------------------+
    | Graphs: [Bits] | [Packets] | [Errors]              |
    +-----------------------------------------------------+
    |                                                     |
    | +-----------------------------------------------+  |
    | | Bits/sec                                     |  |
    | +-----------------------------------------------+  |
    | |           [~~~~~graph~~~~~]                    |  |
    | +-----------------------------------------------+  |
    +-----------------------------------------------------+
    ```

State is stored in `$vars['graph']`. Combine with Pattern 3 (instance selection) or Pattern 6 (format toggle) as needed.

??? example "Pattern 5: Graph Selector"
    ```php
    <?php

    $vars['graph'] ??= 'bits';
    $graphs = ['bits' => 'Bits', 'upkts' => 'Packets', 'errors' => 'Errors'];

    print_optionbar_start();
    $sep = '';
    foreach ($graphs as $graph => $label) {
        echo $sep;
        if ($vars['graph'] == $graph) {
            echo '<span class="pagemenu-selected">';
        }
        echo generate_link($label, $vars, ['graph' => $graph]);
        if ($vars['graph'] == $graph) {
            echo '</span>';
        }
        $sep = ' | ';
    }
    print_optionbar_end();

    $graph_type = 'appname_' . $vars['graph'];
    $graph_array = [
        'height' => '100',
        'width' => '215',
        'to' => \App\Facades\LibrenmsConfig::get('time.now'),
        'id' => $app['app_id'],
        'type' => 'application_' . $graph_type,
    ];

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graphs[$vars['graph']] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div></div>';
    ```

### Navigation Button Pattern

Use `print_optionbar_start()/end()` with pipe-separated links. Active items get `<span class="pagemenu-selected">`.

??? example "Navigation button pattern"
    ```php
    <?php

    // Build base link array for consistent URL generation
    $baseLink = [
        'page'   => 'device',
        'device' => $device['device_id'],
        'tab'    => 'apps',
        'app'    => 'appname',
    ];

    // Parse URL format to determine current view
    $vars['format'] ??= 'list_overview';
    [$format, $subformat] = explode('_', basename($vars['format']), 2);

    print_optionbar_start();

    // Overview link - highlighted when format is 'list'
    $overviewLabel = ($format == 'list')
        ? '<span class="pagemenu-selected">Overview</span>'
        : 'Overview';
    echo '<a href="' . \LibreNMS\Util\Url::generate($baseLink, ['format' => 'list_overview']) . '">' . $overviewLabel . '</a>';
    echo ' | ';

    // Build list of view types with links
    $viewTypes = [
        'graph_size'    => 'Size',
        'graph_count'   => 'Count',
        'graph_status'  => 'Status',
    ];

    $links = [];
    foreach ($viewTypes as $viewKey => $viewLabel) {
        $label = ($subformat === str_replace('graph_', '', $viewKey))
            ? '<span class="pagemenu-selected">' . $viewLabel . '</span>'
            : $viewLabel;
        $links[] = '<a href="' . \LibreNMS\Util\Url::generate($baseLink, ['format' => $viewKey]) . '">' . $label . '</a>';
    }
    echo 'View Types: ' . implode(' | ', $links);

    print_optionbar_end();
    ```

Key points:
- Use `$baseLink` array for consistent URL generation
- Parse `$vars['format']` to determine current view (format_subformat pattern)
- Wrap active item label in `<span class="pagemenu-selected">`
- Use `\LibreNMS\Util\Url::generate()` or `generate_link()` for URL generation
- Separate items with ` | ` (pipe + space)
