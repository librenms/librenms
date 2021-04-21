<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$pagetitle[] = 'Devices';

if (! isset($vars['format'])) {
    $vars['format'] = 'list_detail';
}

$listoptions = '<span class="devices-font-bold">Lists: </span>';

$menu_options = ['basic' => 'Basic', 'detail' => 'Detail'];

$sep = '';
foreach ($menu_options as $option => $text) {
    $listoptions .= $sep;
    if ($vars['format'] == 'list_' . $option) {
        $listoptions .= '<span class="pagemenu-selected">';
    }
    $listoptions .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['format' => 'list_' . $option]) . '">' . $text . '</a>';
    if ($vars['format'] == 'list_' . $option) {
        $listoptions .= '</span>';
    }
    $sep = ' | ';
}

$listoptions .= '&nbsp;&nbsp;&nbsp;<span class="devices-font-bold">Graphs: </span>';

$menu_options = ['bits' => 'Bits',
    'processor' => 'CPU',
    'ucd_load' => 'Load',
    'mempool' => 'Memory',
    'uptime' => 'Uptime',
    'storage' => 'Storage',
    'diskio' => 'Disk I/O',
    'poller_perf' => 'Poller',
    'ping_perf' => 'Ping',
    'temperature' => 'Temperature',
];
$sep = '';
foreach ($menu_options as $option => $text) {
    $listoptions .= $sep;
    if ($vars['format'] == 'graph_' . $option) {
        $listoptions .= '<span class="pagemenu-selected">';
    }
    $listoptions .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['format' => 'graph_' . $option, 'from' => '-24hour', 'to' => 'now']) . '">' . $text . '</a>';
    if ($vars['format'] == 'graph_' . $option) {
        $listoptions .= '</span>';
    }
    $sep = ' | ';
}

$headeroptions = '<select name="type" id="type" onchange="window.open(this.options[this.selectedIndex].value,\'_top\')" class="devices-graphs-select">';
$type = 'device';
foreach (get_graph_subtypes($type) as $avail_type) {
    $display_type = \LibreNMS\Util\StringHelpers::niceCase($avail_type);
    if ('graph_' . $avail_type == $vars['format']) {
        $is_selected = 'selected';
    } else {
        $is_selected = '';
    }
    $headeroptions .= '<option value="' .
        \LibreNMS\Util\Url::generate($vars, [
            'format' => 'graph_' . $avail_type,
            'from' => $vars['from'] ?: \LibreNMS\Config::get('time.day'),
            'to' => $vars['to'] ?: \LibreNMS\Config::get('time.now'),
        ]) . '" ' . $is_selected . '>' . $display_type . '</option>';
}
$headeroptions .= '</select>';

if (isset($vars['searchbar']) && $vars['searchbar'] == 'hide') {
    $headeroptions .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['searchbar' => '']) . '">Restore Search</a>';
} else {
    $headeroptions .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['searchbar' => 'hide']) . '">Remove Search</a>';
}

$headeroptions .= ' | ';

if (isset($vars['bare']) && $vars['bare'] == 'yes') {
    $headeroptions .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['bare' => '']) . '">Restore Header</a>';
} else {
    $headeroptions .= '<a href="' . \LibreNMS\Util\Url::generate($vars, ['bare' => 'yes']) . '">Remove Header</a>';
}

[$format, $subformat] = explode('_', $vars['format'], 2);
$detailed = $subformat == 'detail';
$no_refresh = $format == 'list';

