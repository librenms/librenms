#!/usr/bin/env php
<?php

use App\Jobs\PingCheck;
use LibreNMS\Data\Source\Fping;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Util\Debug;

$init_modules = ['alerts', 'laravel', 'nodb'];
require __DIR__ . '/includes/init.php';

$options = getopt('fhdvrg:');

if (isset($options['h'])) {
    echo <<<'END'
ping.php: Usage ping.php [-d] [-v] [-r] [-g group(s)]
  -d enable debug output
  -v enable verbose debug output
  -r do not create or update RRDs
  -g only ping devices for this poller group, may be comma separated list

END;
    exit;
}

Debug::set(isset($options['d']));
Debug::setVerbose(isset($options['v']));

if (! isset($options['f']) && ! Fping::runPing('cron')) {
    if (Debug::isEnabled()) {
        echo "Fast Pings are not enabled for cron scheduling.  Add -f to the command to run manually, or make sure the icmp_check option is set to true and the schedule_type.ping option is set to cron to allow crontab scheduling.\n";
    }
    exit(0);
}

Datastore::init($options);

if (isset($options['g'])) {
    $groups = explode(',', $options['g']);
} else {
    $groups = [];
}

PingCheck::dispatchSync(isset($options['f']) ? 'force' : 'cron', $groups);
