<?php
$name = 'nvidia';
$app_id = $app['app_id'];
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Bar1 MB';
$unitlen       = 8;
$bigdescrlen   = 8;
$smalldescrlen = 8;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 15;

$rrdVar='bar1';

require 'nvidia-common.inc.php';
