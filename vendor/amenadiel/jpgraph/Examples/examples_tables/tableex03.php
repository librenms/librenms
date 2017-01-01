<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

$cols = 4;
$rows = 3;
$data = array( array('2007'),
               array('','Q1','','','Q2'),
	       array('','Jan','Feb','Mar','Apr','May','Jun'),
	       array('Min','15.2', '12.5', '9.9', '70.0', '22.4','21.5'),
	       array('Max','23.9', '14.2', '18.6', '71.3','66.8','42.6'));

$q=1;

$graph = new CanvasGraph(350,200);

$table = new GTextTable($cols,$rows);
$table->Init();
$table->Set($data);
$table->SetBorder(2,'black');

// Setup top row with the year title
$table->MergeCells(0,0,0,6);
$table->SetRowFont(0,FF_ARIAL,FS_BOLD,16);
$table->SetRowColor(0,'navy');
$table->SetRowAlign(0,'center');

// Setup quarter header
$table->MergeCells(1,1,1,3);
$table->MergeCells(1,4,1,6);
$table->SetRowAlign(1,'center');
$table->SetRowFont(1,FF_ARIAL,FS_BOLD,10);
$table->SetRowColor(1,'navy');
$table->SetRowFillColor(1,'lightgray');
$table->SetRowGrid(2,'',0); // Turn off the gridline just under the top row

// Setup row and column headers
$table->SetRowFont(2,FF_ARIAL,FS_NORMAL,11);
$table->SetRowColor(2,'navy');
$table->SetRowFillColor(2,'lightgray');

$table->SetColFont(0,FF_ARIAL,FS_NORMAL,11);
$table->SetColColor(0,'navy');
$table->SetColFillColor(0,'lightgray');

$table->SetCellFillColor(0,0,'lightgreen');
$table->SetCellFillColor(1,0,'lightgreen');
$table->SetCellFillColor(2,0,'lightgreen');

// Highlight cell 2,3
$table->SetCellFillColor(4,3,'yellow');

$graph->Add($table);
$graph->Stroke();

?>

