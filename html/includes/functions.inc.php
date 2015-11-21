<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @package    librenms
 * @subpackage functions
 * @author     LibreNMS Contributors <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Group
 */

/**
 * Compare $t with the value of $vars[$v], if that exists
 * @param string $v Name of the var to test
 * @param string $t Value to compare $vars[$v] to
 * @return boolean true, if values are the same, false if $vars[$v] is unset or values differ
 */
function var_eq($v, $t) {
    global $vars;
    if (isset($vars[$v]) && $vars[$v] == $t) {
        return true;
    }

    return false;
}

/**
 * Get the value of $vars[$v], if it exists
 * @param string $v Name of the var to get
 * @return string|boolean The value of $vars[$v] if it exists, false if it does not exist
 */
function var_get($v) {
    global $vars;
    if (isset($vars[$v])) {
        return $vars[$v];
    }

    return false;
}


function data_uri($file, $mime) {
    $contents = file_get_contents($file);
    $base64   = base64_encode($contents);
    return ('data:'.$mime.';base64,'.$base64);

}//end data_uri()


function nicecase($item) {
    switch ($item) {
    case 'dbm':
        return 'dBm';

    case 'mysql':
        return ' MySQL';

    case 'powerdns':
        return 'PowerDNS';

    case 'bind':
        return 'BIND';

    default:
        return ucfirst($item);
    }

}//end nicecase()


function toner2colour($descr, $percent) {
    $colour = get_percentage_colours(100 - $percent);

    if (substr($descr, -1) == 'C' || stripos($descr, 'cyan') !== false) {
        $colour['left']  = '55D6D3';
        $colour['right'] = '33B4B1';
    }

    if (substr($descr, -1) == 'M' || stripos($descr, 'magenta') !== false) {
        $colour['left']  = 'F24AC8';
        $colour['right'] = 'D028A6';
    }

    if (substr($descr, -1) == 'Y' || stripos($descr, 'yellow') !== false
        || stripos($descr, 'giallo') !== false
        || stripos($descr, 'gul') !== false
    ) {
        $colour['left']  = 'FFF200';
        $colour['right'] = 'DDD000';
    }

    if (substr($descr, -1) == 'K' || stripos($descr, 'black') !== false
        || stripos($descr, 'nero') !== false
    ) {
        $colour['left']  = '000000';
        $colour['right'] = '222222';
    }

    return $colour;

}//end toner2colour()


function generate_link($text, $vars, $new_vars=array()) {
    return '<a href="'.generate_url($vars, $new_vars).'">'.$text.'</a>';

}//end generate_link()


function generate_url($vars, $new_vars=array()) {
    $vars = array_merge($vars, $new_vars);

    $url = $vars['page'].'/';
    unset($vars['page']);

    foreach ($vars as $var => $value) {
        if ($value == '0' || $value != '' && strstr($var, 'opt') === false && is_numeric($var) === false) {
            $url .= $var.'='.urlencode($value).'/';
        }
    }

    return ($url);

}//end generate_url()


function escape_quotes($text) {
    return str_replace('"', "\'", str_replace("'", "\'", $text));

}//end escape_quotes()


function generate_overlib_content($graph_array, $text) {
    global $config;

    $overlib_content = '<div class=overlib><span class=overlib-text>'.$text.'</span><br />';
    foreach (array('day', 'week', 'month', 'year') as $period) {
        $graph_array['from'] = $config['time'][$period];
        $overlib_content    .= escape_quotes(generate_graph_tag($graph_array));
    }

    $overlib_content .= '</div>';

    return $overlib_content;

}//end generate_overlib_content()


function get_percentage_colours($percentage) {
    $background = array();
    if ($percentage > '90') {
        $background['left']  = 'c4323f';
        $background['right'] = 'C96A73';
    }

    else if ($percentage > '75') {
        $background['left']  = 'bf5d5b';
        $background['right'] = 'd39392';
    }

    else if ($percentage > '50') {
        $background['left']  = 'bf875b';
        $background['right'] = 'd3ae92';
    }

    else if ($percentage > '25') {
        $background['left']  = '5b93bf';
        $background['right'] = '92b7d3';
    }

    else {
        $background['left']  = '9abf5b';
        $background['right'] = 'bbd392';
    }

    return ($background);

}//end get_percentage_colours()