if ($format == 'graph') {
    if (empty($vars['from'])) {
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
    } else {
        $graph_array['from'] = $vars['from'];
    }
    if (empty($vars['to'])) {
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
    } else {
        $graph_array['to'] = $vars['to'];
    }

    echo '<div class="panel panel-default panel-condensed">';
    echo '<div class="panel-heading">';
    echo '<div class="row" style="padding: 0px 10px 0px 10px;">';
    echo '<div class="pull-left">' . $listoptions . '</div>';
    echo '<div class="pull-right">' . $headeroptions . '</div>';
    echo '<div class="col-md-12" style="padding: 10px 0px 0px 0px;">';
    include_once 'includes/html/print-date-selector.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="panel-body">';

    $sql_param = [];

    if (isset($vars['state'])) {
        if ($vars['state'] == 'up') {
            $state = '1';
        } elseif ($vars['state'] == 'down') {
            $state = '0';
        }
    }

    if (! empty($vars['searchquery'])) {
        $where .= ' AND (sysName LIKE ? OR hostname LIKE ? OR hardware LIKE ? OR os LIKE ? OR location LIKE ?)';
        $sql_param += array_fill(count($param), 5, '%' . $vars['searchquery'] . '%');
    }
    if (! empty($vars['os'])) {
        $where .= ' AND os = ?';
        $sql_param[] = $vars['os'];
    }
    if (! empty($vars['version'])) {
        $where .= ' AND version = ?';
        $sql_param[] = $vars['version'];
    }
    if (! empty($vars['hardware'])) {
        $where .= ' AND hardware = ?';
        $sql_param[] = $vars['hardware'];
    }
    if (! empty($vars['features'])) {
        $where .= ' AND features = ?';
        $sql_param[] = $vars['features'];
    }

    if (! empty($vars['type'])) {
        if ($vars['type'] == 'generic') {
            $where .= " AND ( type = ? OR type = '')";
            $sql_param[] = $vars['type'];
        } else {
            $where .= ' AND type = ?';
            $sql_param[] = $vars['type'];
        }
    }
    if (! empty($vars['state'])) {
        $where .= ' AND status= ?';
        $sql_param[] = $state;
        $where .= " AND disabled='0' AND `disable_notify`='0'";
        $sql_param[] = '';
    }
    if (! empty($vars['disabled'])) {
        $where .= ' AND disabled= ?';
        $sql_param[] = $vars['disabled'];
    }
    if (! empty($vars['ignore'])) {
        $where .= ' AND `ignore`= ?';
        $sql_param[] = $vars['ignore'];
    }
    if (! empty($vars['disable_notify'])) {
        $where .= ' AND `disable_notify`= ?';
        $sql_param[] = $vars['disable_notify'];
    }
    if (! empty($vars['location']) && $vars['location'] == 'Unset') {
        $location_filter = '';
    }
    if (! empty($vars['location'])) {
        $location_filter = $vars['location'];
    }
    if (isset($vars['poller_group'])) {
        $where .= ' AND `poller_group`= ?';
        $sql_param[] = $vars['poller_group'];
    }
    if (! empty($vars['group'])) {
        $where .= ' AND ( ';
        foreach (DB::table('device_group_device')->where('device_group_id', $vars['group'])->pluck('device_id') as $dev) {
            $where .= 'device_id = ? OR ';
            $sql_param[] = $dev;
        }
        $where = substr($where, 0, strlen($where) - 3);
        $where .= ' )';
    }

    $query = 'SELECT * FROM `devices` LEFT JOIN `locations` ON `devices`.`location_id` = `locations`.`id` WHERE 1';

    if (isset($where)) {
        $query .= $where;
    }

    $query .= ' ORDER BY hostname';

    $row = 1;
    foreach (dbFetchRows($query, $sql_param) as $device) {
        if (is_integer($row / 2)) {
            $row_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $row_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        if (device_permitted($device['device_id'])) {
            if (! $location_filter || $device['location'] == $location_filter) {
                $graph_type = 'device_' . $subformat;

                if (session('widescreen')) {
                    $width = 270;
                } else {
                    $width = 315;
                }

                $graph_array_new = [];
                $graph_array_new['type'] = $graph_type;
                $graph_array_new['device'] = $device['device_id'];
                $graph_array_new['height'] = '110';
                $graph_array_new['width'] = $width;
                $graph_array_new['legend'] = 'no';
                $graph_array_new['title'] = 'yes';
                $graph_array_new['from'] = $graph_array['from'];
                $graph_array_new['to'] = $graph_array['to'];

                $graph_array_zoom = $graph_array_new;
                $graph_array_zoom['height'] = '150';
                $graph_array_zoom['width'] = '400';
                $graph_array_zoom['legend'] = 'yes';

                $link_array = $graph_array;
                $link_array['page'] = 'graphs';
                $link_array['type'] = $graph_type;
                $link_array['device'] = $device['device_id'];
                unset($link_array['height'], $link_array['width']);
                $overlib_link = \LibreNMS\Util\Url::generate($link_array);

                echo '<div class="devices-overlib-box" style="min-width:' . ($width + 90) . '; max-width: ' . ($width + 90) . '">';
                echo '<div class="panel panel-default">';
                echo \LibreNMS\Util\Url::overlibLink($overlib_link, \LibreNMS\Util\Url::lazyGraphTag($graph_array_new), \LibreNMS\Util\Url::graphTag($graph_array_zoom));
                echo "</div></div>\n\n";
            }
        }
    }
    echo '</div>';
} else {
    $state = isset($vars['state']) ? $vars['state'] : '';
    $state_selection = "<select name='state' id='state' class='form-control'><option value=''>All</option>" .
        "<option value='up'" . ($state == 'up' ? ' selected' : '') . '>Up</option>' .
        "<option value='down'" . ($state == 'down' ? ' selected' : '') . '>Down</option><select>';

    $features_selected = isset($vars['features']) ? json_encode(['id' => $vars['features'], 'text' => $vars['features']]) : '""';
    $hardware_selected = isset($vars['hardware']) ? json_encode(['id' => $vars['hardware'], 'text' => $vars['hardware']]) : '""';
    $os_selected = isset($vars['os']) ? json_encode(['id' => $vars['os'], 'text' => $vars['hardware']]) : '""';
    $type_selected = isset($vars['type']) ? json_encode(['id' => $vars['type'], 'text' => ucfirst($vars['type'])]) : '""';
    $version_selected = isset($vars['version']) ? json_encode(['id' => $vars['version'], 'text' => $vars['version']]) : '""';

    $os_selected = '""';
    if (isset($vars['os'])) {
        $os_selected = json_encode(['id' => $vars['os'], 'text' => \LibreNMS\Config::getOsSetting($vars['os'], 'text', $vars['os'])]);
    }

    $location_selected = '""';
    if (isset($vars['location'])) {
        $location_text = $vars['location'];
        if (is_numeric($vars['location'])) {
            $location_text = \App\Models\Location::where('id', $vars['location'])->value('location') ?: $vars['location'];
        }
        $location_selected = json_encode(['id' => $vars['location'], 'text' => $location_text]);
    } ?>
    <div class="panel panel-default panel-condensed">
    <div class="panel-heading">
        <div class="row" style="padding: 0px 10px 0px 10px;">
            <div class="pull-left"><?php echo $listoptions; ?></div>
            <div class="pull-right"><?php echo $headeroptions; ?></div>
        </div>
    </div>
    <div class="table-responsive">
        <table id="devices" class="table table-hover table-condensed table-striped">
            <thead>
                <tr>
                    <th data-column-id="status" data-formatter="status" data-width="7px" data-searchable="false"><?php echo $detailed ? 'S.' : 'Status'; ?></th>
                    <th data-column-id="device_id" data-width="5px" data-visible="<?php echo $detailed ? 'true' : 'false'; ?>">Id</th>
                    <th data-column-id="maintenance" data-width="5px" data-searchable="false" data-formatter="maintenance" data-visible="<?php echo $detailed ? 'true' : 'false'; ?>"><?php echo $detailed ? 'M.' : 'Maintenance'; ?></th>
                    <th data-column-id="icon" data-width="70px" data-searchable="false" data-formatter="icon" data-visible="<?php echo $detailed ? 'true' : 'false'; ?>">Vendor</th>
                    <th data-column-id="hostname" data-order="asc" <?php echo $detailed ? 'data-formatter="device"' : ''; ?>>Device</th>
                    <th data-column-id="metrics" data-width="<?php echo $detailed ? '100px' : '150px'; ?>" data-sortable="false" data-searchable="false" data-visible="<?php echo $detailed ? 'true' : 'false'; ?>">Metrics</th>
                    <th data-column-id="hardware">Platform</th>
                    <th data-column-id="os">Operating System</th>
                    <th data-column-id="uptime" data-formatter="uptime">Up/Down Time</th>
                    <th data-column-id="location" data-visible="<?php echo $detailed ? 'true' : 'false'; ?>">Location</th>
                    <th data-column-id="actions" data-width="<?php echo $detailed ? '90px' : '200px'; ?>" data-sortable="false" data-searchable="false" data-header-css-class="device-table-header-actions">Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    </div>
    <script>
        var grid = $("#devices").bootgrid({
            ajax: true,
            rowCount: [50, 100, 250, -1],
            columnSelection: true,
            formatters: {
                "status": function (column, row) {
                    return "<span title=\"Status: " + row.status + " : " + row.extra.replace(/^label-/,'') + "\" class=\"<?php echo $detailed ? 'alert-status' : 'alert-status-small' ?> " + row.extra + "\"></span>";
                },
                "icon": function (column, row) {
                    return "<span class=\"device-table-icon\">" + row.icon + "</span>";
                },
                "maintenance": function (column, row) {
                    if (row.maintenance) {
                        return "<span title=\"Scheduled Maintenance\" class=\"glyphicon glyphicon-wrench\"></span>";
                    }
                    return '';
                },
                "device": function (column, row) {
                    return "<span>" + row.hostname + "</span>";
                },
                "uptime": function (column, row) {
                    if (row.status == 'down') {
                        return "<span class='red'>" + row.uptime + "</span>"
                    } else if(row.status == 'disabled') {
                        return '';
                    } else {
                        return row.uptime;
                    }
                },
            },
            templates: {
                header: "<div class=\"devices-headers-table-menu\" style=\"padding:6px 6px 0px 0px;\"><p class=\"{{css.actions}}\"></p></div><div class=\"row\"></div>"
            },
            post: function () {
                return {
                    format: ' <?php echo $vars['format']; ?>',
                    searchPhrase: '<?php echo htmlspecialchars($vars['searchquery']); ?>',
                    os: '<?php echo $vars['os']; ?>',
                    version: '<?php echo $vars['version']; ?>',
                    hardware: '<?php echo $vars['hardware']; ?>',
                    features: '<?php echo $vars['features']; ?>',
                    location: '<?php echo $vars['location']; ?>',
                    type: '<?php echo $vars['type']; ?>',
                    state: '<?php echo $vars['state']; ?>',
                    disabled: '<?php echo $vars['disabled']; ?>',
                    ignore: '<?php echo $vars['ignore']; ?>',
                    disable_notify: '<?php echo $vars['disable_notify']; ?>',
                    group: '<?php echo $vars['group']; ?>',
                    poller_group: '<?php echo $vars['poller_group']; ?>',
                    device_id: '<?php echo $vars['device_id']; ?>',
                };
            },
            url: "<?php echo url('/ajax/table/device') ?>"
        });

        <?php
        if (! isset($vars['searchbar']) && $vars['searchbar'] != 'hide') {
            ?>
        $(".devices-headers-table-menu").append(
            "<div class='pull-left'>" +
            "<form method='post' action='' class='form-inline devices-search-header' role='form'>" +
            "<?php echo addslashes(csrf_field()) ?>"+
            "<div class='form-group'>" +
            "<input type='text' name='searchquery' id='searchquery' value=''<?php echo $vars['searchquery']; ?>'' class='form-control' placeholder='Search'>" +
            "</div>" +
            "<div class='form-group'><?php echo $state_selection ?></div>" +
            "<div class='form-group'><select name='os' id='os' class='form-control'></select></div>" +
            "<div class='form-group'><select name='version' id='version' class='form-control'></select></div>" +
            "<div class='form-group'><select name='hardware' id='hardware' class='form-control'></select></div>" +
            "<div class='form-group'><select name='features' id='features' class='form-control'></select></div>" +
            "<div class='form-group'><select name='location' id='location' class='form-control'></select></div>" +
            "<div class='form-group'><select name='type' id='device-type' class='form-control'></select></div>" +
            "<input type='submit' class='btn btn-info' value='Search'>" +
            "<a href='<?php echo \LibreNMS\Util\Url::generate(array_diff_key($vars, ['_token' => 1])) ?>' title='Update the browser URL to reflect the search criteria.' class='btn btn-default'>Update URL</a>" +
            "<a href='<?php echo \LibreNMS\Util\Url::generate(['page' => 'devices', 'section' => $vars['section'], 'bare' => $vars['bare']]) ?>' title='Reset criteria to default.' class='btn btn-default'>Reset</a>" +
            "</form>" +
            "</div>"
        );
            <?php
        } ?>

        init_select2("#features", "device-field", {field: 'features'}, <?php echo $features_selected ?>, 'All Featuresets');
        init_select2("#hardware", "device-field", {field: 'hardware'}, <?php echo $hardware_selected ?>, 'All Platforms');
        init_select2("#os", "device-field", {field: 'os'}, <?php echo $os_selected ?>, 'All OS');
        init_select2("#device-type", "device-field", {field: 'type'}, <?php echo $type_selected ?>, 'All Device Types');
        init_select2("#version", "device-field", {field: 'version'}, <?php echo $version_selected ?>, 'All Versions');
        init_select2("#location", "location", {}, <?php echo $location_selected ?>, 'All Locations');
    </script>
    <?php
}
