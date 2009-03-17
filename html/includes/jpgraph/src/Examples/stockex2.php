<?php
// $Id: stockex2.php,v 1.1 2003/01/31 17:41:29 aditus Exp $
include ("../jpgraph.php");
include ("../jpgraph_stock.php");


$datay = array(
    34,42,27,45,
    55,25,12,59,
    38,49,32,64,
    34,40,29,42,
    40,29,22,45 );

// Setup basic graph
$graph = new Graph(300,200);
$graph->SetScale("textlin");
$graph->SetMarginColor('white');
$graph->SetFrame(false);
$graph->ygrid->SetFill(true,'#EFEFEF@0.5','#BBCCFF@0.5');
$graph->SetBox();

$graph->tabtitle->Set(' Week 34 ');
$graph->tabtitle->SetFont(FF_ARIAL,FS_NORMAL,12);

// Get week days in curent locale
$days = $gDateLocale->GetShortDay();
array_shift($days); // Start on monday
$graph->xaxis->SetTickLabels($days);

// Create stock plot
$p1 = new StockPlot($datay);

// Indent plot so first and last bar isn't on the edges
$p1->SetCenter();

// Add and stroke
$graph->Add($p1);
$graph->Stroke();
?>
