<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_radar.php');

$titles=array('Planning','Quality','Time','RR','CR','DR');
$data=array(18, 40, 70, 90, 42,66);

$graph = new RadarGraph (300,280);

$graph->title->Set('Radar with marks');
$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);

$graph->SetTitles($titles);
$graph->SetCenter(0.5,0.55);
$graph->HideTickMarks();
$graph->SetColor('lightgreen@0.7');
$graph->axis->SetColor('darkgray');
$graph->grid->SetColor('darkgray');
$graph->grid->Show();

$graph->axis->title->SetFont(FF_ARIAL,FS_NORMAL,12);
$graph->axis->title->SetMargin(5);
$graph->SetGridDepth(DEPTH_BACK);
$graph->SetSize(0.6);

$plot = new RadarPlot($data);
$plot->SetColor('red@0.2');
$plot->SetLineWeight(1);
$plot->SetFillColor('red@0.7');

$plot->mark->SetType(MARK_IMG_SBALL,'red');

$graph->Add($plot);
$graph->Stroke();
?>
