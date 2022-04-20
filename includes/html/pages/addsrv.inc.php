<?php

$no_refresh = true;

if (! Auth::user()->hasGlobalAdmin()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    if ($vars['addsrv']) {
        if (Auth::user()->hasGlobalAdmin()) {
            $updated = '1';
            // FIXME
            $service = \App\Models\Service::create([
                'device_id' => $vars['device'],
                'service_type' => $vars['type'],
                'service_desc' => strip_tags($vars['descr']),
                'service_ip' => $vars['ip'],
                'service_param' => $vars['params'],
                'service_ignore' => $vars['ignore'],
                'service_disable' => $vars['disabled'],
                'service_template_id' => 0,
                'service_name' => strip_tags($vars['name']),
            ]);
            if ($service->exists) {
                $message .= $message_break . 'Service added (' . $service->service_id . ')!';
                $message_break .= '<br />';
            }
        }
    }
    foreach (\LibreNMS\Services::list() as $current_service) {
        $servicesform .= "<option value='$current_service'>$current_service</option>";
    }

    foreach (dbFetchRows('SELECT * FROM `devices` ORDER BY `hostname`') as $device) {
        $devicesform .= "<option value='" . $device['device_id'] . "'>" . format_hostname($device) . '</option>';
    }

    if ($updated) {
        print_message('Device Settings Saved');
    }

    $pagetitle[] = 'Add service';

    echo "<div class='row'>
        <div class='col-sm-3'>
        </div>
        <div class='col-sm-6'>";

    include_once 'includes/html/print-service-add.inc.php';

    echo '</div>
        </div>';
}//end if
