<?php

print_optionbar_start();

$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'ipmi',
    );

echo generate_link('Overview', $link_array, array('ipmi' => 'overview'));
echo '|';
echo generate_link('Chassis', $link_array, array('ipmi' => 'chassis'));
echo ',';
echo generate_link('SEL', $link_array, array('ipmi' => 'sel'));
echo ',';
echo generate_link('Sensors', $link_array, array('ipmi' => 'Sensors'));

print_optionbar_end();

if (is_file('includes/html/pages/device/ipmi/'.mres($vars['ipmi']).'.inc.php')) {
    include 'includes/html/pages/device/ipmi/'.mres($vars['ipmi']).'.inc.php';
} else {
    include 'includes/html/pages/device/ipmi/overview.inc.php';
}

$pagetitle[] = 'IPMI';
