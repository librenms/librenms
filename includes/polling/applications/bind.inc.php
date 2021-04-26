<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'bind';
$app_id = $app['app_id'];

echo " $name";

if (! empty($agent_data['app'][$name])) {
    $bind = $agent_data['app'][$name];
} else {
    $options = '-Oqv';
    $mib = 'NET-SNMP-EXTEND-MIB';
    $oid = 'nsExtendOutputFull.4.98.105.110.100';
    $bind = snmp_get($device, $oid, $options, $mib);
}

[$incoming, $outgoing, $server, $resolver, $cache, $rrsets, $adb, $sockets] = explode("\n", $bind);

//
// INCOMING PROCESSING
//
[$a, $aaaa, $afsdb, $apl, $caa, $cdnskey, $cds, $cert, $cname, $dhcid, $dlv, $dnskey, $ds, $ipseckey, $key, $kx,
    $loc, $mx, $naptr, $ns, $nsec, $nsec3, $nsec3param, $ptr, $rrsig, $rp, $sig, $soa, $srv, $sshfp, $ta, $tkey, $tlsa,
    $tsig, $txt, $uri, $dname, $any, $axfr, $ixfr, $opt, $spf] = explode(',', $incoming);

$metrics = [];
$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('any', 'DERIVE', 0)
    ->addDataset('a', 'DERIVE', 0)
    ->addDataset('aaaa', 'DERIVE', 0)
    ->addDataset('cname', 'DERIVE', 0)
    ->addDataset('mx', 'DERIVE', 0)
    ->addDataset('ns', 'DERIVE', 0)
    ->addDataset('ptr', 'DERIVE', 0)
    ->addDataset('soa', 'DERIVE', 0)
    ->addDataset('srv', 'DERIVE', 0)
    ->addDataset('spf', 'DERIVE', 0);

