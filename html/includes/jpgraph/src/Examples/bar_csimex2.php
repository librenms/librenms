<?php
include_once ("../jpgraph.php");
include_once ("../jpgraph_bar.php");

$data1y=array(12,8,19,3,10,5);
$data2y=array(8,2,12,7,14,4);

// Create the graph. These two calls are always required
$graph = new Graph(310,200,'auto');
$graph->SetScale("textlin");
$graph->img->SetMargin(40,30,20,40);
$graph->SetShadow();

// Create the bar plots
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

// Create the grouped bar plot
$abplot = new AccBarPlot(array($b1plot,$b2plot));

$abplot->SetShadow();
$abplot->value->Show();

// ...and add it to the graPH
$graph->Add($abplot);

$graph->title->Set("Image map barex2");
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);


// Send back the HTML page which will call this script again
// to retrieve the image.
$graph->StrokeCSIM();

?>
