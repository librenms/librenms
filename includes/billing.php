<?php

#include("../../config.php");

function testPassword($id,$code){

#	include("config.php");

#	$query = mysql_query("SELECT count(bill_id) FROM bills WHERE bill_id = '$id' AND bill_code = '$code'");
#	if (mysql_result($query, 0)) {
#		return "1";
#	} elseif ($code == $master_code) {
		return "1";
#	}
#	else {
#		return "0";
#	}

}

function getDates($dayofmonth) {

	if ($dayofmonth < 10) { $dayofmonth = "0" . $dayofmonth; } 
	list($year, $month) = split('-', date('Y-m'));
	if (date('d') > $dayofmonth) {
		$newmonth = $month + 1;
		if($newmonth == 13) { 
			$newmonth = 1;
			$newyear = year + 1;
		} else {
			$newyear = $year;
		}
                $date_from = $year . $month . $dayofmonth;
		$date_to   = $newyear . $newmonth . $dayofmonth;
		$dt_q = mysql_query("SELECT DATE_ADD(DATE_SUB('$date_from', INTERVAL 1 DAY), INTERVAL 1 MONTH);");
		$date_to = mysql_result($dt_q,0);
		$date_to = str_replace("-","",$date_to);
	} else {
                $newmonth = $month - 1;
                if($newmonth == 0) {
                        $newmonth = 12;
                        $newyear = $year - 1;
		} else {
			$newyear = $year;	
		}
                $date_from = $newyear . $newmonth . $dayofmonth;
		$date_to   = $year . $month . $dayofmonth;
                $dt_q = mysql_query("SELECT DATE_ADD(DATE_SUB('$date_to', INTERVAL 1 MONTH), INTERVAL 1 DAY);");
                $date_from = mysql_result($dt_q,0);
                $date_from = str_replace("-","",$date_from);
	}
	$lq_from = mysql_query("SELECT DATE_SUB('$date_from', INTERVAL 1 MONTH);");
	$last_from = mysql_result($lq_from,0);
        $last_from = str_replace("-","",$last_from);	
	
	$lq_to   = mysql_query("SELECT DATE_SUB('$date_to', INTERVAL 1 MONTH);");
	$last_to = mysql_result($lq_to,0);
        $last_to = str_replace("-","",$last_to);	
	
	$return['0'] = $date_from . "000000";
	$return['1'] = $date_to . "235959";
	$return['2'] = $last_from . "000000";
	$return['3'] = $last_to . "235959";

	return($return);
}


function getValue($host, $community, $port, $id, $inout) {
	$oid  = "IF-MIB::ifHC" . $inout . "Octets." . $id;
        $value = `snmpget -c $community -v2c -O qv $host:$port $oid`;
        return $value;
}

function getIfName($host, $port, $id) {
	$oid = "IF-MIB::ifDescr." . $id;
	$value = `snmpget -c xyyz -v2c -O qv $host:$port $oid`;
	return $value;
}

function getLastPortCounter($port_id,$inout) {
  $query = mysql_query("SELECT count(counter) from port_" . $inout . "_measurements WHERE port_id=" . $port_id);
  $rows = mysql_result($query, 0);
  if($rows > 0) {
    $query = mysql_query("SELECT counter,delta FROM port_" . $inout . "_measurements WHERE port_id=$port_id ORDER BY timestamp DESC");
    $row = mysql_fetch_row($query);
    $return[counter] = $row[0];
    $return[delta] = $row[1];
    $return[state] = "ok";
  } else {
    $return[state] = "failed";
  }
  return($return);
}

function getLastMeasurement($bill_id) {
  $query = mysql_query("SELECT count(delta) from bill_data WHERE bill_id=" . $bill_id);
  $rows  = mysql_result($query, 0);
  if($rows > 0) {
    $query = mysql_query("SELECT timestamp,delta,in_delta,out_delta FROM bill_data WHERE bill_id=$bill_id ORDER BY timestamp DESC");  
    $row = mysql_fetch_row($query);
    $return[delta] = $row[1];
    $return[delta] = $row[2];
    $return[delta] = $row[3];
    $return[timestamp] = $row[0];
    $return[state] = "ok";
  } else {
    $return[state] = "failed";
  }
  return($return);
}

