<?php
$check_cmd = shell_exec($config['nagios_plugins'] . "/check_smtp -H ".$service['hostname']);
