<?php // content="text/plain; charset=utf-8"
require_once '../../vendor/autoload.php';
\JpGraph\JpGraph::load();
\JpGraph\JpGraph::module('pie');
\JpGraph\JpGraph::module('pie3d');

// Some data
$data = array(40, 60, 21, 33);

$piepos = array(0.2, 0.35, 0.5, 0.25, 0.3, 0.7, 0.85, 0.7);
$titles = array('USA', 'Sweden', 'South America', 'Australia');

$n = count($piepos) / 2;

// A new Graph\Graph
$graph = new \PieGraph(450, 300, 'auto');

$theme_class = "PastelTheme";
$graph->SetTheme(new $theme_class());

// Setup background
$graph->SetBackgroundImage('worldmap1.jpg', BGIMG_FILLFRAME);

// Setup title
$graph->title->Set("Pie plots with background image");
$graph->title->SetColor('white');
$graph->SetTitleBackground('#4169E1', TITLEBKG_STYLE2, TITLEBKG_FRAME_FULL, '#4169E1', 10, 10, true);

$p = array();
// Create the plots
for ($i = 0; $i < $n; ++$i) {
    $p[] = new \PiePlot3D($data);
}
for ($i = 0; $i < $n; ++$i) {
    $graph->Add($p[$i]);
}

// Position the four pies and change color
for ($i = 0; $i < $n; ++$i) {
    $p[$i]->SetCenter($piepos[2 * $i], $piepos[2 * $i + 1]);
    $p[$i]->SetSliceColors(array('#1E90FF', '#2E8B57', '#ADFF2F', '#DC143C', '#BA55D3'));
}

// Set the titles
for ($i = 0; $i < $n; ++$i) {
    $p[$i]->title->Set($titles[$i]);
    $p[$i]->title->SetFont(FF_ARIAL, FS_NORMAL, 8);
}

for ($i = 0; $i < $n; ++$i) {
    $p[$i]->value->Show(false);
}

// Size of pie in fraction of the width of the graph
for ($i = 0; $i < $n; ++$i) {
    $p[$i]->SetSize(0.13);
}

for ($i = 0; $i < $n; ++$i) {
    $p[$i]->SetEdge(false);
    $p[$i]->ExplodeSlice(1, 7);
}

$graph->Stroke();
