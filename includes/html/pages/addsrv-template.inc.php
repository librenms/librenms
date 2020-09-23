<?php

$no_refresh = true;

if (! Auth::user()->hasGlobalAdmin()) {
    include 'includes/html/error-no-perm.inc.php';
} else {
    if ($vars['addsrv-template']) {
        if (Auth::user()->hasGlobalAdmin()) {
            $updated = '1';

            $service_template_id = add_service_template($vars['device_group'], $vars['type'], $vars['descr'], $vars['ip'], $vars['params'], $vars['ignore'], $vars['disabled'], 0);
            if ($service_template_id !== null) {
                $message .= $message_break . 'Service Template added (' . $service_template_id . ')!';
                $message_break .= '<br />';
            }
        }
    }
    foreach (list_available_services() as $current_service) {
        $servicesform .= "<option value='$current_service'>$current_service</option>";
    }

    foreach (dbFetchRows('SELECT * FROM `device_groups` ORDER BY `name`') as $device_group) {
        $devicegroupsform .= "<option value='" . $device_group['id'] . "'>" . $device_group['name'] . '</option>';
    }

    if ($updated) {
        print_message('Device Settings Saved');
    }

    $pagetitle[] = 'Add service template';

    echo "<div class='row'>
        <div class='col-sm-3'>
        </div>   
        <div class='col-sm-6'>";

    include_once 'includes/html/print-service-add-template.inc.php';

    echo '</div>
        </div>';
}//end if
