<?php
/**
 * draytek.inc.php
 * @author     Jason Cheng <sanyu3u@gmail.com>
 */
preg_match('/Router Model: ([\w ]+), Version: ([\w\.]+)/', $device['sysDescr'], $tmp_draytek);
$hardware = $tmp_draytek[1];
$version = $tmp_draytek[2];
unset($tmp_draytek);
