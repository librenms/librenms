<?php // content="text/plain; charset=utf-8"
// $Id: horizbarex4.php,v 1.4 2002/11/17 23:59:27 aditus Exp $
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

$datay = array(1992, 1993, 1995, 1996, 1997, 1998, 2001);

// Size of graph
$width  = 400;
$height = 500;

// Set the basic parameters of the graph
$graph = new Graph\Graph($width, $height);
$graph->SetScale('textlin');

$top    = 60;
$bottom = 30;
$left   = 80;
$right  = 30;
$graph->Set90AndMargin($left, $right, $top, $bottom);

// Nice shadow
$graph->SetShadow();

// Setup labels
$lbl = array("Andrew\nTait", "Thomas\nAnderssen", "Kevin\nSpacey", "Nick\nDavidsson",
    "David\nLindquist", "Jason\nTait", "Lorin\nPersson");
$graph->xaxis->SetTickLabels($lbl);

// Label align for X-axis
$graph->xaxis->SetLabelAlign('right', 'center', 'right');

// Label align for Y-axis
$graph->yaxis->SetLabelAlign('center', 'bottom');

// Titles
$graph->title->Set('Number of incidents');

// Create a bar pot
$bplot = new Plot\BarPlot($datay);
$bplot->SetFillColor('orange');
$bplot->SetWidth(0.5);
$bplot->SetYMin(1990);

$graph->Add($bplot);

$graph->Stroke();
