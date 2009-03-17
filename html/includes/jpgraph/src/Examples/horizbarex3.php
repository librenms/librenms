<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$datay=array(2,3,-5,8,12,6,3);
$datax=array("Jan","Feb","Mar","Apr","May","Jun","Jul");

// Size of graph
$width=400; 
$height=500;

// Set the basic parameters of the graph 
$graph = new Graph($width,$height,'auto');
$graph->SetScale("textlin");

$top = 50;
$bottom = 80;
$left = 50;
$right = 20;
$graph->Set90AndMargin($left,$right,$top,$bottom);

$graph->xaxis->SetPos('min');

// Nice shadow
$graph->SetShadow();

// Setup title
$graph->title->Set("Horizontal bar graph ex 3");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);
$graph->subtitle->Set("(Axis at bottom)");

// Setup X-axis
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetFont(FF_FONT2,FS_BOLD,12);

// Some extra margin looks nicer
$graph->xaxis->SetLabelMargin(5);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('right','center');

// Add some grace to y-axis so the bars doesn't go
// all the way to the end of the plot area
$graph->yaxis->scale->SetGrace(20);

// Setup the Y-axis to be displayed in the bottom of the 
// graph. We also finetune the exact layout of the title,
// ticks and labels to make them look nice.
$graph->yaxis->SetPos('max');

// First make the labels look right
$graph->yaxis->SetLabelAlign('center','top');
$graph->yaxis->SetLabelFormat('%d');
$graph->yaxis->SetLabelSide(SIDE_RIGHT);

// The fix the tick marks
$graph->yaxis->SetTickSide(SIDE_LEFT);

// Finally setup the title
$graph->yaxis->SetTitleSide(SIDE_RIGHT);
$graph->yaxis->SetTitleMargin(35);

// To align the title to the right use :
$graph->yaxis->SetTitle('Turnaround 2002','high');
$graph->yaxis->title->Align('right');

// To center the title use :
//$graph->yaxis->SetTitle('Turnaround 2002','center');
//$graph->yaxis->title->Align('center');

$graph->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->yaxis->title->SetAngle(0);

$graph->yaxis->SetFont(FF_FONT2,FS_NORMAL);
// If you want the labels at an angle other than 0 or 90
// you need to use TTF fonts
//$graph->yaxis->SetLabelAngle(0);

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
