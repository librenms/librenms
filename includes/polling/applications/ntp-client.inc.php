<?php

## Polls ntp-client statistics from script via SNMP

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-ntpclient-".$app['app_id'].".rrd";
$options      = "-O qv";
$oid          = "nsExtendOutputFull.9.110.116.112.99.108.105.101.110.116";

$ntpclient    = snmp_get($device, $oid, $options);

echo(" ntp-client");

list ($offset, $frequency, $jitter, $noise, $stability) = explode("\n", $ntpclient);

if (!is_file($rrd_filename))
{
  rrdtool_create($rrd_filename, "--step 300 \
        DS:offset:GAUGE:600:-100:125000000000 \
        DS:frequency:GAUGE:600:-100:125000000000 \
        DS:jitter:GAUGE:600:-100:125000000000 \
        DS:noise:GAUGE:600:-100:125000000000 \
        DS:stability:GAUGE:600:-100:125000000000 \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797 \
        RRA:MIN:0.5:1:600 \
        RRA:MIN:0.5:6:700 \
        RRA:MIN:0.5:24:775 \
        RRA:MIN:0.5:288:797 \
        RRA:MAX:0.5:1:600 \
        RRA:MAX:0.5:6:700 \
        RRA:MAX:0.5:24:775 \
        RRA:MAX:0.5:288:797");
}

rrdtool_update($rrd_filename,  "N:$offset:$frequency:$jitter:$noise:$stability");

?>
