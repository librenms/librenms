<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

$ydata = array(11,3,8,12,5,1,9,13,5,7);
$y2data = array(354,200,265,99,111,91,198,225,293,251);

$graph = new Graph(350,300,"auto");	
$graph->SetAngle(40);
$graph->img->SetMargin(80,80,80,80);
$graph->SetScale("textlin");
$graph->SetY2Scale("lin");
$graph->SetShadow();

// Create the linear plot
$lineplot=new LinePlot($ydata);
$lineplot2=new LinePlot($y2data);

// Add the plot to the graph
$graph->Add($lineplot);
$graph->AddY2($lineplot2);
$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);
$graph->y2axis->SetColor("orange");

$graph->title->Set("Example 1 rotated graph (40 degree)");
$graph->legend->Pos(0.05,0.1,"right","top");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

$lineplot->SetColor("blue");
$lineplot->SetWeight(2);

$lineplot2->SetColor("orange");
$lineplot2->SetWeight(2);

$graph->yaxis->SetColor("blue");

$lineplot->SetLegend("Plot 1");
$lineplot2->SetLegend("Plot 2");

// Display the graph
$graph->Stroke();
?>