function generate_minigraph_image($device, $start, $end, $type, $legend='no', $width=275, $height=100, $sep='&amp;', $class='minigraph-image',$absolute_size=0) {
    return '<img class="'.$class.'" width="'.$width.'" height="'.$height.'" src="graph.php?'.implode($sep, array('device='.$device['device_id'], "from=$start", "to=$end", "width=$width", "height=$height", "type=$type", "legend=$legend", "absolute=$absolute_size")).'">';

}//end generate_minigraph_image()


function generate_device_url($device, $vars=array()) {
    return generate_url(array('page' => 'device', 'device' => $device['device_id']), $vars);

}//end generate_device_url()


function generate_device_link($device, $text=null, $vars=array(), $start=0, $end=0, $escape_text=1, $overlib=1) {
    global $config;

    if (!$start) {
        $start = $config['time']['day'];
    }

    if (!$end) {
        $end = $config['time']['now'];
    }

    $class = devclass($device);
    if (!$text) {
        $text = $device['hostname'];
    }

    if (isset($config['os'][$device['os']]['over'])) {
        $graphs = $config['os'][$device['os']]['over'];
    }
    else if (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over'])) {
        $graphs = $config['os'][$device['os_group']]['over'];
    }
    else {
        $graphs = $config['os']['default']['over'];
    }

    $url = generate_device_url($device, $vars);

    // beginning of overlib box contains large hostname followed by hardware & OS details
    $contents = '<div><span class="list-large">'.$device['hostname'].'</span>';
    if ($device['hardware']) {
        $contents .= ' - '.$device['hardware'];
    }

    if ($device['os']) {
        $contents .= ' - '.mres($config['os'][$device['os']]['text']);
    }

    if ($device['version']) {
        $contents .= ' '.mres($device['version']);
    }

    if ($device['features']) {
        $contents .= ' ('.mres($device['features']).')';
    }

    if (isset($device['location'])) {
        $contents .= ' - '.htmlentities($device['location']);
    }

    $contents .= '</div>';

    foreach ($graphs as $entry) {
        $graph         = $entry['graph'];
        $graphhead = $entry['text'];
        $contents .= '<div class="overlib-box">';
        $contents .= '<span class="overlib-title">'.$graphhead.'</span><br />';
        $contents .= generate_minigraph_image($device, $start, $end, $graph);
        $contents .= generate_minigraph_image($device, $config['time']['week'], $end, $graph);
        $contents .= '</div>';
    }

    if ($escape_text) {
        $text = htmlentities($text);
    }

    if ($overlib == 0) {
        $link = $contents;
    }
    else {
        $link = overlib_link($url, $text, escape_quotes($contents), $class);
    }

    if (device_permitted($device['device_id'])) {
        return $link;
    }
    else {
        return $device['hostname'];
    }

}//end generate_device_link()


function overlib_link($url, $text, $contents, $class) {
    global $config;

    $contents = "<div style=\'background-color: #FFFFFF;\'>".$contents.'</div>';
    $contents = str_replace('"', "\'", $contents);
    $output   = '<a class="'.$class.'" href="'.$url.'"';
    if ($config['web_mouseover'] === false) {
        $output .= '>';
    }
    else {
        $output .= " onmouseover=\"return overlib('".$contents."'".$config['overlib_defaults'].",WRAP,HAUTO,VAUTO); \" onmouseout=\"return nd();\">";
    }

    $output .= $text.'</a>';

    return $output;

}//end overlib_link()


function generate_graph_popup($graph_array) {
    global $config;

    // Take $graph_array and print day,week,month,year graps in overlib, hovered over graph
    $original_from = $graph_array['from'];

    $graph                 = generate_graph_tag($graph_array);
    $content               = '<div class=list-large>'.$graph_array['popup_title'].'</div>';
    $content              .= "<div style=\'width: 850px\'>";
    $graph_array['legend'] = 'yes';
    $graph_array['height'] = '100';
    $graph_array['width']  = '340';
    $graph_array['from']   = $config['time']['day'];
    $content              .= generate_graph_tag($graph_array);
    $graph_array['from']   = $config['time']['week'];
    $content              .= generate_graph_tag($graph_array);
    $graph_array['from']   = $config['time']['month'];
    $content              .= generate_graph_tag($graph_array);
    $graph_array['from']   = $config['time']['year'];
    $content              .= generate_graph_tag($graph_array);
    $content              .= '</div>';

    $graph_array['from'] = $original_from;

    $graph_array['link'] = generate_url($graph_array, array('page' => 'graphs', 'height' => null, 'width' => null, 'bg' => null));

    // $graph_array['link'] = "graphs/type=" . $graph_array['type'] . "/id=" . $graph_array['id'];
    return overlib_link($graph_array['link'], $graph, $content, null);

}//end generate_graph_popup()


