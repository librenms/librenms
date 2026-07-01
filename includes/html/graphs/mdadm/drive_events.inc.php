<?php

$unit_text = 'Events/min';
$ds = 'events';

// events is a DERIVE (per-second rate); scale to per-minute for display.
$multiplier = 60;

require 'includes/html/graphs/mdadm/drive_common.inc.php';
