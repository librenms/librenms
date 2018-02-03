<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Data can be specified using both ordinal index of the axis
// as well as the direction label.
$data = array(
    2 => array(1, 15, 7.5, 2),
    5 => array(1, 1, 1.5, 2),
    7 => array(1, 2, 10, 3, 2),
    8 => array(2, 3, 1, 3, 1, 2),
);

// First create a new windrose graph with a title
$graph = new Graph\WindroseGraph(590, 580);
$graph->title->Set('Japanese locale');
#$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
$graph->title->SetColor('navy');

// Create the free windrose plot.
$wp = new Plot\WindrosePlot($data);
$wp->SetType(WINDROSE_TYPE8);

// Add some "arbitrary" text to the center
$wp->scale->SetZeroLabel("SOx\n8%%");

// Localize the compass direction labels into Japanese
// Note: The labels for data must now also match the exact
// string for the compass directions.
//
// Ｅ　　　東
// NＥ　　北東
// Ｎ　　　北
// NＷ　　北西
// Ｗ　　　西
// SＷ　　南西
// Ｓ　　　南
// SＥ　　南東
$jp_CompassLbl = array('東', '', '北東', '', '北', '', '北西', '',
    '西', '', '南西', '', '南', '', '南東', '');
$wp->SetCompassLabels($jp_CompassLbl);
#$wp->SetFont(FF_MINCHO,FS_NORMAL,15);

// Localize the "Calm" text into Swedish and make the circle
// slightly bigger than default
$jp_calmtext = '平穏';
$wp->legend->SetCircleText($jp_calmtext);
$wp->legend->SetCircleRadius(20);
#$wp->legend->SetCFont(FF_MINCHO,FS_NORMAL,10);
$wp->legend->SetMargin(5, 0);
$wp->SetPos(0.5, 0.5);

// Adjust the displayed ranges
$ranges = array(1, 3, 5, 8, 12, 19, 29);
$wp->SetRanges($ranges);

// Set the scale to always have max value of 30
$wp->scale->Set(30, 10);
#$wp->scale->SetFont(FF_VERA,FS_NORMAL,12);

// Finally add it to the graph and send back to client
$graph->Add($wp);
$graph->Stroke();