function print_graph_popup($graph_array) {
    echo generate_graph_popup($graph_array);

}//end print_graph_popup()


function permissions_cache($user_id) {
    $permissions = array();
    foreach (dbFetchRows("SELECT * FROM devices_perms WHERE user_id = '".$user_id."'") as $device) {
        $permissions['device'][$device['device_id']] = 1;
    }

    foreach (dbFetchRows("SELECT * FROM ports_perms WHERE user_id = '".$user_id."'") as $port) {
        $permissions['port'][$port['port_id']] = 1;
    }

    foreach (dbFetchRows("SELECT * FROM bill_perms WHERE user_id = '".$user_id."'") as $bill) {
        $permissions['bill'][$bill['bill_id']] = 1;
    }

    return $permissions;

}//end permissions_cache()


function bill_permitted($bill_id) {
    global $permissions;

    if ($_SESSION['userlevel'] >= '5') {
        $allowed = true;
    }
    else if ($permissions['bill'][$bill_id]) {
        $allowed = true;
    }
    else {
        $allowed = false;
    }

    return $allowed;

}//end bill_permitted()


function port_permitted($port_id, $device_id=null) {
    global $permissions;

    if (!is_numeric($device_id)) {
        $device_id = get_device_id_by_port_id($port_id);
    }

    if ($_SESSION['userlevel'] >= '5') {
        $allowed = true;
    }
    else if (device_permitted($device_id)) {
        $allowed = true;
    }
    else if ($permissions['port'][$port_id]) {
        $allowed = true;
    }
    else {
        $allowed = false;
    }

    return $allowed;

}//end port_permitted()


function application_permitted($app_id, $device_id=null) {
    global $permissions;

    if (is_numeric($app_id)) {
        if (!$device_id) {
            $device_id = get_device_id_by_app_id($app_id);
        }

        if ($_SESSION['userlevel'] >= '5') {
            $allowed = true;
        }
        else if (device_permitted($device_id)) {
            $allowed = true;
        }
        else if ($permissions['application'][$app_id]) {
            $allowed = true;
        }
        else {
            $allowed = false;
        }
    }
    else {
        $allowed = false;
    }

    return $allowed;

}//end application_permitted()


function device_permitted($device_id) {
    global $permissions;

    if ($_SESSION['userlevel'] >= '5') {
        $allowed = true;
    }
    else if ($permissions['device'][$device_id]) {
        $allowed = true;
    }
    else {
        $allowed = false;
    }

    return $allowed;

}//end device_permitted()


function print_graph_tag($args) {
    echo generate_graph_tag($args);

}//end print_graph_tag()


function generate_graph_tag($args) {
    $urlargs = array();
    foreach ($args as $key => $arg) {
        $urlargs[] = $key.'='.urlencode($arg);
    }

    return '<img src="graph.php?'.implode('&amp;', $urlargs).'" border="0" />';

}//end generate_graph_tag()

function generate_lazy_graph_tag($args) {
    $urlargs = array();
    $w = 0;
    $h = 0;
    foreach ($args as $key => $arg) {
        switch (strtolower($key)) {
            case 'width':
                $w = $arg;
                break;
            case 'height':
                $h = $arg;
                break;
        }
        $urlargs[] = $key."=".urlencode($arg);
    }

    return '<img class="lazy" width="'.$w.'" height="'.$h.'" data-original="graph.php?' . implode('&amp;',$urlargs).'" border="0" />';

}//end generate_lazy_graph_tag()


function generate_graph_js_state($args) {
    // we are going to assume we know roughly what the graph url looks like here.
    // TODO: Add sensible defaults
    $from   = (is_numeric($args['from']) ? $args['from'] : 0);
    $to     = (is_numeric($args['to']) ? $args['to'] : 0);
    $width  = (is_numeric($args['width']) ? $args['width'] : 0);
    $height = (is_numeric($args['height']) ? $args['height'] : 0);
    $legend = str_replace("'", '', $args['legend']);

    $state = <<<STATE
<script type="text/javascript" language="JavaScript">
document.graphFrom = $from;
document.graphTo = $to;
document.graphWidth = $width;
document.graphHeight = $height;
document.graphLegend = '$legend';
</script>
STATE;

    return $state;

}//end generate_graph_js_state()


