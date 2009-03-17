<?php
// $Id: bar_csimex3.php,v 1.3 2002/08/31 20:03:46 aditus Exp $
// Horiontal bar graph with image maps
include ("../jpgraph.php");
include ("../jpgraph_bar.php");

$data1y=array(5,8,19,3,10,5);
$data2y=array(12,2,12,7,14,4);

// Setup the basic parameters for the graph
$graph = new Graph(400,700);
$graph->SetAngle(90);
$graph->SetScale("textlin");

// The negative margins are necessary since we
// have rotated the image 90 degress and shifted the 
// meaning of width, and height. This means that the 
// left and right margins now becomes top and bottom
// calculated with the image width and not the height.
$graph->img->SetMargin(-80,-80,210,210);

$graph->SetMarginColor('white');

// Setup title for graph
$graph->title->Set('Horizontal bar graph');
$graph->title->SetFont(FF_FONT2,FS_BOLD);
$graph->subtitle->Set("With image map\nNote: The URL just points back to this image");

// Setup X-axis.
$graph->xaxis->SetTitle("X-title",'center');
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetAngle(90);
$graph->xaxis->SetTitleMargin(30);
$graph->xaxis->SetLabelMargin(15);
$graph->xaxis->SetLabelAlign('right','center');

// Setup Y-axis

// First we want it at the bottom, i.e. the 'max' value of the
// x-axis
$graph->yaxis->SetPos('max');

// Arrange the title
$graph->yaxis->SetTitle("Turnaround (mkr)",'center');
$graph->yaxis->SetTitleSide(SIDE_RIGHT);
$graph->yaxis->title->SetFont(FF_FONT2,FS_BOLD);
$graph->yaxis->title->SetAngle(0);
$graph->yaxis->title->Align('center','top');
$graph->yaxis->SetTitleMargin(30);

// Arrange the labels
$graph->yaxis->SetLabelSide(SIDE_RIGHT);
$graph->yaxis->SetLabelAlign('center','top');

// Create the bar plots with image maps
$b1plot = new BarPlot($data1y);
$b1plot->SetFillColor("orange");
$targ=array("bar_clsmex2.php#1","bar_clsmex2.php#2","bar_clsmex2.php#3",
            "bar_clsmex2.php#4","bar_clsmex2.php#5","bar_clsmex2.php#6");
$alts=array("val=%d","val=%d","val=%d","val=%d","val=%d","val=%d");
$b1plot->SetCSIMTargets($targ,$alts);

$b2plot = new BarPlot($data2y);
$b2plot->SetFillColor("blue");
$targ=array("bar_clsmex2.php#7","bar_clsmex2.php#8","bar_clsmex2.php#9",
            "bar_clsmex2.php#10","bar_clsmex2.php#11","bar_clsmex2.php#12");
$alts=array("val=%d","val=%d","val=%d","val=%d","val=%d","val=%d");
$b2plot->SetCSIMTargets($targ,$alts);

// Create the accumulated bar plot
$abplot = new AccBarPlot(array($b1plot,$b2plot));
$abplot->SetShadow();

// We want to display the value of each bar at the top
$abplot->value->Show();
$abplot->value->SetFont(FF_FONT1,FS_NORMAL);
$abplot->value->SetAlign('left','center');
$abplot->value->SetColor("black","darkred");
$abplot->value->SetFormat('%.1f mkr');

// ...and add it to the graph
$graph->Add($abplot);

// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->StrokeCSIM();

?>
