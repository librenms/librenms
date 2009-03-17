<?php
include ("../jpgraph.php");
include ("../jpgraph_bar.php");
include ("../jpgraph_line.php");

$datay=array(2,3,5,8.5,11.5,6,3);

// Create the graph.
$graph = new Graph(460,400,'auto');
$graph->SetScale("textlin");
$graph->SetMargin(40,20,50,70);

$graph->legend->SetPos(0.5,0.97,'center','bottom');

$graph->title->Set('Plot line legend');
$graph->title->SetFont(FF_ARIAL,FS_BOLD,14);

$graph->SetTitleBackground('lightblue:1.3',TITLEBKG_STYLE2,TITLEBKG_FRAME_BEVEL);
$graph->SetTitleBackgroundFillStyle(TITLEBKG_FILLSTYLE_HSTRIPED,'lightblue','navy');

// Create a bar pot
$bplot = new BarPlot($datay);
$bplot->value->Show();
$bplot->value->SetFont(FF_VERDANA,FS_BOLD,8);
$bplot->SetValuePos('top');
$bplot->SetLegend('Bar Legend');
$graph->Add($bplot);

$pline = new PlotLine(HORIZONTAL,8,'red',2);
$pline->SetLegend('Line Legend');
$graph->legend->SetColumns(10);
$graph->Add($pline);

$graph->Stroke();
?>
