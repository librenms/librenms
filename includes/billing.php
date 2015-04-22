<?php

function format_bytes_billing($value)
{
  global $config;

  return format_number($value, $config['billing']['base'])."B";
}

function format_bytes_billing_short($value)
{
  global $config;

  return format_number($value, $config['billing']['base'], 2, 3);
}

function getDates($dayofmonth, $months=0)
{
  $dayofmonth = zeropad($dayofmonth);
  $year = date('Y');
  $month = date('m');

  if (date('d') > $dayofmonth) // Billing day is past, so it is next month
  {
    $date_end   = date_create($year.'-'.$month.'-'.$dayofmonth);
    $date_start = date_create($year.'-'.$month.'-'.$dayofmonth);
    date_add($date_end,   date_interval_create_from_date_string('1 month'));
  } else { // Billing day will happen this month, therefore started last month
    $date_end   = date_create($year.'-'.$month.'-'.$dayofmonth);
    $date_start = date_create($year.'-'.$month.'-'.$dayofmonth);
    date_sub($date_start, date_interval_create_from_date_string('1 month'));
  }

  if ($months > 0)
  {
    date_sub($date_start, date_interval_create_from_date_string($months.' month'));
    date_sub($date_end,   date_interval_create_from_date_string($months.' month'));
  }

#  date_sub($date_start, date_interval_create_from_date_string('1 month'));
  date_sub($date_end, date_interval_create_from_date_string('1 day'));

  $date_from = date_format($date_start, 'Ymd') . "000000";
  $date_to   = date_format($date_end, 'Ymd') . "235959";

  date_sub($date_start, date_interval_create_from_date_string('1 month'));
  date_sub($date_end, date_interval_create_from_date_string('1 month'));

  $last_from = date_format($date_start, 'Ymd') . "000000";
  $last_to   = date_format($date_end, 'Ymd') . "235959";

  $return = array();
  $return['0'] = $date_from;
  $return['1'] = $date_to;
  $return['2'] = $last_from;
  $return['3'] = $last_to;

  return($return);
}

function getValue($host, $port, $id, $inout)
{
  global $config;

  $oid  = "IF-MIB::ifHC" . $inout . "Octets." . $id;
  $device = dbFetchRow("SELECT * from `devices` WHERE `hostname` = '".mres($host)."' LIMIT 1");
  $value = snmp_get($device, $oid, "-O qv");

  if (!is_numeric($value))
  {
    $oid  = "IF-MIB::if" . $inout . "Octets." . $id;
    $value = snmp_get($device, $oid, "-Oqv");
  }

  return $value;
}

function getLastPortCounter($port_id,$inout)
{
  $return = array();
  $rows = dbFetchCell("SELECT count(counter) from `port_" . mres($inout) . "_measurements` WHERE `port_id`='" . mres($port_id)."'");

  if ($rows > 0)
  {
    $row = dbFetchRow("SELECT counter,delta FROM `port_".mres($inout)."_measurements` WHERE `port_id`='".mres($port_id)."' ORDER BY timestamp DESC");
    $return[counter] = $row['counter'];
    $return[delta] = $row['delta'];
    $return[state] = "ok";
  } else {
    $return[state] = "failed";
  }
  return($return);
}

function getLastMeasurement($bill_id)
{
  $return = array();
  $rows = dbFetchCell("SELECT count(delta) from bill_data WHERE bill_id='".mres($bill_id)."'");

  if ($rows > 0)
  {
    $row = dbFetchRow("SELECT timestamp,delta,in_delta,out_delta FROM bill_data WHERE bill_id='".mres($bill_id)."' ORDER BY timestamp DESC");
    $return[delta]     = $row['delta'];
    $return[delta_in]  = $row['delta_in'];
    $return[delta_out] = $row['delta_out'];
    $return[timestamp] = $row['timestamp'];
    $return[state] = "ok";
  } else {
    $return[state] = "failed";
  }

  return($return);
}