function print_percentage_bar($width, $height, $percent, $left_text, $left_colour, $left_background, $right_text, $right_colour, $right_background) {
    if ($percent > '100') {
        $size_percent = '100';
    }
    else {
        $size_percent = $percent;
    }

    $output = '
        <div class="container" style="width:'.$width.'px; height:'.$height.'px;">
        <div class="progress" style="min-width: 2em; background-color:#'.$right_background.'; height:'.$height.'px;">
        <div class="progress-bar" role="progressbar" aria-valuenow="'.$size_percent.'" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width:'.$size_percent.'%; background-color: #'.$left_background.';">
        </div>
        </div>
        <b class="pull-left" style="padding-left: 4px; height: '.$height.'px;margin-top:-'.($height * 2).'px; color:#'.$left_colour.';">'.$left_text.'</b>
        <b class="pull-right" style="padding-right: 4px; height: '.$height.'px;margin-top:-'.($height * 2).'px; color:#'.$right_colour.';">'.$right_text.'</b>
        </div>
        ';

    return $output;

}//end print_percentage_bar()


function generate_entity_link($type, $entity, $text=null, $graph_type=null) {
    global $config, $entity_cache;

    if (is_numeric($entity)) {
        $entity = get_entity_by_id_cache($type, $entity);
    }

    switch ($type) {
    case 'port':
        $link = generate_port_link($entity, $text, $graph_type);
        break;

    case 'storage':
        if (empty($text)) {
            $text = $entity['storage_descr'];
        }

        $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'storage'));
        break;

    default:
        $link = $entity[$type.'_id'];
    }

    return ($link);

}//end generate_entity_link()


function generate_port_link($port, $text=null, $type=null, $overlib=1, $single_graph=0) {
    global $config;

    $graph_array = array();
    $port        = ifNameDescr($port);
    if (!$text) {
        $text = fixIfName($port['label']);
    }

    if ($type) {
        $port['graph_type'] = $type;
    }

    if (!isset($port['graph_type'])) {
        $port['graph_type'] = 'port_bits';
    }

    $class = ifclass($port['ifOperStatus'], $port['ifAdminStatus']);

    if (!isset($port['hostname'])) {
        $port = array_merge($port, device_by_id_cache($port['device_id']));
    }

    $content = '<div class=list-large>'.$port['hostname'].' - '.fixifName($port['label']).'</div>';
    if ($port['ifAlias']) {
        $content .= escape_quotes($port['ifAlias']).'<br />';
    }

    $content              .= "<div style=\'width: 850px\'>";
    $graph_array['type']   = $port['graph_type'];
    $graph_array['legend'] = 'yes';
    $graph_array['height'] = '100';
    $graph_array['width']  = '340';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['from']   = $config['time']['day'];
    $graph_array['id']     = $port['port_id'];
    $content              .= generate_graph_tag($graph_array);
    if ($single_graph == 0) {
        $graph_array['from'] = $config['time']['week'];
        $content            .= generate_graph_tag($graph_array);
        $graph_array['from'] = $config['time']['month'];
        $content            .= generate_graph_tag($graph_array);
        $graph_array['from'] = $config['time']['year'];
        $content            .= generate_graph_tag($graph_array);
    }

    $content .= '</div>';

    $url = generate_port_url($port);

    if ($overlib == 0) {
        return $content;
    }
    else if (port_permitted($port['port_id'], $port['device_id'])) {
        return overlib_link($url, $text, $content, $class);
    }
    else {
        return fixifName($text);
    }

}//end generate_port_link()


function generate_port_url($port, $vars=array()) {
    return generate_url(array('page' => 'device', 'device' => $port['device_id'], 'tab' => 'port', 'port' => $port['port_id']), $vars);

}//end generate_port_url()


function generate_peer_url($peer, $vars=array()) {
    return generate_url(array('page' => 'device', 'device' => $peer['device_id'], 'tab' => 'routing', 'proto' => 'bgp'), $vars);

}//end generate_peer_url()


function generate_bill_url($bill, $vars=array()) {
    return generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id']), $vars);

}//end generate_bill_url()


