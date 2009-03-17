<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");

// Callback to negate the argument
function _cb_negate($aVal) {
    return round(-$aVal);
}

// A fake depth curve
$ydata = array(0,1,4,5,8,9,10,14,16,16,16,18,20,20,20,22,22.5,22,19,19,15,15,15,15,10,10,10,6,5,5,5,4,4,2,1,0);

// Negate all data
$n = count($ydata);
for($i=0; $i<$n; ++$i) {
    $ydata[$i] = round(-$ydata[$i]);
}

// Basic graph setup
$graph = new Graph(400,300,"auto");
$graph->SetScale("linlin");
$graph->img->SetMargin(50,50,60,40);	
$graph->SetMarginColor('darkblue');
$graph->SetColor('darkblue');
$graph->SetAxisStyle(AXSTYLE_BOXOUT);

$graph->title->Set("Depth curve. Dive #2");
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$graph->title->SetColor("white");

$graph->subtitle->Set("(Negated Y-axis)");
$graph->subtitle->SetFont(FF_FONT1,FS_NORMAL);
$graph->subtitle->SetColor("white");

// Setup axis
$graph->yaxis->SetLabelFormatCallback("_cb_negate");
$graph->xaxis->SetColor("lightblue","white");
$graph->yaxis->SetColor("lightblue","white");
$graph->ygrid->SetColor("blue");


$lp1 = new LinePlot($ydata);
$lp1->SetColor("yellow");
$lp1->SetWeight(2);


$graph->Add($lp1);
$graph->Stroke();
?>


