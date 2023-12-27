<?php

$unit_text = 'Volts';
$unitlen = 20;
$bigdescrlen = 25;
$smalldescrlen = 25;

$rrdArray = [
    'voutput' => ['descr' => 'PSU Output'],
    'vrating' => ['descr' => 'PSU Rating'],
    'vutility' => ['descr' => 'Utility Output'],
];

require 'pwrstatd-common.inc.php';
