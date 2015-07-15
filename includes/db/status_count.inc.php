<?php

$devices['up']       = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `status` = '1' AND `ignore` = '0'  AND `disabled` = '0'");
$devices['down']     = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `status` = '0' AND `ignore` = '0'  AND `disabled` = '0'");
$devices['ignored']  = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `ignore` = '1' AND `disabled` = '0'");
$devices['disabled'] = dbFetchCell("SELECT COUNT(*) FROM devices WHERE `disabled` = '1'");

$ports['count']    = dbFetchCell("SELECT COUNT(*) FROM ports WHERE `deleted` = '0'");
$ports['up']       = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'");
$ports['down']     = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'down' AND I.`ifAdminStatus` = 'up'");
$ports['shutdown'] = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'");
$ports['ignored']  = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')");
$ports['errored']  = dbFetchCell("SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')");

$services['count']    = dbFetchCell('SELECT COUNT(*) FROM services');
$services['up']       = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '1'");
$services['down']     = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '0'");
$services['ignored']  = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_ignore` = '1' AND `service_disabled` = '0'");
$services['disabled'] = dbFetchCell("SELECT COUNT(*) FROM services WHERE `service_disabled` = '1'");
