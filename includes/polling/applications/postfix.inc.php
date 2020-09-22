<?php

$name = 'postfix';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

$options = '-Oqv';
$queueOID = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.5.109.97.105.108.113';
$detailOID = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.15.112.111.115.116.102.105.120.100.101.116.97.105.108.101.100';
$mailq = snmp_walk($device, $queueOID, $options);
$detail = snmp_walk($device, $detailOID, $options);

[$incomingq, $activeq, $deferredq, $holdq] = explode("\n", $mailq);

[$received, $delivered, $forwarded, $deferred, $bounced, $rejected, $rejectw, $held, $discarded, $bytesr,
     $bytesd, $senders, $sendinghd, $recipients, $recipienthd, $deferralcr, $deferralhid, $chr, $hcrnfqh, $sardnf,
     $sarnobu, $bu, $raruu, $hcrin, $sarnfqa, $rardnf, $rarnfqa, $iuscp, $sce, $scp, $urr] = explode("\n", $detail);

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('incomingq', 'GAUGE', 0)
    ->addDataset('activeq', 'GAUGE', 0)
    ->addDataset('deferredq', 'GAUGE', 0)
    ->addDataset('holdq', 'GAUGE', 0)
    ->addDataset('received', 'GAUGE', 0)
    ->addDataset('delivered', 'GAUGE', 0)
    ->addDataset('forwarded', 'GAUGE', 0)
    ->addDataset('deferred', 'GAUGE', 0)
    ->addDataset('bounced', 'GAUGE', 0)
    ->addDataset('rejected', 'GAUGE', 0)
    ->addDataset('rejectw', 'GAUGE', 0)
    ->addDataset('held', 'GAUGE', 0)
    ->addDataset('discarded', 'GAUGE', 0)
    ->addDataset('bytesr', 'GAUGE', 0)
    ->addDataset('bytesd', 'GAUGE', 0)
    ->addDataset('senders', 'GAUGE', 0)
    ->addDataset('sendinghd', 'GAUGE', 0)
    ->addDataset('recipients', 'GAUGE', 0)
    ->addDataset('recipienthd', 'GAUGE', 0)
    ->addDataset('deferralcr', 'GAUGE', 0)
    ->addDataset('deferralhid', 'GAUGE', 0)
    ->addDataset('chr', 'GAUGE', 0)
    ->addDataset('hcrnfqh', 'GAUGE', 0)
    ->addDataset('sardnf', 'GAUGE', 0)
    ->addDataset('sarnobu', 'GAUGE', 0)
    ->addDataset('bu', 'GAUGE', 0)
    ->addDataset('raruu', 'GAUGE', 0)
    ->addDataset('hcrin', 'GAUGE', 0)
    ->addDataset('sarnfqa', 'GAUGE', 0)
    ->addDataset('rardnf', 'GAUGE', 0)
    ->addDataset('rarnfqa', 'GAUGE', 0)
    ->addDataset('iuscp', 'GAUGE', 0)
    ->addDataset('sce', 'GAUGE', 0)
    ->addDataset('scp', 'GAUGE', 0)
    ->addDataset('urr', 'GAUGE', 0);

$fields = [
    'incomingq' => $incomingq,
    'activeq' => $activeq,
    'deferredq' => $deferredq,
    'holdq' => $holdq,
    'received' => $received,
    'delivered' => $delivered,
    'forwarded' => $forwarded,
    'deferred' => $deferred,
    'bounced' => $bounced,
    'rejected' => $rejected,
    'rejectw' => $rejectw,
    'held' => $held,
    'discarded' => $discarded,
    'bytesr' => $bytesr,
    'bytesd' => $bytesd,
    'senders' => $senders,
    'sendinghd' => $sendinghd,
    'recipients' => $recipients,
    'recipienthd'=> $recipienthd,
    'deferralcr' => $deferralcr,
    'deferralhid' => $deferralhid,
    'chr' => $chr,
    'hcrnfqh' => $hcrnfqh,
    'sardnf' => $sardnf,
    'sarnobu' => $sarnobu,
    'bu' => $bu,
    'raruu' => $raruu,
    'hcrin' => $hcrin,
    'sarnfqa' => $sarnfqa,
    'rardnf' => $rardnf,
    'rarnfqa' => $rarnfqa,
    'iuscp' => $iuscp,
    'sce' => $sce,
    'scp' => $scp,
    'urr' => $urr,
];

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, $mailq, $fields);
