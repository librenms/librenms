<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;
use Amenadiel\JpGraph\Text;

$l1datay = array(11, 9, 2, 4, 3, 13, 17);
$l2datay = array(23, 12, 5, 19, 17, 10, 15);
$datax   = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul");

// Create the graph.
$graph = new Graph\Graph(350, 200);
$graph->img->SetMargin(40, 70, 20, 40);
$graph->SetScale("textlin");
$graph->SetShadow();
$graph->SetColor(array(250, 250, 250));

$graph->img->SetTransparent("white");

$t1 = new Text\Text("This is a text");
$t1->SetPos(0.5, 0.5);
$t1->SetOrientation("h");
$t1->SetFont(FF_FONT1, FS_BOLD);
$t1->SetBox("white", "black", "gray");
$t1->SetColor("black");
$graph->AddText($t1);

// Create the linear error plot
$l1plot = new Plot\LinePlot($l1datay);
$l1plot->SetColor("blue");
$l1plot->SetWeight(2);
$l1plot->SetLegend("Prediction");

// Create the bar plot
$l2plot = new Plot\BarPlot($l2datay);
$l2plot->SetFillColor("orange");
$l2plot->SetLegend("Result");

// Add the plots to the graph
$graph->Add($l1plot);
$graph->Add($l2plot);

$graph->title->Set("Example 16.3");
$graph->xaxis->title->Set("Month");
$graph->yaxis->title->Set("x10,000 US$");

$graph->title->SetFont(FF_FONT1, FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1, FS_BOLD);

$graph->xaxis->SetTickLabels($datax);
//$graph->xaxis->SetTextTickInterval(2);

// Display the graph
$graph->Stroke();
