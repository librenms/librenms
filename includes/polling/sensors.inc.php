<?php

poll_sensor($device,'current','A');
poll_sensor($device,'frequency', 'Hz');
poll_sensor($device,'fanspeed', 'rpm');
poll_sensor($device,'humidity', '%');
poll_sensor($device,'power', 'W');
poll_sensor($device,'voltage', 'V');
poll_sensor($device,'temperature', 'C');

# FIXME voltages have other filenames
#include('includes/polling/voltages.inc.php');

# FIXME also convert temperature, but there's some special code in there?
#include('includes/polling/temperatures.inc.php');

?>
