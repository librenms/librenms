<?php // content="text/plain; charset=utf-8"
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_line.php';
require_once 'jpgraph/jpgraph_bar.php';

function readsunspotdata($aFile, &$aYears, &$aSunspots)
{
    $lines = @file($aFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new JpGraphException('Can not read sunspot data file.');
    }
    foreach ($lines as $line => $datarow) {
        $split       = preg_split('/[\s]+/', $datarow);
        $aYears[]    = substr(trim($split[0]), 0, 4);
        $aSunspots[] = trim($split[1]);
    }
}

$year  = array();
$ydata = array();
readsunspotdata('yearssn.txt', $year, $ydata);

// Just keep the last 20 values in the arrays
$year  = array_slice($year, -20);
$ydata = array_slice($ydata, -20);

// Width and height of the graph
$width  = 600;
$height = 200;

// Create a graph instance
$graph = new Graph\Graph($width, $height);

// Specify what scale we want to use,
// text = txt scale for the X-axis
// int = integer scale for the Y-axis
$graph->SetScale('textint');

// Setup a title for the graph
$graph->title->Set('Sunspot example');

// Setup titles and X-axis labels
$graph->xaxis->title->Set('(year)');
$graph->xaxis->SetTickLabels($year);

// Setup Y-axis title
$graph->yaxis->title->Set('(# sunspots)');

// Create the bar plot
$barplot = new Plot\BarPlot($ydata);

// Add the plot to the graph
$graph->Add($barplot);

// Display the graph
$graph->Stroke();
