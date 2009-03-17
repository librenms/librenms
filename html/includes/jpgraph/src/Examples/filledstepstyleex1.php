<?php

include("../jpgraph.php");
include("../jpgraph_line.php");

// create the graph
$graph= new Graph(400,250,"auto");

$ydata = array(5,10,15,20,15,10);

$graph->SetScale("textlin");
$graph->SetShadow(true);
$graph->SetMarginColor("antiquewhite");
$graph->img->SetMargin(60,40,40,50);
$graph->img->setTransparent("white");
$graph->xaxis->SetFont(FF_FONT1);
$graph->xaxis->setTextTickInterval(1);
$graph->xaxis->SetTextLabelInterval(1);
$graph->legend->SetFillColor("antiquewhite");
$graph->legend->SetShadow(true);
$graph->legend->SetLayout(LEGEND_VERT);
$graph->legend->Pos(0.02,0.01);
$graph->title->Set("Step Styled Example");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$lineplot = new LinePlot($ydata);
$lineplot->SetColor("black");
$lineplot->setFillColor("gray7");
$lineplot->SetStepStyle();
$lineplot->SetLegend(" 2002 ");

// add plot to the graph
$graph->Add($lineplot);
$graph->ygrid->show(false,false);

// display graph
$graph->Stroke();

?>