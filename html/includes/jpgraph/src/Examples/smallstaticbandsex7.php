<?php
// Illustration of the different patterns for bands
// $Id: smallstaticbandsex7.php,v 1.1 2002/09/01 21:51:08 aditus Exp $
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(10,29,3,6);

// Create the graph. 
$graph = new Graph(200,150,"auto");	
$graph->SetScale("textlin");
$graph->SetMargin(25,10,20,20);

// Add 10% grace ("space") at top and botton of Y-scale. 
$graph->yscale->SetGrace(10);

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("lightblue");

// Position the X-axis at the bottom of the plotare
$graph->xaxis->SetPos("min");

$graph->ygrid->Show(false);

// .. and add the plot to the graph
$graph->Add($bplot);

// Add band
$band = new PlotBand(HORIZONTAL,BAND_HLINE,15,35,'khaki4');
$band->ShowFrame(false);
$graph->AddBand($band);

// Set title
$graph->title->SetFont(FF_ARIAL,FS_BOLD,10);
$graph->title->SetColor('darkred');
$graph->title->Set('BAND_HLINE');


$graph->Stroke();
?>
