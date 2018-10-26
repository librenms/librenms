<?php
/**
 * draytek.inc.php
 * @author     Jason Cheng <sanyu3u@gmail.com>
 */
$tmp_draytek  = snmp_get($device, '.1.3.6.1.2.1.1.1.0', '-OQv');
$tmp_hw       = explode(',',$tmp_draytek)[1];
$tmp_ver      = explode(',',$tmp_draytek)[2];
$hardware     = explode(':',$tmp_hw)[1];
$version      = explode(':',$tmp_ver)[1];
unset($tmp_draytek);
unset($tmp_hw);
unset($tmp_ver);
unset($tmp_hardware);
unset($tmp_version);
