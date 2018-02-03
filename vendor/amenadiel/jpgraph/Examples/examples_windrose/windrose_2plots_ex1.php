<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Data can be specified using both ordinal idex of axis as well
// as the direction label
$data = array(
    1 => array(10, 10, 13, 7),
    2 => array(2, 8, 10),
    4 => array(1, 12, 22),
);

$data2 = array(
    4 => array(12, 8, 2, 3),
    2 => array(5, 4, 4, 5, 2),
);

// Create a new small windrose graph
$graph = new Graph\WindroseGraph(660, 400);
$graph->SetShadow();

$graph->title->Set('Two windrose plots in one graph');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);
$graph->subtitle->Set('(Using Box() for each plot)');

$wp = new Plot\WindrosePlot($data);
$wp->SetType(WINDROSE_TYPE8);
$wp->SetSize(0.42);
$wp->SetPos(0.25, 0.55);
$wp->SetBox();

$wp2 = new Plot\WindrosePlot($data2);
$wp2->SetType(WINDROSE_TYPE16);
$wp2->SetSize(0.42);
$wp2->SetPos(0.74, 0.55);
$wp2->SetBox();
$wp2->SetRangeColors(array('green', 'yellow', 'red', 'brown'));

$graph->Add($wp);
$graph->Add($wp2);

$graph->Stroke();
