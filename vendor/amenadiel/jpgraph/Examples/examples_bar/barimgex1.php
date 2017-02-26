<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
require_once 'jpgraph/jpgraph_line.php';

$datay = array(5, 3, 11, 6, 3);

$graph = new Graph\Graph(400, 300, 'auto');
$graph->SetScale("textlin");

$graph->title->Set('Images on top of bars');
$graph->title->SetFont(FF_ARIAL, FS_BOLD, 13);

$graph->SetTitleBackground('lightblue:1.1', TITLEBKG_STYLE1, TITLEBKG_FRAME_BEVEL);

$bplot = new Plot\BarPlot($datay);
$bplot->SetFillColor("orange");
$bplot->SetWidth(0.5);

$lplot = new Plot\LinePlot($datay);
$lplot->SetColor('white@1');
$lplot->SetBarCenter();
$lplot->mark->SetType(MARK_IMG_LBALL, 'red');

$graph->Add($bplot);
$graph->Add($lplot);

$graph->Stroke();
