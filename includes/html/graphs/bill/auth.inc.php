<?php

// Authorises bill viewing
if (is_numeric($vars['id']) && ($auth || bill_permitted($vars['id']))) {
    $bill_id = $vars['id'];
    $bill = dbFetchRow('SELECT * FROM `bills` WHERE bill_id = ?', [$bill_id]);
    $auth = true;
}
