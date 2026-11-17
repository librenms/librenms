<?php

$simple_rrd = true;

$rrd_filename = \LibreNMS\Util\Nfsen::channels($device['hostname'])[$vars['channel'] ?? ''] ?? null;

require 'nfsen_flowtypes.inc.php';
