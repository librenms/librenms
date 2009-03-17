<?php
// $Id: barscalecallbackex1.php,v 1.2 2002/07/11 23:27:28 aditus Exp $
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

// Callback function for Y-scale
function yScaleCallback($aVal) {
	return number_format($aVal);
}

// Some data
$datay=array(120567,134013,192000,87000);

// Create the graph and setup the basic parameters 
$graph = new Graph(460,200,'auto');	
$graph->img->SetMargin(80,30,30,40);
$graph->SetScale("textint");
$graph->SetShadow();
$graph->SetFrame(false); // No border around the graph

// Add some grace to the top so that the scale doesn't
// end exactly at the max value. 
// Since we are using integer scale the gace gets intervalled
// to adding integer values.
// For example grace 10 to 100 will add 1 to max, 101-200 adds 2
// and so on...
$graph->yaxis->scale->SetGrace(30);
$graph->yaxis->SetLabelFormatCallback('yScaleCallback');

// Setup X-axis labels
$a = $gDateLocale->GetShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT2);

// Setup graph title ands fonts
$graph->title->Set("Example of Y-scale callback formatting");
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
$bplot->value->SetFormat('$ %0.0f');
// Black color for positive values and darkred for negative values
$bplot->value->SetColor("black","darkred");
$graph->Add($bplot);

// Finally stroke the graph
$graph->Stroke();
?>
