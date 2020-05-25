<?php

// NS-ROOT-MIB::resMemUsage.0 = Gauge32: 29
// NS-ROOT-MIB::memSizeMB.0 = INTEGER: 815
if ($device['os'] == 'netscaler') {
    discover_mempool($valid_mempool, $device, '0', 'netscaler', 'Memory');
}
