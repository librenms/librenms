<?php

/*
 * Daily Task Checks
 * (c) 2013 LibreNMS Contributors
 */

include('includes/defaults.inc.php');
include('config.php');

$options = getopt("f:");

if ( $options['f'] === 'update') { echo $config['update']; }

?>
