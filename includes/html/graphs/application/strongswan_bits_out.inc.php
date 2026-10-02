<?php

$unit_text = 'Outbound bits/s';
$unitlen = 15;
$bigdescrlen = 18;
$smalldescrlen = 18;

$multiplier = 8;     // bytes/s -> bits/s
$rrdVar = 'bytes_out';

require 'strongswan-common.inc.php';
