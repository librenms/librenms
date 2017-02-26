<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

// Callback function for Y-scale to get 1000 separator on labels
function separator1000($aVal)
{
    return number_format($aVal);
}

function separator1000_usd($aVal)
{
    return '$' . number_format($aVal);
}

// Some data
$datay = array(120567, 134013, 192000, 87000);

// Create the graph and setup the basic parameters
$graph = new Graph\Graph(500, 300, 'auto');
$graph->img->SetMargin(80, 30, 30, 40);
$graph->SetScale('textint');
$graph->SetShadow();
$graph->SetFrame(false); // No border around the graph

// Add some grace to the top so that the scale doesn't
// end exactly at the max value.
// The grace value is the percetage of additional scale
// value we add. Specifying 50 means that we add 50% of the
// max value
$graph->yaxis->scale->SetGrace(50);
$graph->yaxis->SetLabelFormatCallback('separator1000');

// Setup X-axis labels
$a = $gDateLocale->GetShortMonth();
$graph->xaxis->SetTickLabels($a);
$graph->xaxis->SetFont(FF_FONT2);

// Setup graph title ands fonts
$graph->title->Set('Example of Y-scale callback formatting');
$graph->title->SetFont(FF_FONT2, FS_BOLD);
$graph->xaxis->title->Set('Year 2002');
$graph->xaxis->title->SetFont(FF_FONT2, FS_BOLD);

// Create a bar pot
$bplot = new Plot\BarPlot($datay);
$bplot->SetFillColor('orange');
$bplot->SetWidth(0.5);
$bplot->SetShadow();

// Setup the values that are displayed on top of each bar
$bplot->value->Show();

// Must use TTF fonts if we want text at an arbitrary angle
$bplot->value->SetFont(FF_ARIAL, FS_BOLD);
$bplot->value->SetAngle(45);
$bplot->value->SetFormatCallback('separator1000_usd');

// Black color for positive values and darkred for negative values
$bplot->value->SetColor('black', 'darkred');
$graph->Add($bplot);

// Finally stroke the graph
$graph->Stroke();
