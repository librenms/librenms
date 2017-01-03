<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
\JpGraph\JpGraph::load();
\JpGraph\JpGraph::module('pie');
\JpGraph\JpGraph::module('pie3d');

// Some data
$data = array(20, 27, 45, 75, 90);

// Create the Pie Graph.
$graph = new \PieGraph(350, 200);
$graph->SetShadow();

// Set A title for the plot
$graph->title->Set("Example 1 3D Pie plot");
$graph->title->SetFont(FF_VERDANA, FS_BOLD, 18);
$graph->title->SetColor("darkblue");
$graph->legend->Pos(0.1, 0.2);

// Create pie plot
$p1 = new \PiePlot3D($data);
$p1->SetTheme("sand");
$p1->SetCenter(0.4);
$p1->SetAngle(30);
$p1->value->SetFont(FF_ARIAL, FS_NORMAL, 12);
$p1->SetLegends(array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct"));

$graph->Add($p1);
$graph->Stroke();
