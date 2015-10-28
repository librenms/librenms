<?php

preg_match('/JetStream [0-9]+-Port/', $poll_device['sysDescr'], $tmp_hardware);

$hardware = $tmp_hardware[0];
