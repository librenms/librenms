<?php
include ("../jpgraph.php");
include ("../jpgraph_scatter.php");
include ("../jpgraph_line.php");

// Create some "fake" regression data
$datay = array();
$datay2 = array();
$datax = array();
$a=rand(-3,3);
$b=rand(-5,5);
for($x=0; $x<20; ++$x) {
    $datay[] = $a*$x + $b;
    $datay2[] = $a*$x + $b + rand(-30,30);
    $datax[] = $x;
}
 
// Create the graph
$graph = new Graph(300,200,'auto');
$graph->SetScale("linlin");

// Setup title
$graph->title->Set("Example of linear regression");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// make sure that the X-axis is always at the
// bottom at the plot and not just at Y=0 which is
// the default position
$graph->xaxis->SetPos('min');

// Create the scatter plot with some nice colors
$sp1 = new ScatterPlot($datay2,$datax);
$sp1->mark->SetType(MARK_FILLEDCIRCLE);
$sp1->mark->SetFillColor("red");
$sp1->SetColor("blue");
$sp1->SetWeight(3);
$sp1->mark->SetWidth(4);

// Create the regression line
$lplot = new LinePlot($datay);
$lplot->SetWeight(2);
$lplot->SetColor('navy');

// Add the pltos to the line
$graph->Add($sp1);
$graph->Add($lplot);

// ... and stroke
$graph->Stroke();

?>


