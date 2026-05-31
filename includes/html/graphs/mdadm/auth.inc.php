<?php

use App\Models\Application;
use LibreNMS\Agent\Unix\Mdadm\HtmlData;

if (isset($vars['id']) && is_numeric($vars['id'])) {
    // check user has access, unless allow_unauth_graphs is enabled
    $app = Application::when(! $auth, fn ($query) => $query->hasAccess(Auth::user()))->firstWhere(['app_id' => $vars['id']]);

    if ($app) {
        $device = device_by_id_cache($app->device_id);
        $title = generate_device_link($device);
        $title .= ' :: mdadm';
        $graph_title = $device['hostname'] . '::mdadm';

        if (isset($vars['array']) && $vars['array'] !== '') {
            $htmlData = HtmlData::forDevice($app, $device);
            $arrayKey = (string) $vars['array'];
            $arrMeta = $htmlData->arraysMeta[$arrayKey] ?? [];
            $arrName = trim((string) ($arrMeta['array_name'] ?? ''));
            $arrLabel = $arrName !== '' ? "$arrName ($arrayKey)" : $arrayKey;
            $title .= ' :: ' . generate_link($arrLabel, [
                'page' => 'device', 'device' => $device['device_id'], 'tab' => 'apps', 'app' => 'mdadm',
                'array' => $arrayKey,
            ]);
            $graph_title .= '::' . $arrayKey;
        }

        $auth = true;
    }
}
