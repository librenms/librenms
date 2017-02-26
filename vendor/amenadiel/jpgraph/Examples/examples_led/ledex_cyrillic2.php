<?php // content="text/plain; charset=utf-8"

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_led.php');

// By default each "LED" circle has a radius of 3 pixels. Change to 5 and slghtly smaller margin
$led = new DigitalLED74(3);
$led->SetSupersampling(2);
$text =     'Р'.
            'С'.
            'Т'.
            'У'.
            'Ф'.
            'Х'.
            'Ц'.
            'Ч'.
            'Ш'.
            'Щ'.
            'Ъ'.
            'Ы'.
            'Ь'.
            'Э'.
            'Ю'.
            'Я';
$led->StrokeNumber($text, LEDC_RED);

?>
