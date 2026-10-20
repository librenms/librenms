<?php

/*
 * Backwards compatible alias: the per-type Juniper RPM loss graph became
 * sla_loss, which derives the RRD name from the probe's rtt_type. Kept so
 * saved dashboard widgets and bookmarked graph URLs keep rendering.
 */

require 'includes/html/graphs/device/sla_loss.inc.php';
