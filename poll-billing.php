#!/usr/bin/env php
<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage billing
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use LibreNMS\Billing;
use LibreNMS\Data\Store\Datastore;
use LibreNMS\Util\Debug;

$init_modules = [];
require __DIR__ . '/includes/init.php';

if (isset($argv[1]) && is_numeric($argv[1])) {
    // allow old cli style
    $options = ['b' => $argv[1]];
} else {
    $options = getopt('fdb:');
}

Debug::set(isset($options['d']));
Datastore::init();

$scheduler = LibrenmsConfig::get('schedule_type.billing');
if (! isset($options['f']) && $scheduler != 'legacy' && $scheduler != 'cron') {
    Log::debug('Billing is not enabled for cron scheduling. Add the -f command argument if you want to force this command to run.');
    exit(0);
}

$poller_start = microtime(true);
Log::info("Starting Bill Polling Session ... \n");

$query = Bill::query();

if (isset($options['b'])) {
    $query->where('bill_id', $options['b']);
}

foreach ($query->get() as $bill) {
    Log::info('Bill : ' . $bill->bill_name);
    Billing::pollBill($bill);
}

$poller_end = microtime(true);
$poller_run = ($poller_end - $poller_start);
$poller_time = substr($poller_run, 0, 5);

if ($poller_time > 300) {
    Log::warning("BILLING: polling took longer than 5 minutes ($poller_time seconds)!");
}
Log::info("Completed in $poller_time sec");

app('Datastore')->terminate();
