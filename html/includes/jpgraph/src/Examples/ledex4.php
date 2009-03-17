<?php

include "../jpgraph.php";
include "../jpgraph_led.php";

// By default each "LED" circle has a radius of 3 pixels. Change to 5 and slghtly smaller margin
$led = new DigitalLED74(5);
$led->StrokeNumber('ABC123.',LEDC_RED); 



?>
