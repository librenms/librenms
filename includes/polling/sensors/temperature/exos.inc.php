<?php

preg_match('/ Temp.* ([: 0-9]+ C)/', $sensor_value, $temp_value);
[$sensor_value, $dump] = explode(' ', $temp_value[1]);
