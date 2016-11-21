<?php

// Authorises bill viewing and sets $ports as reference to mysql query containing ports for this bill

if (is_numeric($_GET['id']) && ($auth || bill_permitted($_GET['id']))) {
    $bill = dbFetchRow('SELECT * FROM `bills` WHERE bill_id = ?', array($_GET['id']));

    $datefrom = date('YmdHis', $_GET['from']);
    $dateto   = date('YmdHis', $_GET['to']);

    $rates = getRates($_GET['id'], $datefrom, $dateto);

    $ports = dbFetchRows('SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D WHERE B.bill_id = ? AND P.port_id = B.port_id AND D.device_id = P.device_id', array($_GET['id']));

    $auth = true;
}
