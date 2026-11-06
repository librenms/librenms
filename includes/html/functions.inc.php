<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @author     LibreNMS Contributors <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Group
 */

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Bill;
use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use LibreNMS\Enum\ImageFormat;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

function generate_link($text, $vars, $new_vars = [])
{
    return '<a href="' . Url::generate($vars, $new_vars) . '">' . $text . '</a>';
}//end generate_link()

function escape_quotes($text)
{
    return str_replace('"', "\'", str_replace("'", "\'", $text));
}//end escape_quotes()

function generate_overlib_content($graph_array, $text)
{
    $overlib_content = '<div class=overlib><span class=overlib-text>' . htmlspecialchars((string) $text) . '</span><br />';
    foreach (['day', 'week', 'month', 'year'] as $period) {
        $graph_array['from'] = LibrenmsConfig::get("time.$period");
        $overlib_content .= escape_quotes(Url::graphTag($graph_array));
    }

    $overlib_content .= '</div>';

    return $overlib_content;
}//end generate_overlib_content()

function generate_device_link($device, $text = null, $vars = [], $start = 0, $end = 0, $escape_text = 1, $overlib = 1)
{
    $deviceModel = DeviceCache::get((int) ($device['device_id'] ?? 0));

    if (! $escape_text) {
        $text = new HtmlString($text);
    }

    return Url::deviceLink($deviceModel, $text, $vars, $start, $end, $overlib);
}

function bill_permitted($bill_id)
{
    if (Gate::allows('viewAll', Bill::class)) {
        return true;
    }

    return Permissions::canAccessBill($bill_id, Auth::id());
}

function port_permitted($port_id, $device_id = null)
{
    if (Gate::allows('viewAll', Port::class)) {
        return true;
    }

    if (! is_numeric($device_id)) {
        $device_id = PortCache::get((int) $port_id)?->device_id;
    }

    if (device_permitted($device_id)) {
        return true;
    }

    return Permissions::canAccessPort($port_id, Auth::id());
}

function device_permitted($device_id)
{
    if (Gate::allows('viewAll', Device::class)) {
        return true;
    }

    return Permissions::canAccessDevice($device_id, Auth::id());
}

function alert_layout($severity)
{
    switch ($severity) {
        case 'critical':
            $icon = 'exclamation';
            $color = 'danger';
            $background = 'danger';
            break;
        case 'warning':
            $icon = 'warning';
            $color = 'warning';
            $background = 'warning';
            break;
        case 'ok':
            $icon = 'check';
            $color = 'success';
            $background = 'success';
            break;
        default:
            $icon = 'info';
            $color = 'info';
            $background = 'info';
    }

    return ['icon' => $icon,
        'icon_color' => $color,
        'background_color' => $background, ];
}

function generate_port_link($port, $text = null, $type = null, $overlib = 1, $single_graph = 0)
{
    if (is_null($port)) {
        return (string) $text;
    }
    $graph_array = [];

    if (! $text) {
        $text = Rewrite::normalizeIfName($port['label'] ?? $port['ifName']);
    }

    if ($type) {
        $port['graph_type'] = $type;
    }

    $port['graph_type'] ??= 'port_bits';

    $class = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);

    if (! isset($port['hostname'])) {
        $port = array_merge($port, device_by_id_cache($port['device_id']));
    }

    if (! isset($port['label'])) {
        $port = cleanPort($port);
    }

    $content = '<div class="overlib-text">' . ($port['hostname'] ?? '') . ' - ' . Rewrite::normalizeIfName(addslashes(LibreNMS\Util\Clean::html($port['label'], []))) . '</div>';
    $content .= addslashes(LibreNMS\Util\Clean::html($port['ifAlias'], [])) . '<br />';

    $content .= "<div style=\'width: 850px\'>";
    $graph_array['type'] = $port['graph_type'];
    $graph_array['legend'] = 'yes';
    $graph_array['height'] = '100';
    $graph_array['width'] = '340';
    $graph_array['to'] = LibrenmsConfig::get('time.now');
    $graph_array['from'] = LibrenmsConfig::get('time.day');
    $graph_array['id'] = $port['port_id'];
    $content .= Url::graphTag($graph_array);
    if ($single_graph == 0) {
        $graph_array['from'] = LibrenmsConfig::get('time.week');
        $content .= Url::graphTag($graph_array);
        $graph_array['from'] = LibrenmsConfig::get('time.month');
        $content .= Url::graphTag($graph_array);
        $graph_array['from'] = LibrenmsConfig::get('time.year');
        $content .= Url::graphTag($graph_array);
    }

    $content .= '</div>';

    $url = generate_port_url($port);

    if ($overlib == 0) {
        return $content;
    } elseif (port_permitted($port['port_id'], $port['device_id'])) {
        return Url::overlibLink($url, $text, $content, $class);
    } else {
        return Rewrite::normalizeIfName($text);
    }
}//end generate_port_link()