function generate_port_image($args) {
    if (!$args['bg']) {
        $args['bg'] = 'FFFFFF';
    }

    return "<img src='graph.php?type=".$args['graph_type'].'&amp;id='.$args['port_id'].'&amp;from='.$args['from'].'&amp;to='.$args['to'].'&amp;width='.$args['width'].'&amp;height='.$args['height'].'&amp;bg='.$args['bg']."'>";

}//end generate_port_image()


function generate_port_thumbnail($port) {
    global $config;
    $port['graph_type'] = 'port_bits';
    $port['from']       = $config['time']['day'];
    $port['to']         = $config['time']['now'];
    $port['width']      = 150;
    $port['height']     = 21;
    return generate_port_image($port);

}//end generate_port_thumbnail()


function print_port_thumbnail($args) {
    echo generate_port_link($args, generate_port_image($args));

}//end print_port_thumbnail()


function print_optionbar_start($height=0, $width=0, $marginbottom=5) {
    echo '
        <div class="well well-sm">
        ';

}//end print_optionbar_start()


function print_optionbar_end() {
    echo '  </div>';

}//end print_optionbar_end()


function geteventicon($message) {
    if ($message == 'Device status changed to Down') {
        $icon = 'server_connect.png';
    }

    if ($message == 'Device status changed to Up') {
        $icon = 'server_go.png';
    }

    if ($message == 'Interface went down' || $message == 'Interface changed state to Down') {
        $icon = 'if-disconnect.png';
    }

    if ($message == 'Interface went up' || $message == 'Interface changed state to Up') {
        $icon = 'if-connect.png';
    }

    if ($message == 'Interface disabled') {
        $icon = 'if-disable.png';
    }

    if ($message == 'Interface enabled') {
        $icon = 'if-enable.png';
    }

    if (isset($icon)) {
        return $icon;
    }
    else {
        return false;
    }

}//end geteventicon()


function overlibprint($text) {
    return "onmouseover=\"return overlib('".$text."');\" onmouseout=\"return nd();\"";

}//end overlibprint()


function humanmedia($media) {
    global $rewrite_iftype;
    array_preg_replace($rewrite_iftype, $media);
    return $media;

}//end humanmedia()


function humanspeed($speed) {
    $speed = formatRates($speed);
    if ($speed == '') {
        $speed = '-';
    }

    return $speed;

}//end humanspeed()


function devclass($device) {
    if (isset($device['status']) && $device['status'] == '0') {
        $class = 'list-device-down';
    }
    else {
        $class = 'list-device';
    }

    if (isset($device['ignore']) && $device['ignore'] == '1') {
        $class = 'list-device-ignored';
        if (isset($device['status']) && $device['status'] == '1') {
            $class = 'list-device-ignored-up';
        }
    }

    if (isset($device['disabled']) && $device['disabled'] == '1') {
        $class = 'list-device-disabled';
    }

    return $class;

}//end devclass()


function getlocations() {
    $locations           = array();

    // Fetch regular locations
    if ($_SESSION['userlevel'] >= '5') {
        $rows = dbFetchRows('SELECT D.device_id,location FROM devices AS D GROUP BY location ORDER BY location');
    }
    else {
        $rows = dbFetchRows('SELECT D.device_id,location FROM devices AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = ? GROUP BY location ORDER BY location', array($_SESSION['user_id']));
    }

    foreach ($rows as $row) {
        // Only add it as a location if it wasn't overridden (and not already there)
        if ($row['location'] != '') {
            if (!in_array($row['location'], $locations)) {
                $locations[] = $row['location'];
            }
        }
    }

    sort($locations);
    return $locations;

}//end getlocations()


function foldersize($path) {
    $total_size  = 0;
    $files       = scandir($path);
    $total_files = 0;

    foreach ($files as $t) {
        if (is_dir(rtrim($path, '/').'/'.$t)) {
            if ($t <> '.' && $t <> '..') {
                $size        = foldersize(rtrim($path, '/').'/'.$t);
                $total_size += $size;
            }
        }
        else {
            $size        = filesize(rtrim($path, '/').'/'.$t);
            $total_size += $size;
            $total_files++;
        }
    }

    return array(
        $total_size,
        $total_files,
    );

}//end foldersize()


