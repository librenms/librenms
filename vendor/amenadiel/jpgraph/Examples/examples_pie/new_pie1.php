<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Create the Pie Graph.
$graph = new Graph\PieGraph(350, 250);
$graph->title->Set("A Simple Pie Plot");
$graph->SetBox(true);

$data = array(40, 21, 17, 14, 23);
$p1   = new Plot\PiePlot($data);
$p1->ShowBorder();
$p1->SetColor('black');
$p1->SetSliceColors(array('#1E90FF', '#2E8B57', '#ADFF2F', '#DC143C', '#BA55D3'));

$graph->Add($p1);
$graph->Stroke();
