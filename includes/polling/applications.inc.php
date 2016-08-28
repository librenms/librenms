<?php

$sql = "SELECT * FROM `applications` WHERE `device_id`  = '".$device['device_id']."'";
d_echo($sql."\n");

$app_rows = dbFetchRows('SELECT * FROM `applications` WHERE `device_id`  = ?', array($device['device_id']));

if (count($app_rows)) {
    foreach ($app_rows as $app) {
        $app_include = $config['install_dir'].'/includes/polling/applications/'.$app['app_type'].'.inc.php';
        if (is_file($app_include)) {
            include $app_include;
        } else {
            echo $app['app_type'].' include missing! ';
        }
    }

    echo "\n";
}
