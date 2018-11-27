<?php

// [sysDescr]
// ASR5124C-CO, iBOS Version ibos-asr5k-6.1.9-ED-R Copyright (c) 2001-2015 by PacketFront Network Products AB Compiled Tue May 5 19:56:06 CST 2015 by builder
// ASR5624SFS-CO, iBOS Version ibos-asr5k-6.1.9-ED-R Copyright (c) 2001-2015 by PacketFront Network Products AB Compiled Tue May 5 19:56:06 CST 2015 by builder
// ASR5724SFL-CO, iBOS Version ibos-asr5k-6.3.11-ED-R Copyright (c) 2001-2017 by Waystream AB Compiled Wed Aug 30 15:37:06 CST 2017 by builder
// ASR6026-AC, iBOS Version ibos-asr6k-6.3.11-ED-R Copyright (c) 2001-2017 by Waystream AB Compiled Wed Aug 30 15:52:04 CST 2017 by builder
// ASR6124C-CO, iBOS Version ibos-asr6k-6.1.9-ED-R Copyright (c) 2001-2015 by PacketFront Network Products AB Compiled Tue May 5 19:41:20 CST 2015 by builder
// ASR6126-AC, iBOS Version ibos-asr6k-6.3.11-ED-R Copyright (c) 2001-2017 by Waystream AB Compiled Wed Aug 30 15:52:04 CST 2017 by builder
// MS4026-AC, iBOS Version ibos-ms4k-6.3.11-ED-R  Copyright (c) 2001-2017 by Waystream AB Compiled Wed Aug 30 16:04:36 CST 2017 by builder
// SE1, iBOS Version ibos-se1-6.1.9-ED-R Copyright (c) 2001-2015 by PacketFront Network Products AB Compiled Tue May 5 20:09:26 CST 2015 by builder

if (preg_match('/(.*), iBOS Version ibos-.*?-(.*)\s+Copyright/', $device['sysDescr'], $regexp_result)) {
    $hardware = $regexp_result[1];
    $version  = $regexp_result[2];
}