function get95thin($bill_id,$datefrom,$dateto){
	$mq_text 		= "SELECT count(delta) FROM bill_data";
	$mq_text        	= $mq_text . " WHERE bill_id = $bill_id";
	$mq_text 		= $mq_text . " AND timestamp > $datefrom AND timestamp <= $dateto"; 
	$m_query 		= mysql_query($mq_text);
  	$measurements 		= mysql_result($m_query,0);
	$measurement_95th 	= round($measurements /100 * 95) - 1;
	$q_95_text 		=              "SELECT in_delta FROM bill_data  WHERE bill_id = $bill_id";
	$q_95_text		= $q_95_text . " AND timestamp > $datefrom AND timestamp <= $dateto ORDER BY in_delta ASC";
	$q_95th			= mysql_query($q_95_text);
	$m_95th 		= mysql_result($q_95th,$measurement_95th);
	return(round($m_95th / 1000 / 300 * 8, 2));
}

function get95thout($bill_id,$datefrom,$dateto){
	$mq_text 		= "SELECT count(delta) FROM bill_data ";
	$mq_text        	= $mq_text . " WHERE bill_id = $bill_id";
	$mq_text 		= $mq_text . " AND timestamp > $datefrom AND timestamp <= $dateto"; 
	$m_query 		= mysql_query($mq_text);
  	$measurements 		= mysql_result($m_query,0);
	$measurement_95th 	= round($measurements /100 * 95) - 1;
	$q_95_text 		=              "SELECT out_delta FROM bill_data  WHERE bill_id = $bill_id";
	$q_95_text		= $q_95_text . " AND timestamp > $datefrom AND timestamp <= $dateto ORDER BY out_delta ASC";
	$q_95th			= mysql_query($q_95_text);
	$m_95th 		= mysql_result($q_95th,$measurement_95th);
	return(round($m_95th / 1000 / 300 * 8, 2));
}

function getRates($bill_id,$datefrom,$dateto) {
	$mq_text 		= "SELECT count(delta) FROM bill_data ";
	$mq_text        	= $mq_text . " WHERE bill_id = $bill_id";
	$mq_text 		= $mq_text . " AND timestamp > $datefrom AND timestamp <= $dateto"; 
	$m_query 		= mysql_query($mq_text);
	$measurements 		= mysql_result($m_query,0);
	$measurement_95th 	= round($measurements /100 * 95) - 1;
	$q_95_text 		= "SELECT delta FROM bill_data  WHERE bill_id = $bill_id";
	$q_95_text		= $q_95_text . " AND timestamp > $datefrom AND timestamp <= $dateto ORDER BY delta ASC";
	$q_95th			= mysql_query($q_95_text);
	$m_95th 		= mysql_result($q_95th,$measurement_95th);
        $mt_q 			= mysql_query("SELECT sum(delta) FROM bill_data  WHERE bill_id = $bill_id AND timestamp > $datefrom AND timestamp <= $dateto");
        $mtot 			= mysql_result($mt_q,0);
	$data['rate_95th_in'] = get95thIn($bill_id,$datefrom,$dateto);
	$data['rate_95th_out'] = get95thOut($bill_id,$datefrom,$dateto);
	if ($data['rate_95th_out'] > $data['rate_95th_in']) {
		 $data['rate_95th'] 	= $data['rate_95th_out'];
		 $data['dir_95th'] = 'out';
  } else {
		$data['rate_95th'] 	= $data['rate_95th_in'];
		$data['dir_95th'] = 'in';
	}
	$data['total_data'] 	= round($mtot / 1000 / 1000, 2);
	$data['rate_average'] 	= round($mtot / $measurements / 1000 / 300 * 8, 2);
	return($data);
}

function getTotal($bill_id,$datefrom,$dateto) {
	$mt_q = mysql_query("SELECT sum(delta) FROM bill_data  WHERE bill_id = $bill_id AND timestamp > $datefrom AND timestamp <= $dateto");
	$mtot = mysql_result($mt_q,0);
	return($mtot);
}

$dayofmonth     = date("j");

?>
