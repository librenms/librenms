<?php

if (! isset($vars['privoxyPage'])) {
    $vars['privoxyPage'] = 'general';
} elseif ($vars['privoxyPage'] != 'general' &&
         $vars['privoxyPage'] != 'blocks' &&
         $vars['privoxyPage'] != 'http_ver' &&
         $vars['privoxyPage'] != 'http_type' &&
         $vars['privoxyPage'] != 'http_resp' &&
         $vars['privoxyPage'] != 'domains' &&
         $vars['privoxyPage'] != 'conn') {
    $vars['privoxyPage'] = 'general';
}

print_optionbar_start();

$link_tmp = generate_link('General', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'general']);
if ($vars['privoxyPage'] == 'general') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp . ' | ';

$link_tmp = generate_link('Blocks', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'blocks']);
if ($vars['privoxyPage'] == 'blocks') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp . ' | ';

$link_tmp = generate_link('Connections', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'conn']);
if ($vars['privoxyPage'] == 'conn') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp . ' | ';

$link_tmp = generate_link('Domains', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'domains']);
if ($vars['privoxyPage'] == 'domains') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp . ' | ';

$link_tmp = generate_link('HTTP Req Type', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'http_type']);
if ($vars['privoxyPage'] == 'http_type') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp . ' | ';

$link_tmp = generate_link('HTTP Response Codes', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'http_resp']);
if ($vars['privoxyPage'] == 'http_resp') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp . ' | ';

$link_tmp = generate_link('HTTP Version', $link_array, ['app' => 'privoxy', 'privoxyPage' => 'http_ver']);
if ($vars['privoxyPage'] == 'http_ver') {
    $link_tmp = '<span class="pagemenu-selected">' . $link_tmp . '</span>';
}
echo $link_tmp;

print_optionbar_end();

if ($vars['privoxyPage'] == 'general') {
    $graphs = [
        'privoxy_client_requests' => 'Client Requests',
        'privoxy_blocks' => 'Blocked Requests',
        'privoxy_block_percent' => 'Blocked Requests, Percent',
        'privoxy_crunches' => 'Crunches',
        'privoxy_unique_domains' => 'Unique Domains',
        'privoxy_unique_bdomains' => 'Unique Domains Blocked',
        'privoxy_ubd_per' => 'Unique Domains Blocked, Percent',
        'privoxy_bytes_to_client' => 'Bytes Sent To Clients',
        'privoxy_imp_accounted' => 'Improperly Accounted',
    ];
} elseif ($vars['privoxyPage'] == 'conn') {
    $graphs = [
        'privoxy_nog_conns' => 'New Outgoing Requests',
        'privoxy_reused_server_cons' => 'Reused Server Connections',
        'privoxy_max_reqs' => 'Max Requests Per TCP Session',
        'privoxy_out_requests' => 'Out Going Requests',
        'privoxy_bytes_to_client' => 'Bytes To Client',
    ];
} elseif ($vars['privoxyPage'] == 'blocks') {
    $graphs = [
        'privoxy_blocks' => 'Blocked Requests',
        'privoxy_block_percent' => 'Blocked Requests, Percent',
        'privoxy_crunches' => 'Crunches',
        'privoxy_unique_bdomains' => 'Unique Domains, Blocked',
        'privoxy_unique_bdomains_np' => 'Unique Domains Without Port, Blocked',
    ];
} elseif ($vars['privoxyPage'] == 'http_ver') {
    $graphs = [
        'privoxy_ver' => 'HTTP Versions',
        'privoxy_ver_1_0' => 'HTTP/1.0',
        'privoxy_ver_1_1' => 'HTTP/1.1',
        'privoxy_ver_2' => 'HTTP/2',
        'privoxy_ver_3' => 'HTTP/3',
    ];
} elseif ($vars['privoxyPage'] == 'http_type') {
    $graphs = [
        'privoxy_req' => 'HTTP Request Types',
        'privoxy_req_connect' => 'CONNECT',
        'privoxy_req_delete' => 'DELETE',
        'privoxy_req_get' => 'GET',
        'privoxy_req_head' => 'HEAD',
        'privoxy_req_options' => 'OPTIONS',
        'privoxy_req_patch' => 'PATCH',
        'privoxy_req_post' => 'POST',
        'privoxy_req_put' => 'PUT',
        'privoxy_req_trace' => 'TRACE',
    ];
} elseif ($vars['privoxyPage'] == 'http_resp') {
    $graphs = [
        'privoxy_resp_xxx' => 'Response Code Types',
        'privoxy_resp_1xx' => '1xx',
        'privoxy_resp_2xx' => '2xx',
        'privoxy_resp_200' => '200',
        'privoxy_resp_2xx_other' => '2xx Other',
        'privoxy_resp_3xx' => '3xx',
        'privoxy_resp_301' => '301',
        'privoxy_resp_302' => '302',
        'privoxy_resp_303' => '303',
        'privoxy_resp_3xx_other' => '3xx Other',
        'privoxy_resp_4xx' => '4xx',
        'privoxy_resp_403' => '403',
        'privoxy_resp_404' => '404',
        'privoxy_resp_451' => '451',
        'privoxy_resp_4xx_other' => '4xx Other',
        'privoxy_resp_5xx' => '5xx',
        'privoxy_resp_500' => '500',
        'privoxy_resp_502' => '502',
        'privoxy_resp_503' => '503',
        'privoxy_resp_504' => '504',
        'privoxy_resp_5xx_other' => '5xx Other',
    ];
} elseif ($vars['privoxyPage'] == 'domains') {
    $graphs = [
        'privoxy_unique_domains' => 'Unique Domains',
        'privoxy_unique_bdomains' => 'Unique Domains, Blocked',
        'privoxy_unique_domains_np' => 'Unique Domains Without Port',
        'privoxy_unique_bdomains_np' => 'Unique Domains Without Port, Blocked',
        'privoxy_ubd_per' => 'Percent Of Unique Domains Blocked',
        'privoxy_ubd_np_per' => 'Percent Of Unique Domains Blocked, No Port',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
