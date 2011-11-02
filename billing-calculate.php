#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

  foreach (dbFetchRows("SELECT * FROM `bills` ORDER BY `bill_name`") as $bill)
  {
      unset($class);
      $day_data     = getDates($bill['bill_day']);
      $datefrom     = $day_data['0'];
      $dateto       = $day_data['1'];
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
         $allowed = formatStorage($bill['bill_gb']* 1024 * 1024 * 1024);
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


#print_r($rate_data);

           echo("\n");
  }

?>
