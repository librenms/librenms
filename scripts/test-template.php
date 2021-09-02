#!/usr/bin/env php
<?php

$init_modules = ['alerts', 'laravel'];
require __DIR__ . '/../includes/init.php';

use LibreNMS\Alert\AlertData;
use LibreNMS\Alert\RunAlerts;
use LibreNMS\Alert\Template;
use LibreNMS\Util\Debug;

$options = getopt('t:h:r:p:s:d::');

if (isset($options['t']) && isset($options['h']) && isset($options['r'])) {
    Debug::set(isset($options['d']));
    $runAlerts = new RunAlerts();

    $template_id = $options['t'];
    $device_id = ctype_digit($options['h']) ? $options['h'] : getidbyname($options['h']);
    $rule_id = (int) $options['r'];

    $where = 'alerts.device_id=' . $device_id . ' && alerts.rule_id=' . $rule_id;
    if (isset($options['s'])) {
        $where .= ' alerts.state=' . (int) $options['s'];
    }

    $alerts = $runAlerts->loadAlerts($where);
    if (empty($alerts)) {
        echo "No alert found, make sure to select an active alert.\n";
        exit(2);
    }

    $obj = $runAlerts->describeAlert($alerts[0]);
    if (isset($options['p'])) {
        $obj['transport'] = $options['p'];
    }
    $type = new Template;
    $obj['alert'] = new AlertData($obj);
    $obj['title'] = $type->getTitle($obj);
    $obj['msg'] = $type->getBody($obj);
    unset($obj['template']);
    unset($obj['alert']);
    print_r($obj);
} else {
    c_echo('
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

');
    exit(1);
}
