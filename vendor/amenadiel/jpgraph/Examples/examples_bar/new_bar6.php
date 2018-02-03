<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

require_once 'jpgraph/jpgraph_line.php';

//bar1
$data1y = array(115, 130, 135, 130, 110, 130, 130, 150, 130, 130, 150, 120);
//bar2
$data2y = array(180, 200, 220, 190, 170, 195, 190, 210, 200, 205, 195, 150);
//bar3
$data3y = array(220, 230, 210, 175, 185, 195, 200, 230, 200, 195, 180, 130);
$data4y = array(40, 45, 70, 80, 50, 75, 70, 70, 80, 75, 80, 50);
$data5y = array(20, 20, 25, 22, 30, 25, 35, 30, 27, 25, 25, 45);
//line1
$data6y = array(50, 58, 60, 58, 53, 58, 57, 60, 58, 58, 57, 50);
foreach ($data6y as &$y) {
    $y -= 10;
}

// Create the graph. These two calls are always required
$graph = new Graph\Graph(750, 320, 'auto');
$graph->SetScale("textlin");
$graph->SetY2Scale("lin", 0, 90);
$graph->SetY2OrderBack(false);

$graph->SetMargin(35, 50, 20, 5);

$theme_class = new UniversalTheme;
$graph->SetTheme($theme_class);

$graph->yaxis->SetTickPositions(array(0, 50, 100, 150, 200, 250, 300, 350), array(25, 75, 125, 175, 275, 325));
$graph->y2axis->SetTickPositions(array(30, 40, 50, 60, 70, 80, 90));

$months = $gDateLocale->GetShortMonth();
$months = array_merge(array_slice($months, 3, 9), array_slice($months, 0, 3));
$graph->SetBox(false);

$graph->ygrid->SetFill(false);
$graph->xaxis->SetTickLabels(array('A', 'B', 'C', 'D'));
$graph->yaxis->HideLine(false);
$graph->yaxis->HideTicks(false, false);
// Setup month as labels on the X-axis
$graph->xaxis->SetTickLabels($months);

// Create the bar plots
$b1plot = new Plot\BarPlot($data1y);
$b2plot = new Plot\BarPlot($data2y);

$b3plot = new Plot\BarPlot($data3y);
$b4plot = new Plot\BarPlot($data4y);
$b5plot = new Plot\BarPlot($data5y);

$lplot = new Plot\LinePlot($data6y);

// Create the grouped bar plot
$gbbplot = new Plot\AccBarPlot(array($b3plot, $b4plot, $b5plot));
$gbplot  = new Plot\GroupBarPlot(array($b1plot, $b2plot, $gbbplot));

// ...and add it to the graPH
$graph->Add($gbplot);
$graph->AddY2($lplot);

$b1plot->SetColor("#0000CD");
$b1plot->SetFillColor("#0000CD");
$b1plot->SetLegend("Cliants");

$b2plot->SetColor("#B0C4DE");
$b2plot->SetFillColor("#B0C4DE");
$b2plot->SetLegend("Machines");

$b3plot->SetColor("#8B008B");
$b3plot->SetFillColor("#8B008B");
$b3plot->SetLegend("First Track");

$b4plot->SetColor("#DA70D6");
$b4plot->SetFillColor("#DA70D6");
$b4plot->SetLegend("All");

$b5plot->SetColor("#9370DB");
$b5plot->SetFillColor("#9370DB");
$b5plot->SetLegend("Single Only");

$lplot->SetBarCenter();
$lplot->SetColor("yellow");
$lplot->SetLegend("Houses");
$lplot->mark->SetType(MARK_X, '', 1.0);
$lplot->mark->SetWeight(2);
$lplot->mark->SetWidth(8);
$lplot->mark->setColor("yellow");
$lplot->mark->setFillColor("yellow");

$graph->legend->SetFrameWeight(1);
$graph->legend->SetColumns(6);
$graph->legend->SetColor('#4E4E4E', '#00A78A');

$band = new Plot\PlotBand(VERTICAL, BAND_RDIAG, 11, "max", 'khaki4');
$band->ShowFrame(true);
$band->SetOrder(DEPTH_BACK);
$graph->Add($band);

$graph->title->Set("Combineed Line and Bar plots");

// Display the graph
$graph->Stroke();
