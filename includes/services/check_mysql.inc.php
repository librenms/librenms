<?php

// provide some sane default
if ($service['service_param']) {
    $dbname = $service['service_param'];
} else {
    $dbname = "mysql";
}
$check_cmd = $config['nagios_plugins'] . "/check_mysql -H ".$service['hostname']." ".$dbname;
