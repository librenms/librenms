<?php

### Authorises bill viewing and sets $ports as reference to mysql query containing ports for this bill

if (is_numeric($_GET['id']) && ($config['allow_unauth_graphs'] || bill_permitted($_GET['id'])))
{

  $ports = mysql_query("SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                        WHERE B.bill_id = '".mres($_GET['id'])."' AND P.interface_id = B.port_id
                        AND D.device_id = P.device_id");

  $auth = TRUE;
}

?>