<?php

function getDates($dayofmonth)
{
  $dayofmonth = zeropad($dayofmonth);
  $year = date('Y');
  $month = date('m');

  if (date('d') > $dayofmonth)
  {
    $newmonth = zeropad($month + 1);
    if ($newmonth == 13)
    {
      $newmonth = "01";
      $newyear = $year + 1;
    } else {
      $newyear = $year;
    }

    $lastmonth = zeropad($month - 1);
    if ($lastmonth == 0)
    {
      $lastmonth = 12;
      $lastyear = $year - 1;
    } else {
      $lastyear = $year;
    }

    $date_from = $year . $month . $dayofmonth;
    $date_to   = $newyear . $newmonth . $dayofmonth;

    $last_from = $lastyear . $lastmonth . $dayofmonth;
    $last_to   = $year . $month . $dayofmonth;

  }
  else
  {
    $newmonth = zeropad($month - 1);
    if ($newmonth == 0)
    {
      $newmonth = 12;
      $newyear = $year - 1;
    } else {
      $newyear = $year;
    }

    $lastmonth = zeropad($month - 2);
    if ($lastmonth == -1)
    {
      $lastmonth = 10;
      $lastyear = $year - 1;
    } elseif ($lastmonth == 0)
    {
      $lastmonth = 11;
      $lastyear = $year - 1;
    } else {
      $lastyear = $year;
    }

    $date_from = $newyear . $newmonth . $dayofmonth;
    $date_to   = $year . $month . $dayofmonth;

    $last_from = $lastyear . $lastmonth . $dayofmonth;
    $last_to   = $newyear . $newmonth . $dayofmonth;

  }

  $return['0'] = $date_from . "000001";
  $return['1'] = $date_to . "000000";
  $return['2'] = $last_from . "000001";
  $return['3'] = $last_to . "000000";

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

  $q_95_sql = "SELECT (in_delta / period / 1000 * 8) AS rate FROM bill_data  WHERE bill_id = '".mres($bill_id)."'";
  $q_95_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."' ORDER BY in_delta ASC";
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

  $q_95_sql = "SELECT (out_delta / period / 1000 * 8) AS rate FROM bill_data  WHERE bill_id = '".mres($bill_id)."'";
  $q_95_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."' ORDER BY out_delta ASC";

  $a_95th = dbFetchColumn($q_95_sql);
  $m_95th = $a_95th[$measurement_95th];

  return(round($m_95th, 2));
}

function getRates($bill_id,$datefrom,$dateto)
{
  $mq_text = "SELECT count(delta) FROM bill_data ";
  $mq_text .= " WHERE bill_id = '".mres($bill_id)."'";
  $mq_text .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'";
  $measurements = dbFetchCell($mq_sql);
  $measurement_95th = round($measurements /100 * 95) - 1;

  $q_95_sql = "SELECT delta FROM bill_data  WHERE bill_id = '".mres($bill_id)."'";
  $q_95_sql .= " AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."' ORDER BY delta ASC";

  $a_95th = dbFetchColumn($q_95_sql);
  $m_95th = $a_95th[$measurement_95th];

  $mtot = getTotal($bill_id,$datefrom,$dateto);
  $ptot = getPeriod($bill_id,$datefrom,$dateto);

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

  $data['total_data'] = round($mtot / 1000 / 1000, 2);
  $data['rate_average'] = round($mtot / $ptot / 1000 * 8, 2);

#  print_r($data);

  return($data);
}

function getTotal($bill_id,$datefrom,$dateto)
{
  $mtot = dbFetchCell("SELECT SUM(delta) FROM bill_data WHERE bill_id = '".mres($bill_id)."' AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'");
  return($mtot);
}

function getPeriod($bill_id,$datefrom,$dateto)
{
  $ptot = dbFetchCell("SELECT SUM(period) FROM bill_data WHERE bill_id = '".mres($bill_id)."' AND timestamp > '".mres($datefrom)."' AND timestamp <= '".mres($dateto)."'");
  return($ptot);
}

?>
