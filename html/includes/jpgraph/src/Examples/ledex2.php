<?php

include "../jpgraph.php";
include "../jpgraph_led.php";

// By default each "LED" circle has a radius of 3 pixels
$led = new DigitalLED74();
$led->StrokeNumber('0123456789. ABCDEFGHIJKL',LEDC_RED); 



?>