function generate_ap_link($args, $text=null, $type=null) {
    global $config;

    $args = ifNameDescr($args);
    if (!$text) {
        $text = fixIfName($args['label']);
    }

    if ($type) {
        $args['graph_type'] = $type;
    }

    if (!isset($args['graph_type'])) {
        $args['graph_type'] = 'port_bits';
    }

    if (!isset($args['hostname'])) {
        $args = array_merge($args, device_by_id_cache($args['device_id']));
    }

    $content = '<div class=list-large>'.$args['text'].' - '.fixifName($args['label']).'</div>';
    if ($args['ifAlias']) {
        $content .= $args['ifAlias'].'<br />';
    }

    $content              .= "<div style=\'width: 850px\'>";
    $graph_array           = array();
    $graph_array['type']   = $args['graph_type'];
    $graph_array['legend'] = 'yes';
    $graph_array['height'] = '100';
    $graph_array['width']  = '340';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['from']   = $config['time']['day'];
    $graph_array['id']     = $args['accesspoint_id'];
    $content              .= generate_graph_tag($graph_array);
    $graph_array['from']   = $config['time']['week'];
    $content              .= generate_graph_tag($graph_array);
    $graph_array['from']   = $config['time']['month'];
    $content              .= generate_graph_tag($graph_array);
    $graph_array['from']   = $config['time']['year'];
    $content              .= generate_graph_tag($graph_array);
    $content              .= '</div>';

    $url = generate_ap_url($args);
    if (port_permitted($args['interface_id'], $args['device_id'])) {
        return overlib_link($url, $text, $content, null);
    }
    else {
        return fixifName($text);
    }

}//end generate_ap_link()


function generate_ap_url($ap, $vars=array()) {
    return generate_url(array('page' => 'device', 'device' => $ap['device_id'], 'tab' => 'accesspoint', 'ap' => $ap['accesspoint_id']), $vars);

}//end generate_ap_url()


function report_this($message) {
    global $config;
    return '<h2>'.$message.' Please <a href="'.$config['project_issues'].'">report this</a> to the '.$config['project_name'].' developers.</h2>';

}//end report_this()


function report_this_text($message) {
    global $config;
    return $message.'\nPlease report this to the '.$config['project_name'].' developers at '.$config['project_issues'].'\n';

}//end report_this_text()


// Find all the files in the given directory that match the pattern


function get_matching_files($dir, $match='/\.php$/') {
    global $config;

    $list = array();
    if ($handle = opendir($dir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != '.' && $file != '..' && preg_match($match, $file) === 1) {
                $list[] = $file;
            }
        }

        closedir($handle);
    }

    return $list;

}//end get_matching_files()


// Include all the files in the given directory that match the pattern


function include_matching_files($dir, $match='/\.php$/') {
    foreach (get_matching_files($dir, $match) as $file) {
        include_once $file;
    }

}//end include_matching_files()


function generate_pagination($count, $limit, $page, $links=2) {
    $end_page   = ceil($count / $limit);
    $start      = (($page - $links) > 0) ? ($page - $links) : 1;
    $end        = (($page + $links) < $end_page) ? ($page + $links) : $end_page;
    $return     = '<ul class="pagination">';
    $link_class = ($page == 1) ? 'disabled' : '';
    $return    .= "<li><a href='' onClick='changePage(1,event);'>&laquo;</a></li>";
    $return    .= "<li class='$link_class'><a href='' onClick='changePage($page - 1,event);'>&lt;</a></li>";

    if ($start > 1) {
        $return .= "<li><a href='' onClick='changePage(1,event);'>1</a></li>";
        $return .= "<li class='disabled'><span>...</span></li>";
    }

    for ($x = $start; $x <= $end; $x++) {
        $link_class = ($page == $x) ? 'active' : '';
        $return    .= "<li class='$link_class'><a href='' onClick='changePage($x,event);'>$x </a></li>";
    }

    if ($end < $end_page) {
        $return .= "<li class='disabled'><span>...</span></li>";
        $return .= "<li><a href='' onClick='changePage($end_page,event);'>$end_page</a></li>";
    }

    $link_class = ($page == $end_page) ? 'disabled' : '';
    $return    .= "<li class='$link_class'><a href='' onClick='changePage($page + 1,event);'>&gt;</a></li>";
    $return    .= "<li class='$link_class'><a href='' onClick='changePage($end_page,event);'>&raquo;</a></li>";
    $return    .= '</ul>';
    return ($return);

}//end generate_pagination()


