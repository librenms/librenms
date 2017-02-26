<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

// Setup graph context
$graph = new CanvasGraph(430,150);

// Setup the basic table
$data = array(
    array('',        'w631','w632','w633','w634','w635','w636'),
    array('Critical (sum)',13,17,15,8,3,9),
    array('High (sum)',34,35,26,20,22,16),
    array('Low (sum)',41,43,49,45,51,47),
    array('Sum:',88,95,90,73,76,72)
    );

// Setup a basic table
$table = new GTextTable();
$table->Set($data);

// Setup fonts
$table->SetFont(FF_TIMES,FS_NORMAL,11);
$table->SetColFont(0,FF_ARIAL,FS_NORMAL,11);
$table->SetRowFont(0,FF_ARIAL,FS_NORMAL,11);
$table->SetRowFont(4,FF_TIMES,FS_BOLD,14);

// Turn off the grid
$table->SetGrid(0);

// Setup color
$table->SetRowFillColor(0,'lightgray@0.5');
$table->SetRowFillColor(4,'lightgray@0.5');
$table->SetColFillColor(0,'lightgray@0.5');
$table->SetFillColor(0,0,4,0,'lightgray@0.5');

// Set default minimum column width
$table->SetMinColWidth(45);

// Set default table alignment
$table->SetAlign('right');

// Add table to the graph
$graph->Add($table);

// and send it back to the client
$graph->Stroke();

?>

