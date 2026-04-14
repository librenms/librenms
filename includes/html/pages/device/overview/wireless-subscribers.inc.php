<?php

require_once 'includes/html/pages/device/wireless-summary.inc.php';

$subscriber_summary = librenms_wireless_subscriber_summary((int) $device['device_id']);
librenms_render_wireless_subscriber_summary($subscriber_summary);
