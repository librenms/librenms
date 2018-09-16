<?php

$link_array = array(
               'page'   => 'device',
               'device' => $device['device_id'],
               'tab'    => 'nac',
              );
$pagetitle[] = 'NAC';
echo"<br>";


include 'includes/print-nac.inc.php';

echo '</table>';
