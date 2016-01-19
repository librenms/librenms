<?php

// IPMI - We can discover this on poll!
if ($ipmi['host'] = get_dev_attrib($device, 'ipmi_hostname')) {
    echo 'IPMI : ';

    $ipmi['user']     = get_dev_attrib($device, 'ipmi_username');
    $ipmi['password'] = get_dev_attrib($device, 'ipmi_password');

    if ($config['own_hostname'] != $device['hostname'] || $ipmi['host'] != 'localhost') {
        $remote = ' -H '.$ipmi['host'].' -U '.$ipmi['user'].' -P '.$ipmi['password'];
    }

    foreach ($config['ipmi']['type'] as $ipmi_type) {
        $results = external_exec($config['ipmitool']." -I $ipmi_type".$remote.' sensor 2>/dev/null|sort');
        if ($results != '') {
            set_dev_attrib($device, 'ipmi_type', $ipmi_type);
            break;
        }
    }

    echo $ipmi_type;

    $index = 0;

    foreach (explode("\n", $results) as $sensor) {
        // BB +1.1V IOH     | 1.089      | Volts      | ok    | na        | 1.027     | 1.054     | 1.146     | 1.177     | na
        list($desc,$current,$unit,$state,$low_nonrecoverable,$low_limit,$low_warn,$high_warn,$high_limit,$high_nonrecoverable) = explode('|', $sensor);
        $index++;
        if (trim($current) != 'na' && $config['ipmi_unit'][trim($unit)]) {
            discover_sensor(
                $valid['sensor'],
                $config['ipmi_unit'][trim($unit)],
                $device,
                trim($desc),
                $index,
                'ipmi',
                trim($desc),
                '1',
                '1',
                (trim($low_limit) == 'na' ? null : trim($low_limit)),
                (trim($low_warn) == 'na' ? null : trim($low_warn)),
                (trim($high_warn) == 'na' ? null : trim($high_warn)),
                (trim($high_limit) == 'na' ? null : trim($high_limit)),
                $current,
                'ipmi'
            );
        }
    }

    echo "\n";
}
