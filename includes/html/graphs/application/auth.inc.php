<?php

use App\Models\Application;

if (isset($vars['id']) && is_numeric($vars['id'])) {
    // check user has access, unless allow_unauth_graphs is enabled
    $app = Application::when(! $auth, fn ($query) => $query->hasAccess(Auth::user()))->firstWhere(['app_id' => $vars['id']]);

    if ($app) {
        $device = device_by_id_cache($app->device_id);
        if ($app->app_type == 'proxmox') {
            $title = ' :: ' . $vars['port'] . '@' . $vars['hostname'];
        }
        $auth = true;
    }
}
