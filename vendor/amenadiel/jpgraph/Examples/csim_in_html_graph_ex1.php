<?php // content="text/plain; charset=utf-8"
require_once '../jpgraph.php';
require_once '../jpgraph_bar.php';

$datay = array(12, 26, 9, 17, 31);

// Create the graph.
$graph = new Graph\Graph(400, 250);
$graph->SetScale('textlin');
$graph->SetMargin(50, 80, 20, 40);

// Create a bar pot
$bplot = new Plot\BarPlot($datay);

$n = count($datay); // Number of bars

global $_wrapperfilename;

// Create targets for the image maps. One for each column
$targ = array();
$alt = array();
$wtarg = array();
for ($i = 0; $i < $n; ++$i) {
    $urlarg = 'clickedon=' . ($i + 1);
    $targ[] = $_wrapperfilename . '?' . $urlarg;
    $alt[] = 'val=%d';
    $wtarg[] = '';
}
$bplot->SetCSIMTargets($targ, $alt, $wtarg);

$graph->Add($bplot);

$graph->title->Set('Multiple Image maps');
$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->title->SetCSIMTarget('#45', 'Title for Bar', '_blank');

$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetCSIMTarget('#55', 'Y-axis title');
$graph->yaxis->title->Set("Y-title");

$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetCSIMTarget('#55', 'X-axis title');
$graph->xaxis->title->Set("X-title");

// Send back the image when we are called from within the <img> tag
$graph->StrokeCSIMImage();
