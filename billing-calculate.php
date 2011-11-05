#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

  foreach (dbFetchRows("SELECT * FROM `bills` ORDER BY `bill_name`") as $bill)
  {
      unset($class);
      unset($rate_data);
      $day_data     = getDates($bill['bill_day']);
      $datefrom     = $day_data['0'];
      $dateto       = $day_data['1'];
      $datefrom_lp  = $day_data['2'];
      $dateto_lp    = $day_data['3'];
      $check_lp     = dbFetchRow("SELECT * FROM `bill_history` WHERE bill_id = ? AND bill_datefrom = ? AND bill_dateto = ? LIMIT 1", array($bill['bill_id'], $datefrom_lp, $dateto_lp));
      $rate_data    = getRates($bill['bill_id'],$datefrom,$dateto);
      $rate_95th    = $rate_data['rate_95th'];
      $dir_95th     = $rate_data['dir_95th'];
      $total_data   = $rate_data['total_data'];
      $rate_average = $rate_data['rate_average'];

      if ($bill['bill_type'] == "cdr")
      {
         $type = "CDR";
         $allowed = formatRates($bill['bill_cdr'] * 1000);
         $used    = formatRates($rate_data['rate_95th'] * 1000);
         $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100,2);
      } elseif ($bill['bill_type'] == "quota") {
         $type = "Quota";
         $allowed = formatStorage($bill['bill_gb'] * 1024 * 1024 * 1024);
         $used    = formatStorage($rate_data['total_data'] * 1024 * 1024);
         $percent = round(($rate_data['total_data'] / ($bill['bill_gb'] * 1024)) * 100,2);
      }

      echo(str_pad($bill['bill_id']." ".$bill['bill_name'], 30)." ".str_pad($type,8)." ".str_pad($allowed,10)." ".str_pad($used,10)." ".$percent."%");

      $update = array('rate_95th' => $rate_data['rate_95th'],
                      'rate_95th_in' => $rate_data['rate_95th_in'],
                      'rate_95th_out' => $rate_data['rate_95th_out'],
                      'dir_95th' => $rate_data['dir_95th'],
                      'total_data' => $rate_data['total_data'],
                      'total_data_in' => $rate_data['total_data_in'],
                      'total_data_out' => $rate_data['total_data_out'],
                      'rate_average' => $rate_data['rate_average'],
                      'rate_average_in' => $rate_data['rate_average_in'],
                      'rate_average_out' => $rate_data['rate_average_out'],
                      'bill_last_calc' => array('NOW()') );

      dbUpdate($update, 'bills', '`bill_id` = ?', array($bill['bill_id']));
#      print_r($rate_data);
      echo("\n");

      if (empty($check_lp['id'])) {
         unset($rate_data);
         $rate_data      = getRates($bill['bill_id'],$datefrom_lp,$dateto_lp);
         $rate_95th      = $rate_data['rate_95th'];
         $rate_95th_in   = $rate_data['rate_95th_in'];
         $rate_95th_out  = $rate_data['rate_95th_out'];
         $dir_95th       = $rate_data['dir_95th'];
         //$total_data     = formatStorage($rate_data['total_data'] * 1024 * 1024);
         //$total_data_in  = formatStorage($rate_data['total_data_in'] * 1024 * 1024);
         //$total_data_out = formatStorage($rate_data['total_data_out'] * 1024 * 1024);
         //$rate_average   = $rate_data['rate_average'];
         if ($bill['bill_type'] == "cdr")
         {
            $type = "CDR";
            //$allowed = formatRates($bill['bill_cdr'] * 1000);
            //$used    = formatRates($rate_data['rate_95th'] * 1000);
            //$overuse = formatRates(($rate_data['rate_95th'] - $bill['bill_cdr'])* 1000);
            $allowed = $bill['bill_cdr'];
            $used    = $rate_data['rate_95th'];
            $overuse = $used - $allowed;
            $overuse = (($overuse <= 0) ? "0" : $overuse);
            $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100,2);
         } elseif ($bill['bill_type'] == "quota") {
            $type = "Quota";
            //$allowed = formatStorage($bill['bill_gb'] * 1024 * 1024 * 1024);
            //$used    = formatStorage($rate_data['total_data'] * 1024 * 1024);
            //$overuse = formatStorage(($rate_data['total_data'] - ($bill['bill_gb'] * 1024)) * 1024 * 1024);
            $allowed = $bill['bill_gb'] * 1024;
            $used    = $rate_data['total_data'];
            $overuse = $used - $allowed;
            $overuse = (($overuse <= 0) ? "0" : $overuse);
            $percent = round(($rate_data['total_data'] / ($bill['bill_gb'] * 1024)) * 100,2);
         }
         $update = array('rate_95th' => $rate_data['rate_95th'],
                         'rate_95th_in' => $rate_data['rate_95th_in'],
                         'rate_95th_out' => $rate_data['rate_95th_out'],
                         'dir_95th' => $rate_data['dir_95th'],
                         'rate_average' => $rate_data['rate_average'],
                         'rate_average_in' => $rate_data['rate_average_in'],
                         'rate_average_out' => $rate_data['rate_average_out'],
                         'traf_total' => $rate_data['total_data'],
                         'traf_in' => $rate_data['total_data_in'],
                         'traf_out' => $rate_data['total_data_out'],
                         'bill_datefrom' => $datefrom_lp,
                         'bill_dateto' => $dateto_lp,
                         'bill_type' => $type,
                         'bill_allowed' => $allowed,
                         'bill_used' => $used,
                         'bill_overuse' => $overuse,
                         'bill_percent' => $percent,
                         'bill_id' => $bill['bill_id'] );
#         print_r($update);
         dbInsert($update, 'bill_history');
         echo(" * Generated historical data from ".strftime("%x @ %X", strtotime($datefrom_lp))." to ".strftime("%x @ %X", strtotime($dateto_lp))."\n");
      }
  }

?>