function generate_port_url($port, $vars = [])
{
    return Url::generate(['page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['port_id']], $vars);
}//end generate_port_url()

/**
 * Create image to output text instead of a graph.
 *
 * @param  string  $text
 * @param  int[]  $color
 */
function graph_error($text, $short = null, $color = [128, 0, 0])
{
    header('Content-Type: ' . ImageFormat::forGraph()->contentType());
    echo LibreNMS\Util\Graph::error($text, $short, 300, null, $color);
}

function print_optionbar_start($height = 0, $width = 0, $marginbottom = 5)
{
    echo '
        <div class="panel panel-default">
        <div class="panel-heading">
        ';
}//end print_optionbar_start()

function print_optionbar_end()
{
    echo '
        </div>
        </div>
        ';
}//end print_optionbar_end()

function generate_pagination($count, $limit, $page, $links = 2)
{
    $end_page = ceil($count / $limit);
    $start = (($page - $links) > 0) ? ($page - $links) : 1;
    $end = (($page + $links) < $end_page) ? ($page + $links) : $end_page;
    $return = '<ul class="pagination">';
    $link_class = ($page == 1) ? 'disabled' : '';
    $return .= "<li><a href='' onClick='changePage(1,event);'>&laquo;</a></li>";
    $return .= "<li class='$link_class'><a href='' onClick='changePage($page - 1,event);'>&lt;</a></li>";

    if ($start > 1) {
        $return .= "<li><a href='' onClick='changePage(1,event);'>1</a></li>";
        $return .= "<li class='disabled'><span>...</span></li>";
    }

    for ($x = $start; $x <= $end; $x++) {
        $link_class = ($page == $x) ? 'active' : '';
        $return .= "<li class='$link_class'><a href='' onClick='changePage($x,event);'>$x </a></li>";
    }

    if ($end < $end_page) {
        $return .= "<li class='disabled'><span>...</span></li>";
        $return .= "<li><a href='' onClick='changePage($end_page,event);'>$end_page</a></li>";
    }

    $link_class = ($page == $end_page) ? 'disabled' : '';
    $return .= "<li class='$link_class'><a href='' onClick='changePage($page + 1,event);'>&gt;</a></li>";
    $return .= "<li class='$link_class'><a href='' onClick='changePage($end_page,event);'>&raquo;</a></li>";
    $return .= '</ul>';

    return $return;
}//end generate_pagination()

function get_url()
{
    // http://stackoverflow.com/questions/2820723/how-to-get-base-url-with-php
    // http://stackoverflow.com/users/184600/ma%C4%8Dek
    return sprintf(
        '%s://%s%s',
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
    );
}//end get_url()

/**
 * Return the rows from 'ports' for all ports of a certain type as parsed by port_descr_parser.
 * One or an array of strings can be provided as an argument; if an array is passed, all ports matching
 * any of the types in the array are returned.
 *
 * @param  $types  mixed String or strings matching 'port_descr_type's.
 * @return array Rows from the ports table for matching ports.
 */
function get_ports_from_type($given_types)
{
    // Make the arg an array if it isn't, so subsequent steps only have to handle arrays.
    if (! is_array($given_types)) {
        $given_types = [$given_types];
    }

    // Check the config for a '_descr' entry for each argument. This is how a 'custom_descr' entry can
    //  be key/valued to some other string that's actually searched for in the DB. Merge or append the
    //  configured value if it's an array or a string. Or append the argument itself if there's no matching
    //  entry in config.
    $search_types = [];
    foreach ($given_types as $type) {
        if (LibrenmsConfig::has($type . '_descr')) {
            $type_descr = LibrenmsConfig::get($type . '_descr');
            if (is_array($type_descr)) {
                $search_types = array_merge($search_types, $type_descr);
            } else {
                $search_types[] = $type_descr;
            }
        } else {
            $search_types[] = $type;
        }
    }

    // Using the full list of strings to search the DB for, build the 'where' portion of a query that
    //  compares 'port_descr_type' with entry in the list. Also, since '@' is the convential wildcard,
    //  replace it with '%' so it functions as a wildcard in the SQL query.
    $type_where = ' (';
    $or = '';
    $type_param = [];

    foreach ($search_types as $type) {
        if (! empty($type)) {
            $type = strtr($type, '@', '%');
            $type_where .= " $or `port_descr_type` LIKE ?";
            $or = 'OR';
            $type_param[] = $type;
        }
    }
    $type_where .= ') ';

    // Run the query with the generated 'where' and necessary parameters, and send it back.
    $ports = dbFetchRows("SELECT * FROM `ports` as I, `devices` AS D WHERE $type_where AND I.device_id = D.device_id ORDER BY I.ifAlias", $type_param);

    return $ports;
}

function get_rules_from_json()
{
    return json_decode(file_get_contents(resource_path('definitions/alert_rules.json')), true);
}

function search_oxidized_config($search_in_conf_textbox)
{
    if (Gate::denies('oxidized.search')) {
        return false;
    }

    $oxidized_search_url = LibrenmsConfig::get('oxidized.url') . '/nodes/conf_search?format=json';
    $postdata = http_build_query(
        [
            'search_in_conf_textbox' => $search_in_conf_textbox,
        ]
    );
    $opts = ['http' => [
        'method' => 'POST',
        'header' => 'Content-type: application/x-www-form-urlencoded',
        'content' => $postdata,
    ],
    ];
    $context = stream_context_create($opts);

    $nodes = json_decode(file_get_contents($oxidized_search_url, false, $context), true);
    // Look up Oxidized node names to LibreNMS devices for a link
    foreach ($nodes as &$n) {
        $dev = DeviceCache::getByHostname($n['node']);
        $n['dev_id'] = $dev ? $dev->device_id : false;
        $n['full_name'] = $dev ? $dev->display : $n['full_name'];
    }

    return $nodes;
}

/**
 * @param  int  $eventlog_severity
 * @return string $eventlog_severity_icon
 */
function eventlog_severity($eventlog_severity)
{
    return match ($eventlog_severity) {
        1 => 'label-success',
        2 => 'label-info',
        3 => 'label-primary',
        4 => 'label-warning',
        5 => 'label-danger',
        default => 'label-default',
    };
} // end eventlog_severity

function get_oxidized_nodes_list()
{
    $context = stream_context_create([
        'http' => [
            'header' => 'Accept: application/json',
        ],
    ]);

    $data = json_decode(file_get_contents(LibrenmsConfig::get('oxidized.url') . '/nodes?format=json', false, $context), true);

    foreach ($data as $object) {
        $device = DeviceCache::getByHostname($object['name']);
        if (! device_permitted($device->device_id)) {
            //user cannot see this device, so let's skip it.
            continue;
        }
        try {
            // Convert UTC time string to local timezone set
            $utc_time = $object['time'];
            $utc_date = new DateTime($utc_time, new DateTimeZone('UTC'));
            $local_timezone = new DateTimeZone(date_default_timezone_get());
            $local_date = $utc_date->setTimezone($local_timezone);

            // Generate local time string
            $formatted_local_time = $local_date->format('Y-m-d H:i:s T');
        } catch (Exception) {
            // Just display the current value of $object['time'];
            $formatted_local_time = $object['time'];
        }
        echo '<tr>
        <td>' . e($device->device_id) . '</td>
        <td>' . e($object['name']) . '</td>
        <td>' . e($device->sysName) . '</td>
        <td>' . e($object['status']) . '</td>
        <td>' . e($formatted_local_time) . '</td>
        <td>' . e($object['model']) . '</td>
        <td>' . e($object['group']) . '</td>
        <td></td>
        </tr>';
    }
}

/**
 * Return stacked graphs information
 *
 * @param  string  $transparency  value of desired transparency applied to rrdtool options (values 01 - 99)
 * @return array containing transparency and stacked setup
 */
function generate_stacked_graphs($force_stack = false, $transparency = '88')
{
    if (LibrenmsConfig::get('webui.graph_stacked') == true || $force_stack == true) {
        return ['transparency' => $transparency, 'stacked' => '1'];
    } else {
        return ['transparency' => '', 'stacked' => '-1'];
    }
}

/**
 * @params int unix time
 * @params int seconds
 *
 * @return int
 *
 * Rounds down to the nearest interval.
 *
 * The first argument is required and it is the unix time being
 * rounded down.
 *
 * The second value is the time interval. If not specified, it
 * defaults to 300, or 5 minutes.
 */
function lowest_time($time, $seconds = 300)
{
    return $time - ($time % $seconds);
}

/**
 * @params int
 *
 * @return string|void
 *
 * This returns the subpath for working with nfdump.
 *
 * 1 value is taken and that is a unix time stamp. It will be then be rounded
 * off to the lowest five minutes earlier.
 *
 * The return string will be a path partial you can use with nfdump to tell it what
 * file or range of files to use.
 *
 * Below ie a explanation of the layouts as taken from the NfSen config file.
 *  0             no hierachy levels - flat layout - compatible with pre NfSen version
 *  1 %Y/%m/%d    year/month/day
 *  2 %Y/%m/%d/%H year/month/day/hour
 *  3 %Y/%W/%u    year/week_of_year/day_of_week
 *  4 %Y/%W/%u/%H year/week_of_year/day_of_week/hour
 *  5 %Y/%j       year/day-of-year
 *  6 %Y/%j/%H    year/day-of-year/hour
 *  7 %Y-%m-%d    year-month-day
 *  8 %Y-%m-%d/%H year-month-day/hour
 */
function time_to_nfsen_subpath($time)
{
    $time = lowest_time($time);
    $layout = LibrenmsConfig::get('nfsen_subdirlayout');

    if ($layout == 0) {
        return 'nfcapd.' . date('YmdHi', $time);
    } elseif ($layout == 1) {
        return date('Y\/m\/d\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 2) {
        return date('Y\/m\/d\/H\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 3) {
        return date('Y\/W\/w\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 4) {
        return date('Y\/W\/w\/H\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 5) {
        return date('Y\/z\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 6) {
        return date('Y\/z\/H\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 7) {
        return date('Y\-m\-d\/\n\f\c\a\p\d\.YmdHi', $time);
    } elseif ($layout == 8) {
        return date('Y\-m\-d\/H\/\n\f\c\a\p\d\.YmdHi', $time);
    }
}

/**
 * @params string hostname
 *
 * @return string
 *
 * Takes a hostname and transforms it to the name
 * used by nfsen.
 */
function nfsen_hostname($hostname)
{
    $nfsen_hostname = str_replace('.', LibrenmsConfig::get('nfsen_split_char'), $hostname);

    if (! is_null(LibrenmsConfig::get('nfsen_suffix'))) {
        $nfsen_hostname = str_replace(LibrenmsConfig::get('nfsen_suffix'), '', $nfsen_hostname);
    }

    return $nfsen_hostname;
}

/**
 * @params string hostname
 *
 * @return string|void
 *
 * Takes a hostname and returns the path to the nfsen
 * live dir.
 */
function nfsen_live_dir($hostname)
{
    $hostname = nfsen_hostname($hostname);

    foreach (LibrenmsConfig::get('nfsen_base') as $base_dir) {
        if (file_exists($base_dir) && is_dir($base_dir)) {
            return $base_dir . '/profiles-data/live/' . $hostname;
        }
    }
}
