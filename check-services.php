#!/usr/bin/env php
<?php

/*
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage services
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

chdir(dirname($argv[0]));

require 'includes/defaults.inc.php';
require 'config.php';
require 'includes/definitions.inc.php';
require 'includes/functions.php';

foreach (dbFetchRows('SELECT * FROM `devices` AS D, `services` AS S WHERE S.device_id = D.device_id ORDER by D.device_id DESC') as $service) {
    if ($service['status'] = '1') {
        unset($check, $service_status, $time, $status);
        $service_status = $service['service_status'];
        $service_type   = strtolower($service['service_type']);
        $service_param  = $service['service_param'];
        $checker_script = $config['install_dir'].'/includes/services/'.$service_type.'/check.inc';

        if (is_file($checker_script)) {
            include $checker_script;
        }
        else {
            $cmd = $config['nagios_plugins'] . "/check_" . $service['service_type'] . " -H " . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']);
            $cmd .= " ".$service['service_param'];
            $check = shell_exec($cmd);
            list($check, $time) = split("\|", $check);
            if(stristr($check, "ok -")) {
                $status = 1;
            }
            else {
                $status = 0;
            }
        }

            $update = array();

        if ($service_status != $status) {
            $update['service_changed'] = time();
        }
        else {
            unset($updated);
        }

            $update = array_merge(array('service_status' => $status, 'service_message' => $check, 'service_checked' => time()), $update);
            dbUpdate($update, 'services', '`service_id` = ?', array($service['service_id']));
            unset($update);
    }
    else {
        $status = '0';
    }//end if

} //end foreach
