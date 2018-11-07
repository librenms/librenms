<?php
/**
 * draytek.inc.php
 * @author     Jason Cheng <sanyu3u@gmail.com>
 */
preg_match('/Router Model: ([\w ]+), Version: ([\S\w\.]+),/', snmp_get($device, '.1.3.6.1.2.1.1.1.0', '-OQv'), $tmp_draytek);
$hardware = $tmp_draytek[1];
$version  = $tmp_draytek[2];
unset($tmp_draytek);

