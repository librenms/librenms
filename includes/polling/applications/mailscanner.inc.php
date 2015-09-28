<?php

// Polls MailScanner statistics from script via SNMP
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-mailscannerV2-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$oid          = 'nsExtendOutputFull.11.109.97.105.108.115.99.97.110.110.101.114';

$mailscanner = snmp_get($device, $oid, $options);

echo ' mailscanner';

list ($msg_recv, $msg_rejected, $msg_relay, $msg_sent, $msg_waiting, $spam, $virus) = explode("\n", $mailscanner);

if (!is_file($rrd_filename)) {
    rrdtool_create(
        $rrd_filename,
        '--step 300 
        DS:msg_recv:COUNTER:600:0:125000000000 
        DS:msg_rejected:COUNTER:600:0:125000000000 
        DS:msg_relay:COUNTER:600:0:125000000000 
        DS:msg_sent:COUNTER:600:0:125000000000 
        DS:msg_waiting:COUNTER:600:0:125000000000 
        DS:spam:COUNTER:600:0:125000000000 
        DS:virus:COUNTER:600:0:125000000000 '.$config['rrd_rra']
    );
}

$fields = array(
                'msg_recv'     => $msg_recv,
                'msg_rejected' => $msg_rejected,
                'msg_relay'    => $msg_relay,
                'msg_sent'     => $msg_sent,
                'msg_waiting'  => $msg_waiting,
                'spam'         => $spam,
                'virus'        => $virus,
);

rrdtool_update($rrd_filename, $fields);
