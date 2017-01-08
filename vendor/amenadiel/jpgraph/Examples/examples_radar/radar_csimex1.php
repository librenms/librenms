<?php // content="text/plain; charset=utf-8"
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_radar.php');

$titles=array('Planning','Quality','Time','RR','CR','DR');
$data=array(18, 40, 70, 90, 42, 66);

$n = count($data);
for( $i=0; $i < $n; ++$i ) {
	$targets[$i] = "#$i";
	$alts[$i] = "Data point #$i";
}

$graph = new RadarGraph (300,280);

$graph->title->Set('Radar with marks');
$graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
$graph->title->SetMargin(10);

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
$plot->SetLineWeight(2);
$plot->SetFillColor('red@0.7');
$plot->mark->SetType(MARK_IMG_DIAMOND,'red',0.6);
$plot->mark->SetFillColor('darkred');
$plot->SetCSIMTargets( $targets , $alts );

$graph->Add($plot);
$graph->StrokeCSIM();
?>
