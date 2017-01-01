<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
require_once 'jpgraph/jpgraph_line.php';

define('DATAPERMONTH', 40);

// Some data
$m = $gDateLocale->GetShortMonth();
$k = 0;
for ($i = 0; $i < 480; ++$i) {
    $datay[$i] = rand(1, 40);
    if ($i % DATAPERMONTH === 0) {
        $months[$i] = $m[(int) ($i / DATAPERMONTH)];
    } else {
        $months[$i] = 'xx';
    }

}

// new Graph\Graph with a drop shadow
$graph = new Graph\Graph(400, 200);
//$graph->SetShadow();

// Use a "text" X-scale
$graph->SetScale('textlin');

// Specify X-labels
$graph->xaxis->SetTickLabels($months);
$graph->xaxis->SetTextTickInterval(DATAPERMONTH, 0);
$graph->xaxis->SetTextLabelInterval(2);

// Set title and subtitle
$graph->title->Set('Textscale with tickinterval=2');

// Use built in font
$graph->title->SetFont(FF_FONT1, FS_BOLD);

$graph->SetBox(true, 'red');

// Create the bar plot
$lp1 = new Plot\LinePlot($datay);
$lp1->SetLegend('Temperature');

// The order the plots are added determines who's ontop
$graph->Add($lp1);

// Finally output the  image
$graph->Stroke();
