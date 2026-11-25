<?php

$simple_rrd = true;

$rrd_filename = \LibreNMS\Util\Nfsen::rrd($device['hostname']);

require 'nfsen_flowtypes.inc.php';
