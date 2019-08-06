<?php

preg_match('/version (.*)/', $device['sysDescr'], $data);
$version = $data[1];
