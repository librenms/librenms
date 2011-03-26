<?php

### Authorises bill viewing and sets $ports as reference to mysql query containing ports for this bill

include("../includes/billing.php");
include("../includes/functions.php"); ## FIXME zeropad()

if (is_numeric($_GET['id']) && ($config['allow_unauth_graphs'] || bill_permitted($_GET['id'])))
{
  $bill_query   = mysql_query("SELECT * FROM `bills` WHERE bill_id = '".mres($_GET['id'])."'");
  $bill         = mysql_fetch_assoc($bill_query);

  $day_data     = getDates($bill['bill_day']);
  $datefrom     = $day_data['0'];
  $dateto       = $day_data['1'];

  $rates = getRates($_GET['id'], $datefrom,  $dateto);

  $ports = mysql_query("SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                        WHERE B.bill_id = '".mres($_GET['id'])."' AND P.interface_id = B.port_id
                        AND D.device_id = P.device_id");

  $auth = TRUE;
}

?>