function is_admin() {
    if ($_SESSION['userlevel'] >= '10') {
        $allowed = true;
    }
    else {
        $allowed = false;
    }

    return $allowed;

}//end is_admin()


function is_read() {
    if ($_SESSION['userlevel'] == '5') {
        $allowed = true;
    }
    else {
        $allowed = false;
    }

    return $allowed;

}//end is_read()

function is_demo_user() {

    if ($_SESSION['userlevel'] == 11) {
        return true;
    }
    else {
        return false;
    }
}// end is_demo_user();

function is_normal_user() {

    if (is_admin() === false && is_read() === false && is_demo_user() === false) {
        return true;
    }
    else {
        return false;
    }
}// end is_normal_user()

function demo_account() {
    print_error("You are logged in as a demo account, this page isn't accessible to you");

}//end demo_account()


function get_client_ip() {
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else {
        $client_ip = $_SERVER['REMOTE_ADDR'];
    }

    return $client_ip;

}//end get_client_ip()


function shorten_interface_type($string) {
    return str_ireplace(
        array(
            'FastEthernet',
            'TenGigabitEthernet',
            'GigabitEthernet',
            'Port-Channel',
            'Ethernet',
        ),
        array(
            'Fa',
            'Te',
            'Gi',
            'Po',
            'Eth',
        ),
        $string
    );

}//end shorten_interface_type()


function clean_bootgrid($string) {
    $output = str_replace(array("\r", "\n"), '', $string);
    $output = addslashes($output);
    return $output;

}//end clean_bootgrid()


// Insert new config items
function add_config_item($new_conf_name, $new_conf_value, $new_conf_type, $new_conf_desc) {
    if (dbInsert(array('config_name' => $new_conf_name, 'config_value' => $new_conf_value, 'config_default' => $new_conf_value, 'config_type' => $new_conf_type, 'config_desc' => $new_conf_desc, 'config_group' => '500_Custom Settings', 'config_sub_group' => '01_Custom settings', 'config_hidden' => '0', 'config_disabled' => '0'), 'config')) {
        $db_inserted = 1;
    }
    else {
        $db_inserted = 0;
    }

    return ($db_inserted);

}//end add_config_item()


function get_config_by_group($group) {
    $group = array($group);
    $items = array();
    foreach (dbFetchRows("SELECT * FROM `config` WHERE `config_group` = '?'", array($group)) as $config_item) {
        $val = $config_item['config_value'];
        if (filter_var($val, FILTER_VALIDATE_INT)) {
            $val = (int) $val;
        }
        else if (filter_var($val, FILTER_VALIDATE_FLOAT)) {
            $val = (float) $val;
        }
        else if (filter_var($val, FILTER_VALIDATE_BOOLEAN)) {
            $val = (boolean) $val;
        }

        if ($val === true) {
            $config_item += array('config_checked' => 'checked');
        }

        $items[$config_item['config_name']] = $config_item;
    }

    return $items;

}//end get_config_by_group()


function get_config_like_name($name) {
    $name  = array($name);
    $items = array();
    foreach (dbFetchRows("SELECT * FROM `config` WHERE `config_name` LIKE '%?%'", array($name)) as $config_item) {
        $items[$config_item['config_id']] = $config_item;
    }

    return $items;

}//end get_config_like_name()


function get_config_by_name($name) {
    $config_item = dbFetchRow('SELECT * FROM `config` WHERE `config_name` = ?', array($name));
    return $config_item;

}//end get_config_by_name()


function set_config_name($name, $config_value) {
    return dbUpdate(array('config_value' => $config_value), 'config', '`config_name`=?', array($name));

}//end set_config_name()


function get_url() {
    // http://stackoverflow.com/questions/2820723/how-to-get-base-url-with-php
    // http://stackoverflow.com/users/184600/ma%C4%8Dek
    return sprintf(
        '%s://%s%s',
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
    );

}//end get_url()


