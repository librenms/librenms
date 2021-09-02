<?php

// Tranzeo sysDescr is quite uniform :
//
// Tranzeo TR6SL5, OS 6.8.0(1024), FW TR6-5.0.2SL5, 5.xGHz, 0dBi int. antenna
// Tranzeo TR6Rt, OS 6.8.0(1024), FW TR6-3.6.0Rt, 5.xGHz, 19dBi int. antenna
// Tranzeo TR6CPQ, OS 6.3.34(1019), FW TR6-2.0.12CPQ, 2.4GHz, 15dBi int. antenna
// Tranzeo TR900Rt, OS 6.8.0(1024), FW TR900-3.3.3Rt, 900MHz, 17dBi ext. antenna
[$hardware, $version, $features, $hardware_antenna] = explode(', ', $device['sysDescr']);

[,$version] = explode(' ', $version);
[$version] = explode('(', $version);
[,$features] = explode(' ', $features);
