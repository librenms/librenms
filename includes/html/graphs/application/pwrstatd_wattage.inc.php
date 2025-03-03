<?php

$unit_text = 'Watts';
$unitlen = 20;
$bigdescrlen = 25;
$smalldescrlen = 25;

$rrdArray = [
    'wload' => ['descr' => 'PSU Load'],
    'wrating' => ['descr' => 'PSU Rating'],
];

require 'pwrstatd-common.inc.php';
