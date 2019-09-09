<?php

$hostname=escapeshellarg(get_dev_attrib($device, 'ipmi_hostname'));
$username=escapeshellarg(get_dev_attrib($device, 'ipmi_username'));
$password=escapeshellarg(get_dev_attrib($device, 'ipmi_password'));
$ipmi_general='-h '.$hostname.' -u '.$username.' -p '.$password;

echo '<pre>';
system('ipmi-power --stat '.$ipmi_general);
echo("\n");
system('ipmi-sensors '.$ipmi_general);
echo("\n");
system('ipmi-sel '.$ipmi_general);
echo '</pre>';
