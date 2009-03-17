<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

// Some data
$databary=array(12,7,16,6,7,14,9,3);
$months=$gDateLocale->GetShortMonth();

// New graph with a drop shadow
$graph = new Graph(300,200,'auto');
$graph->SetShadow();

// Use a "text" X-scale
$graph->SetScale("textlin");

// Specify X-labels
$graph->xaxis->SetTickLabels($months);

// Set title and subtitle
$graph->title->Set("Textscale with specified labels");

// Use built in font
$graph->title->SetFont(FF_FONT1,FS_BOLD);

// Create the bar plot
$b1 = new BarPlot($databary);
$b1->SetLegend("Temperature");

//$b1->SetAbsWidth(6);
//$b1->SetShadow();

// The order the plots are added determines who's ontop
$graph->Add($b1);

// Finally output the  image
$graph->Stroke();

?>


