#!/usr/bin/env php
<?php

$init_modules = array('alerts');
require __DIR__ . '/../includes/init.php';

$options = getopt('t:h:r:p:d::');

if ($options['t'] && $options['h'] && $options['r']) {
    if (isset($options['d'])) {
        $debug = true;
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_reporting', 1);
    }
    $template_id = $options['t'];
    $device_id = ctype_digit($options['h']) ? $options['h'] : getidbyname($options['h']);
    $rule_id = $options['r'];

    $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($device_id, $rule_id));
    $alert['details'] = json_decode(gzuncompress($alert['details']), true);
    $obj = DescribeAlert($alert);
    $obj['template'] = dbFetchCell('SELECT `template` FROM `alert_templates` WHERE `id`=?', array($template_id));
    if (isset($options['p'])) {
        $obj['transport'] = $options['p'];
    }
    d_echo($obj);
    $ack = FormatAlertTpl($obj);
    print_r($ack);
} else {
    c_echo("
Usage:
    -t Is the template ID.
    -h Is the device ID or hostname
    -r Is the rule ID
    -p Is the transport name (optional)
    -d Debug
    
Example:
./scripts/test-template.php -t 10 -d -h localhost -r 2 -p mail

");
    exit(1);
}
