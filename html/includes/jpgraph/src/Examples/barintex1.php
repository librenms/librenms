<?php
// $Id: barintex1.php,v 1.3 2002/07/11 23:27:28 aditus Exp $
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

// Some data
$datay=array(1,1,0.5);

// Create the graph and setup the basic parameters 
$graph = new Graph(460,200,'auto');	
$graph->img->SetMargin(40,30,30,40);
$graph->SetScale("textint");
$graph->SetShadow();
$graph->SetFrame(false); // No border around the graph

// Add some grace to the top so that the scale doesn't
// end exactly at the max value. 
$graph->yaxis->scale->SetGrace(100);

// Setup X-axis labels
$a = $gDateLocale->GetShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT2);

// Setup graph title ands fonts
$graph->title->Set("Example of integer Y-scale");
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->xaxis->title->Set("Year 2002");
$graph->xaxis->title->SetFont(FF_FONT2,FS_BOLD);
                              
// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("orange");
$bplot->SetWidth(0.5);
$bplot->SetShadow();

// Setup the values that are displayed on top of each bar
$bplot->value->Show();
// Must use TTF fonts if we want text at an arbitrary angle
$bplot->value->SetFont(FF_ARIAL,FS_BOLD);
$bplot->value->SetAngle(45);
// Black color for positive values and darkred for negative values
$bplot->value->SetColor("black","darkred");
$graph->Add($bplot);

// Finally stroke the graph
$graph->Stroke();
?>
