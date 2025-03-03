<?php

$unit_text = 'Percent';
$unitlen = 20;
$bigdescrlen = 25;
$smalldescrlen = 25;

$rrdArray = [
    'pcapacity' => ['descr' => 'PSU Battery Charge'],
    'pload' => ['descr' => 'PSU Load'],
];

require 'pwrstatd-common.inc.php';
