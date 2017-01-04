<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$data = array(40, 60, 21, 33);

$graph = new Graph\PieGraph(300, 200);
$graph->SetShadow();

$graph->title->Set("Example 4 of pie plot");
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$p1 = new Plot\PiePlot($data);
$p1->value->SetFont(FF_FONT1, FS_BOLD);
$p1->value->SetColor("darkred");
$p1->SetSize(0.3);
$p1->SetCenter(0.4);
$p1->SetLegends(array("Jan", "Feb", "Mar", "Apr", "May"));
$graph->Add($p1);

$graph->Stroke();
