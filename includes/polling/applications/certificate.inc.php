<?php
use LibreNMS\Exceptions\JsonAppMissingKeysException;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'certificate';
$app_id = $app['app_id'];
$output = 'OK';

try {
    $certificate_data = json_app_get($device, $name, 1)['data'];
} catch (JsonAppMissingKeysException $e) {
    $certificate_data = $e->getParsedJson();
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' .$e->getCode().':'. $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode().':'.$e->getMessage(), []); // Set empty metrics and error message
    return;
}

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('age', 'GAUGE', 0)
    ->addDataset('remaining_days', 'GAUGE', 0);

$metrics = array();
foreach ($certificate_data as $data) {
    $cert_name = $data['cert_name'];
    $age = $data['age'];
    $remaining_days = $data['remaining_days'];

    $rrd_name = array('app', $name, $app_id, $cert_name);

    $fields = array(
        'age'            => $age,
        'remaining_days' => $remaining_days
    );

    $metrics[$cert_name] = $fields;
    $tags = array('name' => $cert_name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
    data_update($device, 'app', $tags, $fields);
}

update_application($app, $output, $metrics);
