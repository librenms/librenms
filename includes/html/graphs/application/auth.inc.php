<?php

use App\Models\Application;

if (is_numeric($vars['id'])) {
    $app = Application::hasAccess(Auth::user())->firstWhere(['app_id' => $vars['id']]);
    if ($auth || $app) {
        $device = device_by_id_cache($app->device_id);
        if ($app->app_type != 'proxmox') {
            $title = generate_device_link($device);
            $title .= $graph_subtype;
        } else {
            $title = $vars['port'] . '@' . $vars['hostname'] . ' on ' . generate_device_link($device);
        }
        $auth = true;
    }
}
