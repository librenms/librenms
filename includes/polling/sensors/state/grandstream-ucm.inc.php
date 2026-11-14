<?php

// Normalize before the generic poller can extract digits or map an empty value to OK.
$sensor_value = \LibreNMS\OS\GrandstreamUcm::trunkState($sensor_value);
