<?php
require_once 'jpgraph/jpgraph.php';
require_once 'jpgraph/jpgraph_bar.php';
require_once 'jpgraph/jpgraph_line.php';

$theme = isset($_GET['theme']) ? $_GET['theme'] : null;

$data = array(
    0 => array(0 => 79, 1 => -25, 2 => -7, 3 => 85, 4 => -26, 5 => -32),
    1 => array(0 => 76, 1 => 51, 2 => 86, 3 => 12, 4 => -7, 5 => 94),
    2 => array(0 => 49, 1 => 38, 2 => 7, 3 => -40, 4 => 9, 5 => -7),
    3 => array(0 => 69, 1 => 96, 2 => 49, 3 => 7, 4 => 92, 5 => -38),
    4 => array(0 => 68, 1 => 16, 2 => 82, 3 => -49, 4 => 50, 5 => 7),
    5 => array(0 => -37, 1 => 28, 2 => 32, 3 => 6, 4 => 13, 5 => 57),
    6 => array(0 => 24, 1 => -11, 2 => 7, 3 => 10, 4 => 51, 5 => 51),
    7 => array(0 => 3, 1 => -1, 2 => -12, 3 => 61, 4 => 10, 5 => 47),
    8 => array(0 => -47, 1 => -21, 2 => 43, 3 => 53, 4 => 36, 5 => 34),
);

// Create the graph. These two calls are always required
$graph = new Graph\Graph(400, 300);

$graph->SetScale("textlin");
if ($theme) {
    $graph->SetTheme(new $theme());
}
$theme_class = new OrangeTheme;
$graph->SetTheme($theme_class);

$plot = array();
// Create the bar plots
for ($i = 0; $i < 4; $i++) {
    $plot[$i] = new Plot\BarPlot($data[$i]);
    $plot[$i]->SetLegend('plot' . ($i + 1));
}
//$acc1 = new Plot\AccBarPlot(array($plot[0], $plot[1]));
//$acc1->value->Show();
$gbplot = new Plot\GroupBarPlot(array($plot[2], $plot[1]));

for ($i = 4; $i < 8; $i++) {
    $plot[$i] = new Plot\LinePlot($data[$i]);
    $plot[$i]->SetLegend('plot' . $i);
    $plot[$i]->value->Show();
}

$graph->Add($gbplot);
$graph->Add($plot[4]);

$title = "OrangeTheme Example";
$title = mb_convert_encoding($title, 'UTF-8');
$graph->title->Set($title);
$graph->xaxis->title->Set("X-title");
$graph->yaxis->title->Set("Y-title");

// Display the graph
$graph->Stroke();
