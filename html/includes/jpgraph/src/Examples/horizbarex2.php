<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(2,3,5,8,12,6,3);
$datax=array("Jan","Feb","Mar","Apr","May","Jun","Jul");

$width=400; 
$height=500;

// Set the basic parameters of the graph 
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");

$top = 80;
$bottom = 30;
$left = 50;
$right = 30;
$graph->Set90AndMargin($left,$right,$top,$bottom);

// Nice shadow
$graph->SetShadow();

// Setup title
$graph->title->Set("Horizontal bar graph ex 2");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
$graph->subtitle->Set("(Axis at top)");

// Setup X-axis
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,12);

// Some extra margin looks nicer
$graph->xaxis->SetLabelMargin(5);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('right','center');

// Add some grace to y-axis so the bars doesn't go
// all the way to the end of the plot area
$graph->yaxis->scale->SetGrace(20);
$graph->yaxis->SetLabelAlign('center','bottom');
$graph->yaxis->SetLabelAngle(45);
$graph->yaxis->SetLabelFormat('%d');
$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,12);

// We don't want to display Y-axis
//$graph->yaxis->Hide();

// Now create a bar pot
$bplot = new BarPlot($datay);
$bplot->SetFillColor("orange");
$bplot->SetShadow();

//You can change the width of the bars if you like
//$bplot->SetWidth(0.5);

// We want to display the value of each bar at the top
$bplot->value->Show();
$bplot->value->SetFont(FF_ARIAL,FS_BOLD,12);
$bplot->value->SetAlign('left','center');
$bplot->value->SetColor("black","darkred");
$bplot->value->SetFormat('%.1f mkr');

// Add the bar to the graph
$graph->Add($bplot);


$graph->Stroke();
?>
