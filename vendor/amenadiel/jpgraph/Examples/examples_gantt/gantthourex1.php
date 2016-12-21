<?php // content="text/plain; charset=utf-8"
// Gantt hour example
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_gantt.php');

$graph = new GanttGraph();
$graph->SetMarginColor('blue:1.7');
$graph->SetColor('white');

$graph->SetBackgroundGradient('navy','white',GRAD_HOR,BGRAD_MARGIN);
$graph->scale->hour->SetBackgroundColor('lightyellow:1.5');
$graph->scale->hour->SetFont(FF_FONT1);
$graph->scale->day->SetBackgroundColor('lightyellow:1.5');
$graph->scale->day->SetFont(FF_FONT1,FS_BOLD);

$graph->title->Set("Example of hours in scale");
$graph->title->SetColor('white');
$graph->title->SetFont(FF_VERDANA,FS_BOLD,14);

$graph->ShowHeaders(GANTT_HDAY | GANTT_HHOUR);

$graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
$graph->scale->week->SetFont(FF_FONT1);
$graph->scale->hour->SetIntervall(4);

$graph->scale->hour->SetStyle(HOURSTYLE_HM24);
$graph->scale->day->SetStyle(DAYSTYLE_SHORTDAYDATE3);

$data = array(
    array(0,"  Label 1", "2001-01-26 04:00","2001-01-26 14:00"),
    array(1,"  Label 2", "2001-01-26 10:00","2001-01-26 18:00"),
    array(2,"  Label 3", "2001-01-26","2001-01-27 10:00")
);


for($i=0; $i<count($data); ++$i) {
	$bar = new GanttBar($data[$i][0],$data[$i][1],$data[$i][2],$data[$i][3],"[5%]",10);
	if( count($data[$i])>4 )
		$bar->title->SetFont($data[$i][4],$data[$i][5],$data[$i][6]);
	$bar->SetPattern(BAND_RDIAG,"yellow");
	$bar->SetFillColor("red");
	$graph->Add($bar);
}

$graph->Stroke();



?>


