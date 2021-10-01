#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @copyright  (C) 2018 LibreNMS
 * Adapted from old snmptrap.php handler
 */

use LibreNMS\Util\Debug;

$init_modules = [];
require __DIR__ . '/includes/init.php';

$options = getopt('d::');

if (Debug::set(isset($options['d']))) {
    echo "DEBUG!\n";
}

$text = stream_get_contents(STDIN);

// create handle and send it this trap
\LibreNMS\Snmptrap\Dispatcher::handle(new \LibreNMS\Snmptrap\Trap($text));
