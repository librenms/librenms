<?php
/*
 * LibreNMS module to Graph Cisco IPSLA UDP Jitter metrics
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$sla = dbFetchRow('SELECT `sla_nr`,`rtt_type` FROM `slas` WHERE `sla_id` = ?', array($vars['id']));

?>
<div class="well well-sm">
    <!-- Need some kind of header here to represent the SLA -->
</div>

<div class="panel panel-default" id="ipsla">
<?php

// All SLA's support the RTT metric
include 'sla/rtt.inc.php';

// Load the per-type SLA metrics
$rtt_type = basename($sla['rtt_type']);
if (file_exists("includes/html/pages/device/sla/$rtt_type.inc.php")) {
    include "includes/html/pages/device/sla/$rtt_type.inc.php";
}

?>
</div>
<?php