function get95thin($bill_id,$datefrom,$dateto)
{
  $mq_sql = "SELECT count(delta) FROM bill_data WHERE bill_id = '".mres($bill_id)."'";
  $mq_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'";
  $measurements = dbFetchCell($mq_sql);
  $measurement_95th = round($measurements /100 * 95) - 1;

  $q_95_sql = "SELECT (in_delta / period * 8) AS rate FROM bill_data  WHERE bill_id = '".mres($bill_id)."'";
  $q_95_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."' ORDER BY rate ASC";
  $a_95th = dbFetchColumn($q_95_sql);
  $m_95th = $a_95th[$measurement_95th];

  return(round($m_95th, 2));
}

function get95thout($bill_id,$datefrom,$dateto)
{
  $mq_sql = "SELECT count(delta) FROM bill_data WHERE bill_id = '".mres($bill_id)."'";
  $mq_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'";
  $measurements = dbFetchCell($mq_sql);
  $measurement_95th = round($measurements /100 * 95) - 1;

  $q_95_sql = "SELECT (out_delta / period * 8) AS rate FROM bill_data  WHERE bill_id = '".mres($bill_id)."'";
  $q_95_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."' ORDER BY rate ASC";

  $a_95th = dbFetchColumn($q_95_sql);
  $m_95th = $a_95th[$measurement_95th];

  return(round($m_95th, 2));
}

function getRates($bill_id,$datefrom,$dateto)
{
  $data = array();
  $mq_text = "SELECT count(delta) FROM bill_data ";
  $mq_text .= " WHERE bill_id = '".mres($bill_id)."'";
  $mq_text .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'";
  $measurements = dbFetchCell($mq_sql);
  $measurement_95th = round($measurements /100 * 95) - 1;

  $q_95_sql = "SELECT delta FROM bill_data  WHERE bill_id = '".mres($bill_id)."'";
  $q_95_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."' ORDER BY delta ASC";

  $a_95th = dbFetchColumn($q_95_sql);
  $m_95th = $a_95th[$measurement_95th];

  $sum_data = getSum($bill_id,$datefrom,$dateto);
  $mtot = $sum_data['total'];
  $mtot_in = $sum_data['inbound'];
  $mtot_out = $sum_data['outbound'];
  $ptot = $sum_data['period'];

  $data['rate_95th_in'] = get95thIn($bill_id,$datefrom,$dateto);
  $data['rate_95th_out'] = get95thOut($bill_id,$datefrom,$dateto);

  if ($data['rate_95th_out'] > $data['rate_95th_in'])
  {
    $data['rate_95th'] = $data['rate_95th_out'];
    $data['dir_95th'] = 'out';
  } else {
    $data['rate_95th'] = $data['rate_95th_in'];
    $data['dir_95th'] = 'in';
  }

  $data['total_data']      = $mtot;
  $data['total_data_in']   = $mtot_in;
  $data['total_data_out']  = $mtot_out;
  $data['rate_average']    = $mtot / $ptot * 8;

#  print_r($data);

  return($data);
}

function getTotal($bill_id,$datefrom,$dateto)
{
  $mtot = dbFetchCell("SELECT SUM(delta) FROM bill_data WHERE bill_id = '".mres($bill_id)."' AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'");
  return($mtot);
}

function getSum($bill_id,$datefrom,$dateto)
{
  $sum = dbFetchRow("SELECT SUM(period) as period, SUM(delta) as total, SUM(in_delta) as inbound, SUM(out_delta) as outbound FROM bill_data WHERE bill_id = '".mres($bill_id)."' AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'");
  return($sum);
}

function getPeriod($bill_id,$datefrom,$dateto)
{
  $ptot = dbFetchCell("SELECT SUM(period) FROM bill_data WHERE bill_id = '".mres($bill_id)."' AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'");
  return($ptot);
}

?>