$fields = [
    'any'   => $any,
    'a'     => $a,
    'aaaa'  => $aaaa,
    'cname' => $cname,
    'mx'    => $mx,
    'ns'    => $ns,
    'ptr'   => $ptr,
    'soa'   => $soa,
    'srv'   => $srv,
    'spf'   => $spf,
];
$metrics['queries'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$rrd_name = ['app', $name, $app_id, 'incoming'];
$rrd_def = RrdDefinition::make()
    ->addDataset('afsdb', 'DERIVE', 0)
    ->addDataset('apl', 'DERIVE', 0)
    ->addDataset('caa', 'DERIVE', 0)
    ->addDataset('cdnskey', 'DERIVE', 0)
    ->addDataset('cds', 'DERIVE', 0)
    ->addDataset('cert', 'DERIVE', 0)
    ->addDataset('dhcid', 'DERIVE', 0)
    ->addDataset('dlv', 'DERIVE', 0)
    ->addDataset('dnskey', 'DERIVE', 0)
    ->addDataset('ds', 'DERIVE', 0)
    ->addDataset('ipseckey', 'DERIVE', 0)
    ->addDataset('key', 'DERIVE', 0)
    ->addDataset('kx', 'DERIVE', 0)
    ->addDataset('loc', 'DERIVE', 0)
    ->addDataset('naptr', 'DERIVE', 0)
    ->addDataset('nsec', 'DERIVE', 0)
    ->addDataset('nsec3', 'DERIVE', 0)
    ->addDataset('nsec3param', 'DERIVE', 0)
    ->addDataset('rrsig', 'DERIVE', 0)
    ->addDataset('rp', 'DERIVE', 0)
    ->addDataset('sig', 'DERIVE', 0)
    ->addDataset('sshfp', 'DERIVE', 0)
    ->addDataset('ta', 'DERIVE', 0)
    ->addDataset('tkey', 'DERIVE', 0)
    ->addDataset('tlsa', 'DERIVE', 0)
    ->addDataset('tsig', 'DERIVE', 0)
    ->addDataset('txt', 'DERIVE', 0)
    ->addDataset('uri', 'DERIVE', 0)
    ->addDataset('dname', 'DERIVE', 0)
    ->addDataset('axfr', 'DERIVE', 0)
    ->addDataset('ixfr', 'DERIVE', 0)
    ->addDataset('opt', 'DERIVE', 0);

$fields = [
    'afsdb' => $afsdb,
    'apl' => $apl,
    'caa' => $caa,
    'cdnskey' => $cdnskey,
    'cds' => $cds,
    'cert' => $cert,
    'dhcid' => $dhcid,
    'dlv' => $dlv,
    'dnskey' => $dnskey,
    'ds' => $ds,
    'ipseckey' => $ipseckey,
    'key' => $key,
    'kx' => $kx,
    'loc' => $loc,
    'naptr' => $naptr,
    'nsec' => $nsec,
    'nsec3' => $nsec3,
    'nsec3param' => $nsec3param,
    'rrsig' => $rrsig,
    'rp' => $rp,
    'sig' => $sig,
    'sshfp' => $sshfp,
    'ta' => $ta,
    'tkey' => $tkey,
    'tlsa' => $tlsa,
    'tsig' => $tsig,
    'txt' => $txt,
    'uri' => $uri,
    'dname' => $dname,
    'axfr' => $axfr,
    'ixfr' => $ixfr,
    'opt' => $opt,
];
$metrics['incoming'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// OUTGOING PROCESSING
//
[$a, $aaaa, $afsdb, $apl, $caa, $cdnskey, $cds, $cert, $cname, $dhcid, $dlv, $dnskey, $ds, $ipseckey, $key, $kx,
    $loc, $mx, $naptr, $ns, $nsec, $nsec3, $nsec3param, $ptr, $rrsig, $rp, $sig, $soa, $srv, $sshfp, $ta, $tkey, $tlsa,
    $tsig, $txt, $uri, $dname, $any, $axfr, $ixfr, $opt, $spf] = explode(',', $outgoing);

$rrd_name = ['app', $name, $app_id, 'outgoing'];
$rrd_def = RrdDefinition::make()
    ->addDataset('any', 'DERIVE', 0)
    ->addDataset('a', 'DERIVE', 0)
    ->addDataset('aaaa', 'DERIVE', 0)
    ->addDataset('cname', 'DERIVE', 0)
    ->addDataset('mx', 'DERIVE', 0)
    ->addDataset('ns', 'DERIVE', 0)
    ->addDataset('ptr', 'DERIVE', 0)
    ->addDataset('soa', 'DERIVE', 0)
    ->addDataset('srv', 'DERIVE', 0)
    ->addDataset('spf', 'DERIVE', 0)
    ->addDataset('afsdb', 'DERIVE', 0)
    ->addDataset('apl', 'DERIVE', 0)
    ->addDataset('caa', 'DERIVE', 0)
    ->addDataset('cdnskey', 'DERIVE', 0)
    ->addDataset('cds', 'DERIVE', 0)
    ->addDataset('cert', 'DERIVE', 0)
    ->addDataset('dhcid', 'DERIVE', 0)
    ->addDataset('dlv', 'DERIVE', 0)
    ->addDataset('dnskey', 'DERIVE', 0)
    ->addDataset('ds', 'DERIVE', 0)
    ->addDataset('ipseckey', 'DERIVE', 0)
    ->addDataset('key', 'DERIVE', 0)
    ->addDataset('kx', 'DERIVE', 0)
    ->addDataset('loc', 'DERIVE', 0)
    ->addDataset('naptr', 'DERIVE', 0)
    ->addDataset('nsec', 'DERIVE', 0)
    ->addDataset('nsec3', 'DERIVE', 0)
    ->addDataset('nsec3param', 'DERIVE', 0)
    ->addDataset('rrsig', 'DERIVE', 0)
    ->addDataset('rp', 'DERIVE', 0)
    ->addDataset('sig', 'DERIVE', 0)
    ->addDataset('sshfp', 'DERIVE', 0)
    ->addDataset('ta', 'DERIVE', 0)
    ->addDataset('tkey', 'DERIVE', 0)
    ->addDataset('tlsa', 'DERIVE', 0)
    ->addDataset('tsig', 'DERIVE', 0)
    ->addDataset('txt', 'DERIVE', 0)
    ->addDataset('uri', 'DERIVE', 0)
    ->addDataset('dname', 'DERIVE', 0)
    ->addDataset('axfr', 'DERIVE', 0)
    ->addDataset('ixfr', 'DERIVE', 0)
    ->addDataset('opt', 'DERIVE', 0);

$fields = [
    'any'   => $any,
    'a'     => $a,
    'aaaa'  => $aaaa,
    'cname' => $cname,
    'mx'    => $mx,
    'ns'    => $ns,
    'ptr'   => $ptr,
    'soa'   => $soa,
    'srv'   => $srv,
    'spf'   => $spf,
    'afsdb' => $afsdb,
    'apl' => $apl,
    'caa' => $caa,
    'cdnskey' => $cdnskey,
    'cds' => $cds,
    'cert' => $cert,
    'dhcid' => $dhcid,
    'dlv' => $dlv,
    'dnskey' => $dnskey,
    'ds' => $ds,
    'ipseckey' => $ipseckey,
    'key' => $key,
    'kx' => $kx,
    'loc' => $loc,
    'naptr' => $naptr,
    'nsec' => $nsec,
    'nsec3' => $nsec3,
    'nsec3param' => $nsec3param,
    'rrsig' => $rrsig,
    'rp' => $rp,
    'sig' => $sig,
    'sshfp' => $sshfp,
    'ta' => $ta,
    'tkey' => $tkey,
    'tlsa' => $tlsa,
    'tsig' => $tsig,
    'txt' => $txt,
    'uri' => $uri,
    'dname' => $dname,
    'axfr' => $axfr,
    'ixfr' => $ixfr,
    'opt' => $opt,
];
$metrics['outgoing'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// SERVER PROCESSING
//
[$i4rr, $i6rr, $rwer, $trr, $aqr, $rqr, $rs, $trs, $rwes, $qrisa, $qriaa, $qrinaa, $qrin, $qris, $qrind,
    $qcr, $dqr, $oqf, $uqr, $tqr, $oeor, $qd] = explode(',', $server);

$rrd_name = ['app', $name, $app_id, 'server'];
$rrd_def = RrdDefinition::make()
    ->addDataset('i4rr', 'DERIVE', 0)
    ->addDataset('i6rr', 'DERIVE', 0)
    ->addDataset('rwer', 'DERIVE', 0)
    ->addDataset('trr', 'DERIVE', 0)
    ->addDataset('aqr', 'DERIVE', 0)
    ->addDataset('rqr', 'DERIVE', 0)
    ->addDataset('rs', 'DERIVE', 0)
    ->addDataset('trs', 'DERIVE', 0)
    ->addDataset('rwes', 'DERIVE', 0)
    ->addDataset('qrisa', 'DERIVE', 0)
    ->addDataset('qriaa', 'DERIVE', 0)
    ->addDataset('qrinaa', 'DERIVE', 0)
    ->addDataset('qrin', 'DERIVE', 0)
    ->addDataset('qris', 'DERIVE', 0)
    ->addDataset('qrind', 'DERIVE', 0)
    ->addDataset('qcr', 'DERIVE', 0)
    ->addDataset('dqr', 'DERIVE', 0)
    ->addDataset('oqf', 'DERIVE', 0)
    ->addDataset('uqr', 'DERIVE', 0)
    ->addDataset('tqr', 'DERIVE', 0)
    ->addDataset('oeor', 'DERIVE', 0)
    ->addDataset('qd', 'DERIVE', 0);

$fields = [
    'i4rr' => $i4rr,
    'i6rr' => $i6rr,
    'rwer' => $rwer,
    'trr' => $trr,
    'aqr' => $aqr,
    'rqr' => $rqr,
    'rs' => $rs,
    'trs' => $trs,
    'rwes' => $rwes,
    'qrisa' => $qrisa,
    'qriaa' => $qriaa,
    'qrinaa' => $qrinaa,
    'qrin' => $qrin,
    'qris' => $qris,
    'qrind' => $qrind,
    'qcr' => $qcr,
    'dqr' => $dqr,
    'oqf' => $oqf,
    'uqr' => $uqr,
    'tqr' => $tqr,
    'oeor' => $oeor,
    'qd' => $qd,
];
$metrics['server'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// RESOLVER PROCESSING
//
[$i4qs, $i6qs, $i4rr, $i6rr, $nr, $sr, $fr, $eqf, $trr, $ldr, $qr, $qt, $i4naf, $i6naf,
    $i4naff, $i6naff, $rttl10, $rtt10t100, $rtt100t500, $rtt500t800, $rtt800t1600, $rttg1600,
    $bs, $rr] = explode(',', $resolver);

$rrd_name = ['app', $name, $app_id, 'resolver'];
$rrd_def = RrdDefinition::make()
    ->addDataset('i4qs', 'DERIVE', 0)
    ->addDataset('i6qs', 'DERIVE', 0)
    ->addDataset('i4rr', 'DERIVE', 0)
    ->addDataset('i6rr', 'DERIVE', 0)
    ->addDataset('nr', 'DERIVE', 0)
    ->addDataset('sr', 'DERIVE', 0)
    ->addDataset('fr', 'DERIVE', 0)
    ->addDataset('eqf', 'DERIVE', 0)
    ->addDataset('trr', 'DERIVE', 0)
    ->addDataset('ldr', 'DERIVE', 0)
    ->addDataset('qr', 'DERIVE', 0)
    ->addDataset('qt', 'DERIVE', 0)
    ->addDataset('i4naf', 'DERIVE', 0)
    ->addDataset('i6naf', 'DERIVE', 0)
    ->addDataset('i4naff', 'DERIVE', 0)
    ->addDataset('i6naff', 'DERIVE', 0)
    ->addDataset('rttl10', 'DERIVE', 0)
    ->addDataset('rtt10t100', 'DERIVE', 0)
    ->addDataset('rtt100t500', 'DERIVE', 0)
    ->addDataset('rtt500t800', 'DERIVE', 0)
    ->addDataset('rtt800t1600', 'DERIVE', 0)
    ->addDataset('rttg1600', 'DERIVE', 0)
    ->addDataset('bs', 'GAUGE', 0)
    ->addDataset('rr', 'DERIVE', 0);

$fields = [
    'i4qs' => $i4qs,
    'i6qs' => $i6qs,
    'i4rr' => $i4rr,
    'i6rr' => $i6rr,
    'nr' => $nr,
    'sr' => $sr,
    'fr' => $fr,
    'eqf' => $eqf,
    'trr' => $trr,
    'ldr' => $ldr,
    'qr' => $qr,
    'qt' => $qt,
    'i4naf' => $i4naf,
    'i6naf' => $i6naf,
    'i4naff' => $i4naff,
    'i6naff' => $i6naff,
    'rttl10' => $rttl10,
    'rtt10t100' => $rtt10t100,
    'rtt100t500' => $rtt100t500,
    'rtt500t800' => $rtt500t800,
    'rtt800t1600' => $rtt800t1600,
    'rttg1600' => $rttg1600,
    'bs' => $bs,
    'rr' => $rr,
];
$metrics['resolver'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// CACHE PROCESSING
//
[$ch, $cm, $chfq, $cmfq, $crddtme, $crddtte, $cdn, $cdhb, $ctmt, $ctmiu, $cthmiu,
    $chmt, $chmiu, $chhmiu] = explode(',', $cache);

$rrd_name = ['app', $name, $app_id, 'cache'];
$rrd_def = RrdDefinition::make()
    ->addDataset('ch', 'DERIVE', 0)
    ->addDataset('cm', 'DERIVE', 0)
    ->addDataset('chfq', 'DERIVE', 0)
    ->addDataset('cmfq', 'DERIVE', 0)
    ->addDataset('crddtme', 'DERIVE', 0)
    ->addDataset('crddtte', 'DERIVE', 0)
    ->addDataset('cdn', 'GAUGE', 0)
    ->addDataset('cdhb', 'GAUGE', 0)
    ->addDataset('ctmt', 'GAUGE', 0)
    ->addDataset('ctmiu', 'GAUGE', 0)
    ->addDataset('cthmiu', 'GAUGE', 0)
    ->addDataset('chmt', 'GAUGE', 0)
    ->addDataset('chmiu', 'GAUGE', 0)
    ->addDataset('chhmiu', 'GAUGE', 0);

$fields = [
    'ch' => $ch,
    'cm' => $cm,
    'chfq' => $chfq,
    'cmfq' => $cmfq,
    'crddtme' => $crddtme,
    'crddtte' => $crddtte,
    'cdn' => $cdn,
    'cdhb' => $cdhb,
    'ctmt' => $ctmt,
    'ctmiu' => $ctmiu,
    'cthmiu' => $cthmiu,
    'chmt' => $chmt,
    'chmiu' => $chmiu,
    'chhmiu' => $chhmiu,
];
$metrics['cache'] = $fields;

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

//
// ADB PROCESSING
//
[$ahts, $aiht, $nhts, $niht] = explode(',', $adb);

$rrd_name = ['app', $name, $app_id, 'adb'];
$rrd_def = RrdDefinition::make()
    ->addDataset('ahts', 'GAUGE', 0)
    ->addDataset('aiht', 'GAUGE', 0)
    ->addDataset('nhts', 'GAUGE', 0)
    ->addDataset('niht', 'GAUGE', 0);

$fields = [
    'ahts' => $ahts,
    'aiht' => $aiht,
    'nhts' => $nhts,
    'niht' => $niht,
];
$metrics['adb'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// SOCKETS PROCESSING
//
[$ui4so, $ui6so, $ti4so, $ti6so, $rso, $ui4sc, $ui6sc, $ti4sc, $ti6sc, $ui4sbf, $ti4sbf, $ui6sbf, $ti6sbf,
    $ui4scf, $ti4scf, $ui6scf, $ti6scf, $ui4ce, $ti4ce, $ui6ce, $ti6ce, $ti4ca, $ti6ca, $ui4se, $ti4se, $ui6se,
    $ti6se, $ui4re, $ti4re, $ui6re, $ti6re, $ui4sa, $ui6sa, $ti4sa, $ti6sa, $rsa] = explode(',', $sockets);

$rrd_name = ['app', $name, $app_id, 'sockets'];
$rrd_def = RrdDefinition::make()
    ->addDataset('ui4so', 'DERIVE', 0)
    ->addDataset('ui6so', 'DERIVE', 0)
    ->addDataset('ti4so', 'DERIVE', 0)
    ->addDataset('ti6so', 'DERIVE', 0)
    ->addDataset('rso', 'DERIVE', 0)
    ->addDataset('ui4sc', 'DERIVE', 0)
    ->addDataset('ui6sc', 'DERIVE', 0)
    ->addDataset('ti4sc', 'DERIVE', 0)
    ->addDataset('ti6sc', 'DERIVE', 0)
    ->addDataset('ui4sbf', 'DERIVE', 0)
    ->addDataset('ti4sbf', 'DERIVE', 0)
    ->addDataset('ui6sbf', 'DERIVE', 0)
    ->addDataset('ti6sbf', 'DERIVE', 0)
    ->addDataset('ui4scf', 'DERIVE', 0)
    ->addDataset('ti4scf', 'DERIVE', 0)
    ->addDataset('ui6scf', 'DERIVE', 0)
    ->addDataset('ti6scf', 'DERIVE', 0)
    ->addDataset('ui4ce', 'DERIVE', 0)
    ->addDataset('ti4ce', 'DERIVE', 0)
    ->addDataset('ui6ce', 'DERIVE', 0)
    ->addDataset('ti6ce', 'DERIVE', 0)
    ->addDataset('ti4ca', 'DERIVE', 0)
    ->addDataset('ti6ca', 'DERIVE', 0)
    ->addDataset('ui4se', 'DERIVE', 0)
    ->addDataset('ti4se', 'DERIVE', 0)
    ->addDataset('ui6se', 'DERIVE', 0)
    ->addDataset('ti6se', 'DERIVE', 0)
    ->addDataset('ui4re', 'DERIVE', 0)
    ->addDataset('ti4re', 'DERIVE', 0)
    ->addDataset('ui6re', 'DERIVE', 0)
    ->addDataset('ti6re', 'DERIVE', 0)
    ->addDataset('ui4sa', 'GAUGE', 0)
    ->addDataset('ui6sa', 'GAUGE', 0)
    ->addDataset('ti4sa', 'GAUGE', 0)
    ->addDataset('ti6sa', 'GAUGE', 0)
    ->addDataset('rsa', 'GAUGE', 0);

$fields = [
    'ui4so' => $ui4so,
    'ui6so' => $ui6so,
    'ti4so' => $ti4so,
    'ti6so' => $ti6so,
    'rso' => $rso,
    'ui4sc' => $ui4sc,
    'ui6sc' => $ui6sc,
    'ti4sc' => $ti4sc,
    'ti6sc' => $ti6sc,
    'ui4sbf' => $ui4sbf,
    'ti4sbf' => $ti4sbf,
    'ui6sbf' => $ui6sbf,
    'ti6sbf' => $ti6sbf,
    'ui4scf' => $ui4scf,
    'ti4scf' => $ti4scf,
    'ui6scf' => $ui6scf,
    'ti6scf' => $ti6scf,
    'ui4ce' => $ui4ce,
    'ti4ce' => $ti4ce,
    'ui6ce' => $ui6ce,
    'ti6ce' => $ti6ce,
    'ti4ca' => $ti4ca,
    'ti6ca' => $ti6ca,
    'ui4se' => $ui4se,
    'ti4se' => $ti4se,
    'ui6se' => $ui6se,
    'ti6se' => $ti6se,
    'ui4re' => $ui4re,
    'ti4re' => $ti4re,
    'ui6re' => $ui6re,
    'ti6re' => $ti6re,
    'ui4sa' => $ui4sa,
    'ui6sa' => $ui6sa,
    'ti4sa' => $ti4sa,
    'ti6sa' => $ti6sa,
    'rsa' => $ti6sa,
];
$metrics['sockets'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

//
// RR SETS PROCESSING
//
[$a, $aaaa, $afsdb, $apl, $caa, $cdnskey, $cds, $cert, $cname, $dhcid, $dlv, $dnskey, $ds, $ipseckey, $key, $kx,
    $loc, $mx, $naptr, $ns, $nsec, $nsec3, $nsec3param, $ptr, $rrsig, $rp, $sig, $soa, $srv, $sshfp, $ta, $tkey, $tlsa,
    $tsig, $txt, $uri, $dname, $nxdomain, $any, $axfr, $ixfr, $opt, $spf, $nota, $notaaaa, $notafsdb, $notapl, $notcaa, $notcdnskey,
    $notcds, $notcert, $notcname, $notdhcid, $notdlv, $notdnskey, $notds, $notipseckey, $notkey, $notkx, $notloc, $notmx,
    $notnaptr, $notns, $notnsec, $notnsec3, $notnsec3param, $notptr, $notrrsig, $notrp, $notsig, $notsoa, $notsrv,
    $notsshfp, $notta, $nottkey, $nottlsa, $nottsig, $nottxt, $noturi, $notdname, $notnxdomain, $notany, $notaxfr, $notixfr, $notopt,
    $notspf] = explode(',', $rrsets);

$rrd_def = RrdDefinition::make()
    ->addDataset('any', 'GAUGE', 0)
    ->addDataset('a', 'GAUGE', 0)
    ->addDataset('aaaa', 'GAUGE', 0)
    ->addDataset('cname', 'GAUGE', 0)
    ->addDataset('mx', 'GAUGE', 0)
    ->addDataset('ns', 'GAUGE', 0)
    ->addDataset('ptr', 'GAUGE', 0)
    ->addDataset('soa', 'GAUGE', 0)
    ->addDataset('srv', 'GAUGE', 0)
    ->addDataset('spf', 'GAUGE', 0)
    ->addDataset('afsdb', 'GAUGE', 0)
    ->addDataset('apl', 'GAUGE', 0)
    ->addDataset('caa', 'GAUGE', 0)
    ->addDataset('cdnskey', 'GAUGE', 0)
    ->addDataset('cds', 'GAUGE', 0)
    ->addDataset('cert', 'GAUGE', 0)
    ->addDataset('dhcid', 'GAUGE', 0)
    ->addDataset('dlv', 'GAUGE', 0)
    ->addDataset('dnskey', 'GAUGE', 0)
    ->addDataset('ds', 'GAUGE', 0)
    ->addDataset('ipseckey', 'GAUGE', 0)
    ->addDataset('key', 'GAUGE', 0)
    ->addDataset('kx', 'GAUGE', 0)
    ->addDataset('loc', 'GAUGE', 0)
    ->addDataset('naptr', 'GAUGE', 0)
    ->addDataset('nsec', 'GAUGE', 0)
    ->addDataset('nsec3', 'GAUGE', 0)
    ->addDataset('nsec3param', 'GAUGE', 0)
    ->addDataset('rrsig', 'GAUGE', 0)
    ->addDataset('rp', 'GAUGE', 0)
    ->addDataset('sig', 'GAUGE', 0)
    ->addDataset('sshfp', 'GAUGE', 0)
    ->addDataset('ta', 'GAUGE', 0)
    ->addDataset('tkey', 'GAUGE', 0)
    ->addDataset('tlsa', 'GAUGE', 0)
    ->addDataset('tsig', 'GAUGE', 0)
    ->addDataset('txt', 'GAUGE', 0)
    ->addDataset('uri', 'GAUGE', 0)
    ->addDataset('dname', 'GAUGE', 0)
    ->addDataset('nxdomain', 'GAUGE', 0)
    ->addDataset('axfr', 'GAUGE', 0)
    ->addDataset('ixfr', 'GAUGE', 0)
    ->addDataset('opt', 'GAUGE', 0);

//first handle the positive
$rrd_name = ['app', $name, $app_id, 'rrpositive'];

$fields = [
    'any'   => $any,
    'a'     => $a,
    'aaaa'  => $aaaa,
    'cname' => $cname,
    'mx'    => $mx,
    'ns'    => $ns,
    'ptr'   => $ptr,
    'soa'   => $soa,
    'srv'   => $srv,
    'spf'   => $spf,
    'afsdb' => $afsdb,
    'apl' => $apl,
    'caa' => $caa,
    'cdnskey' => $cdnskey,
    'cds' => $cds,
    'cert' => $cert,
    'dhcid' => $dhcid,
    'dlv' => $dlv,
    'dnskey' => $dnskey,
    'ds' => $ds,
    'ipseckey' => $ipseckey,
    'key' => $key,
    'kx' => $kx,
    'loc' => $loc,
    'naptr' => $naptr,
    'nsec' => $nsec,
    'nsec3' => $nsec3,
    'nsec3param' => $nsec3param,
    'rrsig' => $rrsig,
    'rp' => $rp,
    'sig' => $sig,
    'sshfp' => $sshfp,
    'ta' => $ta,
    'tkey' => $tkey,
    'tlsa' => $tlsa,
    'tsig' => $tsig,
    'txt' => $txt,
    'uri' => $uri,
    'dname' => $dname,
    'nxdomain' => $nxdomain,
    'axfr' => $axfr,
    'ixfr' => $ixfr,
    'opt' => $opt,
];
$metrics['rrpositive'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

// now handle the negative
$rrd_name = ['app', $name, $app_id, 'rrnegative'];

$fields = [
    'any'   => $notany,
    'a'     => $nota,
    'aaaa'  => $notaaaa,
    'cname' => $notcname,
    'mx'    => $notmx,
    'ns'    => $notns,
    'ptr'   => $notptr,
    'soa'   => $notsoa,
    'srv'   => $notsrv,
    'spf'   => $notspf,
    'afsdb' => $notafsdb,
    'apl' => $notapl,
    'caa' => $notcaa,
    'cdnskey' => $notcdnskey,
    'cds' => $notcds,
    'cert' => $notcert,
    'dhcid' => $notdhcid,
    'dlv' => $notdlv,
    'dnskey' => $notdnskey,
    'ds' => $notds,
    'ipseckey' => $notipseckey,
    'key' => $notkey,
    'kx' => $notkx,
    'loc' => $notloc,
    'naptr' => $notnaptr,
    'nsec' => $notnsec,
    'nsec3' => $notnsec3,
    'nsec3param' => $notnsec3param,
    'rrsig' => $notrrsig,
    'rp' => $notrp,
    'sig' => $notsig,
    'sshfp' => $notsshfp,
    'ta' => $notta,
    'tkey' => $nottkey,
    'tlsa' => $nottlsa,
    'tsig' => $nottsig,
    'txt' => $nottxt,
    'uri' => $noturi,
    'dname' => $notdname,
    'nxdomain'=> $notnxdomain,
    'axfr' => $notaxfr,
    'ixfr' => $notixfr,
    'opt' => $notopt,
];
$metrics['rrnegative'] = $fields;

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, $bind, $metrics);
