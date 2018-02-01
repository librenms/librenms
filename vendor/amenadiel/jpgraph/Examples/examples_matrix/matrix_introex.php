<?php
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_matrix.php';
require_once 'jpgraph/jpgraph_iconplot.php';

$data = array(
    array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
    array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0),
    array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
    array(10, 9, 8, 17, 6, 5, 4, 3, 2, 1, 0),
    array(0, 1, 2, 3, 4, 4, 9, 7, 8, 9, 10),
    array(8, 1, 2, 3, 4, 8, 3, 7, 8, 9, 10),
    array(10, 3, 5, 7, 6, 5, 4, 3, 12, 1, 0),
    array(10, 9, 8, 7, 6, 5, 4, 3, 2, 1, 0),
);
// Interpolate the data a factor of 4 to get some mroe
// data points
doMeshInterpolate($data, 4);

// Setup a timer
$timer = new Util\JpgTimer();
$timer->Push();

//--------------------------------------------------------------
// Setup a basic matrix graph
//--------------------------------------------------------------
$width  = 740;
$height = 500;
$graph  = new MatrixGraph($width, $height);
$graph->SetMargin(1, 2, 70, 1);
$graph->SetColor('white');
$graph->SetMarginColor('#fafafa');
$graph->title->Set('Intro matrix graph');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 14);

// Setup the background image
$graph->SetBackgroundImage('fireplace.jpg', BGIMG_FILLPLOT);
$graph->SetBackgroundImageMix(50);

// Setup the timer in the right footer
$graph->footer->SetTimer($timer);
$graph->footer->right->SetColor('white');

//--------------------------------------------------------------
// Create the 2 matrix plots
//--------------------------------------------------------------
$mp = array();
$n  = 2;
for ($i = 0; $i < $n; ++$i) {
    $mp[$i] = new MatrixPlot($data);
    $mp[$i]->colormap->SetMap($i);
    $mp[$i]->SetSize(300, 250);
    $mp[$i]->SetLegendLayout(1);
    $mp[$i]->SetAlpha(0.2);

    // Make the legend slightly longer than default
    $mp[$i]->legend->SetSize(20, 280);
}
$mp[1]->colormap->SetMap(3);

$hor1 = new LayoutHor(array($mp[0], $mp[1]));
$hor1->SetCenterPos(0.5, 0.5);

$graph->Add($hor1);

//--------------------------------------------------------------
// Add texts to the graph
//--------------------------------------------------------------
$txts = array(
    array('Temperature gradient', $width / 2, 80),
    array('Heat color map', 200, 110),
    array('High contrast map', 560, 110));

$n = count($txts);
$t = array();
for ($i = 0; $i < $n; ++$i) {
    $t[$i] = new Text($txts[$i][0], $txts[$i][1], $txts[$i][2]);
    $t[$i]->SetFont(FF_ARIAL, FS_BOLD, 14);
    $t[$i]->SetColor('white');
    $t[$i]->SetAlign('center', 'top');
}
$graph->Add($t);

//--------------------------------------------------------------
// Add Jpgraph logo to top left corner
//--------------------------------------------------------------
$icon = new IconPlot('jpglogo.jpg', 2, 2, 0.9, 50);
$graph->Add($icon);

$graph->Stroke();
