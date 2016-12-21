<?php // content="text/plain; charset=utf-8"

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_led.php');

// By default each "LED" circle has a radius of 3 pixels. Change to 5 and slghtly smaller margin
$led = new DigitalLED74(3);
$led->SetSupersampling(2);
$text =     'А'.
            'Б'.
            'В'.
            'Г'.
            'Д'.
            'Е'.
            'Ё'.
            'З'.
            'И'.
            'Й'.
            'К'.
            'Л'.
            'М'.
            'Н'.
            'О'.
			'П';
$led->StrokeNumber($text, LEDC_RED);

?>
