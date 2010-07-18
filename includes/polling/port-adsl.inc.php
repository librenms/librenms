<?php

    if($array[$device[device_id]][$port[ifIndex]] && $port['ifType'] == "adsl" && $array[$device[device_id]][$port[ifIndex]]['adslLineCoding']) { // Check to make sure Port data is cached.

      $this_port = &$array[$device[device_id]][$port[ifIndex]];

      $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("port-".$port['ifIndex']."-adsl.rrd");

      $rrd_create  = " --step 300";
      $rrd_create .= " DS:AtucCurrSnrMgn:GAUGE:600:0:U";
      $rrd_create .= " DS:AtucCurrAtn:GAUGE:600:0:U";
      $rrd_create .= " DS:AtucCurrOutputPwr:GAUGE:600:0:U";
      $rrd_create .= " DS:AtucCurrAttainableR:GAUGE:600:0:U";
      $rrd_create .= " DS:AtucChanCurrTxRate:GAUGE:600:0:U";
      $rrd_create .= " DS:AturCurrSnrMgn:GAUGE:600:0:U";
      $rrd_create .= " DS:AturCurrAtn:GAUGE:600:0:U";
      $rrd_create .= " DS:AturCurrOutputPwr:GAUGE:600:0:U";
      $rrd_create .= " DS:AturCurrAttainableR:GAUGE:600:0:U";
      $rrd_create .= " DS:AturChanCurrTxRate:GAUGE:600:0:U";
      $rrd_create .= " DS:AtucPerfLofs:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AtucPerfLoss:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AtucPerfLprs:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AtucPerfESs:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AtucPerfInits:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AturPerfLofs:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AturPerfLoss:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AturPerfLprs:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AturPerfESs:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AtucChanCorrectedBl:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AtucChanUncorrectBl:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AturChanCorrectedBl:COUNTER:600:U:100000000000";
      $rrd_create .= " DS:AturChanUncorrectBl:COUNTER:600:U:100000000000";
      $rrd_create .= " RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797";
      $rrd_create .= " RRA:MIN:0.5:1:600 RRA:MIN:0.5:6:700 RRA:MIN:0.5:24:775 RRA:MIN:0.5:288:797 ";
      $rrd_create .= " RRA:MAX:0.5:1:600 RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797 ";

	#ADSL-LINE-MIB::adslLineCoding.11 = INTEGER: dmt(2)
	#ADSL-LINE-MIB::adslLineType.11 = INTEGER: fastOnly(2)
	#ADSL-LINE-MIB::adslAtucInvVendorID.11 = STRING: "GSPN"
	#ADSL-LINE-MIB::adslAtucInvVersionNumber.11 = STRING: "8"
	#ADSL-LINE-MIB::adslAtucCurrSnrMgn.11 = Gauge32: 90 tenth dB
	#ADSL-LINE-MIB::adslAtucCurrAtn.11 = Gauge32: 125 tenth dB
	#ADSL-LINE-MIB::adslAtucCurrOutputPwr.11 = Gauge32: 185 tenth dBm
	#ADSL-LINE-MIB::adslAtucCurrAttainableRate.11 = Gauge32: 9792000 bps
	#ADSL-LINE-MIB::adslAturInvSerialNumber.11 = ""
	#ADSL-LINE-MIB::adslAturInvVendorID.11 = STRING: "STMI"
	#ADSL-LINE-MIB::adslAturInvVersionNumber.11 = STRING: "0"

      $adsl_oids = array('AtucCurrSnrMgn','AtucCurrAtn','AtucCurrOutputPwr','AtucCurrAttainableRate','AtucChanCurrTxRate','AturCurrSnrMgn','AturCurrAtn','AturCurrOutputPwr','AturCurrAttainableRate','AturChanCurrTxRate','AtucPerfLofs','AtucPerfLoss','AtucPerfLprs','AtucPerfESs','AtucPerfInits','AturPerfLofs','AturPerfLoss','AturPerfLprs','AturPerfESs','AtucChanCorrectedBlks','AtucChanUncorrectBlks','AturChanCorrectedBlks','AturChanUncorrectBlks');

      $adsl_db_oids = array('adslLineCoding','adslLineType','adslAtucInvVendorID','adslAtucInvVersionNumber','adslAtucCurrSnrMgn','adslAtucCurrAtn','adslAtucCurrOutputPwr','adslAtucCurrAttainableRate','adslAturInvSerialNumber','adslAturInvVendorID','adslAturInvVersionNumber');
 
      if(mysql_result(mysql_query("SELECT COUNT(*) FROM `ports_adsl` WHERE `interface_id` = '".$port['interface_id']."'"),0) == "0") { mysql_query("INSERT INTO `ports_adsl` (`interface_id`) VALUES ('".$port['interface_id']."')"); }
      $mysql_update = "UPDATE `ports_adsl` SET `port_adsl_updated` = NOW()";
      foreach($adsl_db_oids as $oid) {
        $data = str_replace("\"", "", $this_port[$oid]);
        $mysql_update .= ",`".$oid."` = '".$data."'";
      }      
      $mysql_update .= "WHERE `interface_id` = '".$port['interface_id']."'";
      mysql_query($mysql_update);

      if($debug) { echo($mysql_update); echo(mysql_affected_rows()); echo(mysql_error()); }


      $rrdupdate = "N";
      foreach($adsl_oids as $oid) {
        $oid = "adsl".$oid;
        $data = str_replace("\"", "", $this_port[$oid]) + 0;
        $rrdupdate .= ":$data";
      }

      if (!is_file($rrdfile)) { rrdtool_create ($rrdfile, $rrd_create); }
      rrdtool_update ($rrdfile, $rrdupdate);

      echo("ADSL ");

    }

?>
