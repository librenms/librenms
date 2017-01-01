<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Some data
$data = array(40, 60, 21, 33);

// Create the Pie Graph.
$graph = new Graph\PieGraph(350, 250);

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

// Set A title for the plot
$graph->title->Set("A Simple 3D Pie Plot");

// Create
$p1 = new Plot\PiePlot3D($data);
$graph->Add($p1);

$p1->ShowBorder();
$p1->SetColor('black');
$p1->SetSliceColors(array('#1E90FF', '#2E8B57', '#ADFF2F', '#BA55D3'));
$p1->ExplodeSlice(1);
$graph->Stroke();
