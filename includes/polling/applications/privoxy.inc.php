<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'privoxy';
try {
    $returned = json_app_get($device, $name);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$fields = [
    'block_percent' => 0,
    'blocks' => 0,
    'bytes_to_client' => 0,
    'client_cons' => 0,
    'client_requests' => 0,
    'con_failures' => 0,
    'con_timeouts' => 0,
    'crunches' => 0,
    'empty_resps' => 0,
    'empty_resps_new' => 0,
    'empty_resps_reuse' => 0,
    'fast_redirs' => 0,
    'imp_accounted' => 0,
    'max_reqs' => 0,
    'nog_conns' => 0,
    'out_requests' => 0,
    'req_connect' => 0,
    'req_delete' => 0,
    'req_get' => 0,
    'req_head' => 0,
    'req_options' => 0,
    'req_patch' => 0,
    'req_post' => 0,
    'req_put' => 0,
    'req_trace' => 0,
    'resp_1xx' => 0,
    'resp_200' => 0,
    'resp_2xx' => 0,
    'resp_2xx_other' => 0,
    'resp_301' => 0,
    'resp_302' => 0,
    'resp_303' => 0,
    'resp_3xx' => 0,
    'resp_3xx_other' => 0,
    'resp_403' => 0,
    'resp_404' => 0,
    'resp_451' => 0,
    'resp_4xx' => 0,
    'resp_4xx_other' => 0,
    'resp_500' => 0,
    'resp_502' => 0,
    'resp_503' => 0,
    'resp_504' => 0,
    'resp_5xx' => 0,
    'resp_5xx_other' => 0,
    'reused_server_cons' => 0,
    'ska_offers' => 0,
    'ubd_np_per' => 0,
    'ubd_per' => 0,
    'unique_bdomains' => 0,
    'unique_bdomains_np' => 0,
    'unique_domains' => 0,
    'unique_domains_np' => 0,
    'ver_1_0' => 0,
    'ver_1_1' => 0,
    'ver_2' => 0,
    'ver_3' => 0,
];

$rrd_def = RrdDefinition::make()
    ->addDataset('block_percent', 'GAUGE', 0)
    ->addDataset('blocks', 'GAUGE', 0)
    ->addDataset('bytes_to_client', 'GAUGE', 0)
    ->addDataset('client_cons', 'GAUGE', 0)
    ->addDataset('client_requests', 'GAUGE', 0)
    ->addDataset('con_failures', 'GAUGE', 0)
    ->addDataset('con_timeouts', 'GAUGE', 0)
    ->addDataset('crunches', 'GAUGE', 0)
    ->addDataset('empty_resps', 'GAUGE', 0)
    ->addDataset('empty_resps_new', 'GAUGE', 0)
    ->addDataset('empty_resps_reuse', 'GAUGE', 0)
    ->addDataset('fast_redirs', 'GAUGE', 0)
    ->addDataset('imp_accounted', 'GAUGE', 0)
    ->addDataset('max_reqs', 'GAUGE', 0)
    ->addDataset('nog_conns', 'GAUGE', 0)
    ->addDataset('out_requests', 'GAUGE', 0)
    ->addDataset('req_connect', 'GAUGE', 0)
    ->addDataset('req_delete', 'GAUGE', 0)
    ->addDataset('req_get', 'GAUGE', 0)
    ->addDataset('req_head', 'GAUGE', 0)
    ->addDataset('req_options', 'GAUGE', 0)
    ->addDataset('req_patch', 'GAUGE', 0)
    ->addDataset('req_post', 'GAUGE', 0)
    ->addDataset('req_put', 'GAUGE', 0)
    ->addDataset('req_trace', 'GAUGE', 0)
    ->addDataset('resp_1xx', 'GAUGE', 0)
    ->addDataset('resp_200', 'GAUGE', 0)
    ->addDataset('resp_2xx', 'GAUGE', 0)
    ->addDataset('resp_2xx_other', 'GAUGE', 0)
    ->addDataset('resp_301', 'GAUGE', 0)
    ->addDataset('resp_302', 'GAUGE', 0)
    ->addDataset('resp_303', 'GAUGE', 0)
    ->addDataset('resp_3xx', 'GAUGE', 0)
    ->addDataset('resp_3xx_other', 'GAUGE', 0)
    ->addDataset('resp_403', 'GAUGE', 0)
    ->addDataset('resp_404', 'GAUGE', 0)
    ->addDataset('resp_451', 'GAUGE', 0)
    ->addDataset('resp_4xx', 'GAUGE', 0)
    ->addDataset('resp_4xx_other', 'GAUGE', 0)
    ->addDataset('resp_500', 'GAUGE', 0)
    ->addDataset('resp_502', 'GAUGE', 0)
    ->addDataset('resp_503', 'GAUGE', 0)
    ->addDataset('resp_504', 'GAUGE', 0)
    ->addDataset('resp_5xx', 'GAUGE', 0)
    ->addDataset('resp_5xx_other', 'GAUGE', 0)
    ->addDataset('reused_server_cons', 'GAUGE', 0)
    ->addDataset('ska_offers', 'GAUGE', 0)
    ->addDataset('ubd_np_per', 'GAUGE', 0)
    ->addDataset('ubd_per', 'GAUGE', 0)
    ->addDataset('unique_bdomains', 'GAUGE', 0)
    ->addDataset('unique_bdomains_np', 'GAUGE', 0)
    ->addDataset('unique_domains', 'GAUGE', 0)
    ->addDataset('unique_domains_np', 'GAUGE', 0)
    ->addDataset('ver_1_0', 'GAUGE', 0)
    ->addDataset('ver_1_1', 'GAUGE', 0)
    ->addDataset('ver_2', 'GAUGE', 0)
    ->addDataset('ver_3', 'GAUGE', 0);

foreach ($fields as $key => $value) {
    if (isset($returned['data'][$key])) {
        $fields[$key] = $returned['data'][$key];
    }
}

$rrd_name = ['app', $name, $app->app_id];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// all done so update the app metrics
//
update_application($app, 'OK', $fields);
