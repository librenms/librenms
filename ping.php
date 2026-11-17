#!/usr/bin/env php
<?php

use App\Jobs\PingCheck;
use LibreNMS\Util\Debug;

$init_modules = ['alerts', 'laravel', 'nodb'];
require __DIR__ . '/includes/init.php';

$options = getopt('fhdvg:');

if (isset($options['h'])) {
    echo <<<'END'
ping.php: Usage ping.php [-d] [-v] [-g group(s)]
  -d enable debug output
  -v enable verbose debug output
  -g only ping devices for this poller group, may be comma separated list

END;
    exit;
}

Debug::set(isset($options['d']));
Debug::setVerbose(isset($options['v']));

$scheduler = \App\Facades\LibrenmsConfig::get('schedule_type.ping');
if (! isset($options['f']) && $scheduler != 'legacy' && $scheduler != 'cron') {
    if (Debug::isEnabled()) {
        echo "Fast Pings are not enabled for cron scheduling.  Add the -f command argument if you want to force this command to run.\n";
    }
    exit(0);
}

if (isset($options['g'])) {
    $groups = explode(',', $options['g']);
} else {
    $groups = [];
}

PingCheck::dispatchSync($groups);
