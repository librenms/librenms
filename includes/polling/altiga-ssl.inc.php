<?php

if ($device['os'] == "asa" || $device['os'] == "pix")
{
  echo("ALTIGA-MIB SSL VPN Statistics \n");

  $oids =   array('alSslStatsTotalSessions','alSslStatsActiveSessions','alSslStatsMaxSessions','alSslStatsPreDecryptOctets',
                  'alSslStatsPostDecryptOctets','alSslStatsPreEncryptOctets','alSslStatsPostEncryptOctets');

  unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);

  $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("altiga-ssl.rrd");

  $rrd_create  = " RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600";
  $rrd_create .= " RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";
  $rrd_create .= " DS:TotalSessions:COUNTER:600:U:100000 DS:ActiveSessions:GAUGE:600:0:U DS:MaxSessions:GAUGE:600:0:U";
  $rrd_create .= " DS:PreDecryptOctets:COUNTER:600:U:100000000000 DS:PostDecryptOctets:COUNTER:600:U:100000000000 DS:PreEncryptOctets:COUNTER:600:U:100000000000";
  $rrd_create .= " DS:PostEncryptOctets:COUNTER:600:U:100000000000";

  if (!file_exists($rrdfile))
  {
    rrdtool_create($rrdfile, $rrd_create);
  }

  $data_array = snmpwalk_cache_oid($device, $proto, array(), "ALTIGA-SSL-STATS-MIB");

  $rrdupdate = "N";

  foreach ($oids as $oid)
  {
    if (is_numeric($data_array[0][$oid]))
    {
       $value = $data_array[0][$oid];
    } else {
      $value = "0";
    }
    $rrdupdate .= ":$value";
  }

  if ($data_array[0]['alSslStatsTotalSessions'] || is_file($rrdfile))
  {
    rrdtool_update($rrdfile, $rrdupdate);
  }

  unset($rrdfile, $rrdupdate, $data_array);
}

?>