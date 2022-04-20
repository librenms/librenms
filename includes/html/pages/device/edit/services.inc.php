<?php

if (Auth::user()->hasGlobalRead()) {
    if ($vars['addsrv']) {
        if (Auth::user()->hasGlobalAdmin()) {
            $updated = '1';
            // FIXME
            $service = \App\Models\Service::create([
                'device_id' => $vars['device'],
                'service_type' => $vars['type'],
                'service_desc' => $vars['descr'],
                'service_ip' => $vars['ip'],
                'service_param' => $vars['params'],
                'service_ignore' => $vars['ignore'],
                'service_disable' => $vars['disabled'],
                'service_template_id' => 0,
                'service_name' => $vars['name'],
            ]);
            if ($service->exists) {
                $message .= $message_break . 'Service added (' . $service->service_id . ')!';
                $message_break .= '<br />';
            }
        }
    }

    // Build the types list.
    foreach (\LibreNMS\Services::list() as $current_service) {
        $servicesform .= "<option value='$current_service'>$current_service</option>";
    }

    $dev = device_by_id_cache($device['device_id']);
    $devicesform = "<option value='" . $dev['device_id'] . "'>" . $dev['hostname'] . '</option>';

    if ($updated) {
        print_message('Device Settings Saved');
    }

    echo '<div class="col-sm-6 col-sm-offset-3">';

    include_once 'includes/html/print-service-add.inc.php';
} else {
    include 'includes/html/error-no-perm.inc.php';
}
