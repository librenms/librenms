#!/usr/bin/env php
<?php

$init_modules = ['alerts', 'laravel'];
require __DIR__ . '/../includes/init.php';

use LibreNMS\Alert\Template;
use LibreNMS\Alert\AlertData;

$options = getopt('t:h:r:p:s:d::');

if (isset($options['t']) && isset($options['h']) && isset($options['r'])) {
    set_debug(isset($options['d']));

    $template_id = $options['t'];
    $device_id = ctype_digit($options['h']) ? $options['h'] : getidbyname($options['h']);
    $rule_id = $options['r'];
    if (isset($options['s'])) {
        $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 && alert_log.state=? ORDER BY alert_log.id DESC LIMIT 1', array($device_id, $rule_id, intval($options['s'])));
    }
    if (!isset($alert)) {
        $alert = dbFetchRow('SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? && alert_rules.disabled = 0 ORDER BY alert_log.id DESC LIMIT 1', array($device_id, $rule_id));
    }
    $alert['details'] = json_decode(gzuncompress($alert['details']), true);
    $obj = DescribeAlert($alert);
    if (isset($options['p'])) {
        $obj['transport'] = $options['p'];
    }
    $type  = new Template;
    $obj['alert']     = new AlertData($obj);
    $obj['title']     = $type->getTitle($obj);
    $obj['msg']       = $type->getBody($obj);
    unset($obj['template']);
    unset($obj['alert']);
    print_r($obj);
} else {
    c_echo("
Usage:
    -t Is the template ID.
    -h Is the device ID or hostname
    -r Is the rule ID
    -p Is the transport name (optional)
    -s Is the alert state <0|1|2|3|4> (optional - defaults to current state.)
       0 = ok, 1 = alert, 2 = acknowledged, 3 = got worse, 4 = got better
    -d Debug

Example:
./scripts/test-template.php -t 10 -d -h localhost -r 2 -p mail

");
    exit(1);
}
