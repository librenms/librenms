<?php

use LibreNMS\Config;

unset($vars['page']);

// Setup here

if (session('widescreen')) {
    $graph_width = 1700;
    $thumb_width = 180;
} else {
    $graph_width = 1075;
    $thumb_width = 113;
}

$vars['from'] = parse_at_time($vars['from']) ?: Config::get('time.day');
$vars['to'] = parse_at_time($vars['to']) ?: Config::get('time.now');

preg_match('/^(?P<type>[A-Za-z0-9]+)_(?P<subtype>.+)/', $vars['type'], $graphtype);

$type = basename($graphtype['type']);
$subtype = basename($graphtype['subtype']);
$id = $vars['id'];

if (is_numeric($vars['device'])) {
    $device = device_by_id_cache($vars['device']);
} elseif (! empty($vars['device'])) {
    $device = device_by_name($vars['device']);
}

if (is_file('includes/html/graphs/' . $type . '/auth.inc.php')) {
    require 'includes/html/graphs/' . $type . '/auth.inc.php';
}

if (! $auth) {
    require 'includes/html/error-no-perm.inc.php';
} else {
    if (Config::has("graph_types.$type.$subtype.descr")) {
        $title .= ' :: ' . Config::get("graph_types.$type.$subtype.descr");
    } elseif ($type == 'device' && $subtype == 'collectd') {
        $title .= ' :: ' . \LibreNMS\Util\StringHelpers::niceCase($subtype) . ' :: ' . $vars['c_plugin'];
        if (isset($vars['c_plugin_instance'])) {
            $title .= ' - ' . $vars['c_plugin_instance'];
        }
        $title .= ' - ' . $vars['c_type'];
        if (isset($vars['c_type_instance'])) {
            $title .= ' - ' . $vars['c_type_instance'];
        }
    } else {
        $title .= ' :: ' . \LibreNMS\Util\StringHelpers::niceCase($subtype);
    }

    $graph_array = $vars;
    $graph_array['height'] = '60';
    $graph_array['width'] = $thumb_width;
    $graph_array['legend'] = 'no';
    $graph_array['to'] = Config::get('time.now');

    print_optionbar_start();
    echo $title;

    // FIXME allow switching between types for sensor and wireless also restrict types to ones that have data
    if ($type != 'sensor') {
        echo '<div style="float: right;"><form action="">';
        echo csrf_field();
        echo "<select name='type' id='type' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\" >";

        foreach (get_graph_subtypes($type, $device) as $avail_type) {
            echo "<option value='" . \LibreNMS\Util\Url::generate($vars, ['type' => $type . '_' . $avail_type, 'page' => 'graphs']) . "'";
            if ($avail_type == $subtype) {
                echo ' selected';
            }
            $display_type = \LibreNMS\Util\StringHelpers::niceCase($avail_type);
            echo ">$display_type</option>";
        }
        echo '</select></form></div>';
    }

    print_optionbar_end();

    $thumb_array = Config::get('graphs.row.normal');

    echo '<table width=100% class="thumbnail_graph_table"><tr>';

    foreach ($thumb_array as $period => $text) {
        $graph_array['from'] = Config::get("time.$period");

        $link_array = $vars;
        $link_array['from'] = $graph_array['from'];
        $link_array['to'] = $graph_array['to'];
        $link_array['page'] = 'graphs';
        $link = \LibreNMS\Util\Url::generate($link_array);

        echo '<td style="text-align: center;">';
        echo '<b>' . $text . '</b>';
        echo '<a href="' . $link . '">';
        echo \LibreNMS\Util\Url::lazyGraphTag($graph_array);
        echo '</a>';
        echo '</td>';
    }

    echo '</tr></table>';

    $graph_array = $vars;
    $graph_array['height'] = Config::get('webui.min_graph_height');
    $graph_array['width'] = $graph_width;

    if ($screen_width = Session::get('screen_width')) {
        if ($screen_width > 800) {
            $graph_array['width'] = ($screen_width - ($screen_width / 10));
        } else {
            $graph_array['width'] = ($screen_width - ($screen_width / 4));
        }
    }

    if ($screen_height = Session::get('screen_height')) {
        if ($screen_height > 960) {
            $graph_array['height'] = ($screen_height - ($screen_height / 2));
        } else {
            $graph_array['height'] = max($graph_array['height'], ($screen_height - ($screen_height / 1.5)));
        }
    }

    echo '<hr />';

    include_once 'includes/html/print-date-selector.inc.php';

    echo '<div style="padding-top: 5px";></div>';
    echo '<center>';
    if ($vars['legend'] == 'no') {
        echo generate_link('Show Legend', $vars, ['page' => 'graphs', 'legend' => null]);
    } else {
        echo generate_link('Hide Legend', $vars, ['page' => 'graphs', 'legend' => 'no']);
    }

    // FIXME : do this properly
    //  if ($type == "port" && $subtype == "bits")
    //  {
    echo ' | ';
    if ($vars['previous'] == 'yes') {
        echo generate_link('Hide Previous', $vars, ['page' => 'graphs', 'previous' => null]);
    } else {
        echo generate_link('Show Previous', $vars, ['page' => 'graphs', 'previous' => 'yes']);
    }
    //  }

    echo ' | ';
    if ($vars['showcommand'] == 'yes') {
        echo generate_link('Hide RRD Command', $vars, ['page' => 'graphs', 'showcommand' => null]);
    } else {
        echo generate_link('Show RRD Command', $vars, ['page' => 'graphs', 'showcommand' => 'yes']);
    }

    if ($vars['type'] == 'port_bits') {
        echo ' | To show trend, set to future date';
    }

    echo '</center>';

    echo generate_graph_js_state($graph_array);

    echo '<div style="width: ' . $graph_array['width'] . '; margin: auto;"><center>';
    if (Config::get('webui.dynamic_graphs', false) === true) {
        echo generate_dynamic_graph_js($graph_array);
        echo generate_dynamic_graph_tag($graph_array);
    } else {
        echo \LibreNMS\Util\Url::lazyGraphTag($graph_array);
    }
    echo '</center></div>';

    if (Config::has('graph_descr.' . $vars['type'])) {
        print_optionbar_start();
        echo '<div style="float: left; width: 30px;">
            <div style="margin: auto auto;">
            <i class="fa fa-info-circle fa-lg icon-theme" aria-hidden="true"></i>
            </div>
            </div>';
        echo Config::get('graph_descr.' . $vars['type']);
        print_optionbar_end();
    }

    if ($vars['showcommand']) {
        $_GET = $graph_array;
        $command_only = 1;

        require 'includes/html/graphs/graph.inc.php';
    }
}
