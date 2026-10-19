<?php

/** @var \LibreNMS\OS\Routeros $os */
$os->discoverLteStateSensors();
$os->discoverFirmwareStateSensor();
$os->discoverVrrpStateSensors();
