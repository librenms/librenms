<?php
include ("../jpgraph.php");
include ("../jpgraph_line.php");
include ("../jpgraph_scatter.php");

$datay1 = array(4,26,15,44);

// Setup the graph
$graph = new Graph(300,200);
$graph->SetMarginColor('white');
$graph->SetScale("textlin");
$graph->SetFrame(false);
$graph->SetMargin(30,5,25,20);

// Setup the tab
$graph->tabtitle->Set(' Year 2003 ' );
$graph->tabtitle->SetFont(FF_ARIAL,FS_BOLD,13);
$graph->tabtitle->SetColor('darkred','#E1E1FF');

// Enable X-grid as well
$graph->xgrid->Show();

// Use months as X-labels
$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());

// Create the plot
$p1 = new LinePlot($datay1);
$p1->SetColor("navy");

// Use an image of favourite car as marker
$p1->mark->SetType(MARK_IMG,'saab_95.jpg',0.5);

// Displayes value on top of marker image
$p1->value->SetFormat('%d mil');
$p1->value->Show();
$p1->value->SetColor('darkred');
$p1->value->SetFont(FF_ARIAL,FS_BOLD,10);
// Increase the margin so that the value is printed avove tje
// img marker
$p1->value->SetMargin(14);

// Incent the X-scale so the first and last point doesn't
// fall on the edges
$p1->SetCenter();

$graph->Add($p1);

$graph->Stroke();

?>


