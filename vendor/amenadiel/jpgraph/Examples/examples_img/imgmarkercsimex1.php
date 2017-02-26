<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_scatter.php';

$datay1 = array(4, 26, 15, 44);

// Setup the graph
$graph = new Graph\Graph(300, 200);
$graph->SetMarginColor('white');
$graph->SetScale("textlin");
$graph->SetFrame(false);
$graph->SetMargin(30, 5, 25, 20);

// Setup the tab
$graph->tabtitle->Set(' Year 2003 ');
$graph->tabtitle->SetFont(FF_ARIAL, FS_BOLD, 13);
$graph->tabtitle->SetColor('darkred', '#E1E1FF');

// Enable X-grid as well
$graph->xgrid->Show();

// Use months as X-labels
$graph->xaxis->SetTickLabels($gDateLocale->GetShortMonth());

// Create the plot
$p1 = new Plot\LinePlot($datay1);
$p1->SetColor("navy");

$p1->SetCSIMTargets(array('#1', '#2', '#3', '#4', '#5'));

// Use an image of favourite car as
$p1->mark->SetType(MARK_IMG, 'saab_95.jpg', 0.5);
//$p1->mark->SetType(MARK_SQUARE);

// Displayes value on top of marker image
$p1->value->SetFormat('%d mil');
$p1->value->Show();
$p1->value->SetColor('darkred');
$p1->value->SetFont(FF_ARIAL, FS_BOLD, 10);
// Increase the margin so that the value is printed avove tje
// img marker
$p1->value->SetMargin(14);

// Incent the X-scale so the first and last point doesn't
// fall on the edges
$p1->SetCenter();

$graph->Add($p1);

$graph->StrokeCSIM();