function alert_details($details) {
    if (!is_array($details)) {
        $details = json_decode(gzuncompress($details), true);
    }

    $fault_detail = '';
    foreach ($details['rule'] as $o => $tmp_alerts) {
        $fallback      = true;
        $fault_detail .= '#'.($o + 1).':&nbsp;';
        if ($tmp_alerts['bill_id']) {
            $fault_detail .= '<a href="'.generate_bill_url($tmp_alerts).'">'.$tmp_alerts['bill_name'].'</a>;&nbsp;';
            $fallback      = false;
        }

        if ($tmp_alerts['port_id']) {
            $fault_detail .= generate_port_link($tmp_alerts).';&nbsp;';
            $fallback      = false;
        }

        if ($fallback === true) {
            foreach ($tmp_alerts as $k => $v) {
                if (!empty($v) && $k != 'device_id' && (stristr($k, 'id') || stristr($k, 'desc') || stristr($k, 'msg')) && substr_count($k, '_') <= 1) {
                    $fault_detail .= "$k => '$v', ";
                }
            }

            $fault_detail = rtrim($fault_detail, ', ');
        }

        $fault_detail .= '<br>';
    }//end foreach

    return $fault_detail;

}//end alert_details()

function dynamic_override_config($type, $name, $device) {
    $attrib_val = get_dev_attrib($device,$name);
    if ($attrib_val == 'true') {
        $checked = 'checked';
    }
    else {
        $checked = '';
    }
    if ($type == 'checkbox') {
        return '<input type="checkbox" id="override_config" name="override_config" data-attrib="'.$name.'" data-device_id="'.$device['device_id'].'" data-size="small" '.$checked.'>';
    }
    elseif ($type == 'text') {
        return '<input type="text" id="override_config_text" name="override_config_text" data-attrib="'.$name.'" data-device_id="'.$device['device_id'].'" value="'.$attrib_val.'">';
    }
}//end dynamic_override_config()

function generate_dynamic_config_panel($title,$end_panel=true,$config_groups,$items=array(),$transport='') {
    $anchor = md5($title);
    $output = '
<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#'.$anchor.'">'.$title.'</a>
    ';
    if (!empty($transport)) {
        $output .= '<button name="test-alert" id="test-alert" type="button" data-transport="'.$transport.'" class="btn btn-primary btn-xs pull-right">Test transport</button>';
    }
    $output .= '
        </h4>
    </div>
    <div id="'.$anchor.'" class="panel-collapse collapse">
        <div class="panel-body">
    ';

    if (!empty($items)) {
        foreach ($items as $item) {
            $output .= '
            <div class="form-group has-feedback">
                <label for="'.$item['name'].'"" class="col-sm-4 control-label">'.$item['descr'].' </label>
                <div data-toggle="tooltip" title="'.$config_groups[$item['name']]['config_descr'].'" class="toolTip glyphicon glyphicon-question-sign"></div>
                <div class="col-sm-4">
            ';
            if ($item['type'] == 'checkbox') {
                $output .= '<input id="'.$item['name'].'" type="checkbox" name="global-config-check" '.$config_groups[$item['name']]['config_checked'].' data-on-text="Yes" data-off-text="No" data-size="small" data-config_id="'.$config_groups[$item['name']]['config_id'].'">';
            }
            elseif ($item['type'] == 'text') {
                $output .= '
                <input id="'.$item['name'].'" class="form-control" type="text" name="global-config-input" value="'.$config_groups[$item['name']]['config_value'].'" data-config_id="'.$config_groups[$item['name']]['config_id'].'">
                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                ';
            }
            elseif ($item['type'] == 'select') {
                $output .= '
                <select id="'.$config_groups[$item['name']]['name'].'" class="form-control" name="global-config-select" data-config_id="'.$config_groups[$item['name']]['config_id'].'">
                ';
                if (!empty($item['options'])) {
                    foreach ($item['options'] as $option) {
                        $output .= '<option value="'.$option.'"';
                        if ($option == $config_groups[$item['name']]['config_value']) {
                            $output .= ' selected';
                        }
                        $output .= '>'.$option.'</option>';
                    }
                }
                $output .='
                </select>
                <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                ';
            }
            $output .= '
                </div>
            </div>
            ';
        }
    }

    if ($end_panel === true) {
        $output .= '
        </div>
    </div>
</div>
        ';
    }
    return $output;
}//end generate_dynamic_config_panel()

function get_ripe_api_whois_data_json($ripe_data_param, $ripe_query_param) {
    $ripe_whois_url = 'https://stat.ripe.net/data/'. $ripe_data_param . '/data.json?resource=' . $ripe_query_param;
    return json_decode(file_get_contents($ripe_whois_url) , true);
}//end get_ripe_api_whois_data_json()

