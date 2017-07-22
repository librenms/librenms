#!/usr/bin/env php
<?php

$init_modules = array('alerts');
require __DIR__ . '/../includes/init.php';

$options = getopt('t:h:r:p:s:d::');

if ($options['r'] && $options['h']) {
    if (isset($options['d'])) {
        $debug = true;
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_reporting', 1);
    }
    $rule_id = $options['r'];
    $device_id = ctype_digit($options['h']) ? $options['h'] : getidbyname($options['h']);
    $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($device_id, $rule_id));
    if (empty($alert)) {
        echo "No active alert found, please check that you have the correct ids";
        exit(2);
    }
    $alert['details'] = json_decode(gzuncompress($alert['details']), true);
    $alert['details']['delay'] = 0;
    IssueAlert($alert);
} else {
    c_echo("
Info:
    Use this to send an actual alert via transports that is currently active.
Usage:
    -r Is the Rule ID.
    -h Is the device ID or hostname
    -d Debug
    
Example:
./scripts/test-alert.php -r 4 -d -h localhost

");
    exit(1);
}
