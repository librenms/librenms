<?php

use LibreNMS\Agent\Unix\Mdadm\HtmlData;

if (! isset($app, $device) || ! $app instanceof App\Models\Application || ! is_array($device)) {
    return;
}

$_mdadmData = HtmlData::forDevice($app, $device);

if (empty($_mdadmData->arrayNames())) {
    return;
}

echo view('device.overview.apps.mdadm', [
    'app' => $app,
    'data' => $_mdadmData,
    'appLink' => LibreNMS\Util\Url::generate([
        'page' => 'device',
        'device' => $app->device_id,
        'tab' => 'apps',
        'app' => 'mdadm',
    ]),
]);

unset($_mdadmData);
