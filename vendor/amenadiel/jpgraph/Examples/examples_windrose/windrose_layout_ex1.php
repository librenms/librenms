<?php
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Some data for the five windrose plots
$data = array(
    array(
        1 => array(10, 10, 13, 7),
        2 => array(2, 8, 10),
        4 => array(1, 12, 22)),
    array(
        4 => array(12, 8, 2, 3),
        2 => array(5, 4, 4, 5, 2)),
    array(
        1 => array(12, 8, 2, 3),
        3 => array(5, 4, 4, 5, 2)),
    array(
        2 => array(12, 8, 2, 3),
        3 => array(5, 4, 4, 5, 2)),
    array(
        4 => array(12, 8, 2, 3),
        6 => array(5, 4, 4, 5, 2)),
);

// Legend range colors
$rangecolors = array('green', 'yellow', 'red', 'brown');

// Create a windrose graph with titles
$graph = new Graph\WindroseGraph(750, 700);

$graph->title->Set('Multiple plots with automatic layout');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);

// Setup the individual windrose plots
$wp = array();
for ($i = 0; $i < 5; ++$i) {
    $wp[$i] = new Plot\WindrosePlot($data[$i]);
    $wp[$i]->SetType(WINDROSE_TYPE8);
    if ($i < 2) {
        $wp[$i]->SetSize(0.28);
    } else {
        $wp[$i]->legend->Hide();
        $wp[$i]->SetSize(0.16);
        $wp[$i]->SetCenterSize(0.25);
    }
    $wp[$i]->SetRangeColors($rangecolors);
}

// Position with two rows. Two plots in top row and three plots in
// bottom row.
$hl1 = new LayoutHor(array($wp[0], $wp[1]));
$hl2 = new LayoutHor(array($wp[2], $wp[3], $wp[4]));
$vl  = new LayoutVert(array($hl1, $hl2));

$graph->Add($vl);
$graph->Stroke();
