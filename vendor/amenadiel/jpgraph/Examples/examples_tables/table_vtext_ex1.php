<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

// Setup a basic canvas graph context
$graph = new CanvasGraph(430,600);

// Setup the basic table
$data = array(
    array('GROUP 1O',        'w631','w632','w633','w634','w635','w636'),
    array('Critical (sum)',13,17,15,8,3,9),
    array('High (sum)',34,35,26,20,22,16),
    array('Low (sum)',41,43,49,45,51,47),
    array('Sum:',88,95,90,73,76,72)
    );

// Setup the basic table and default font
$table = new GTextTable();
$table->Set($data);
$table->SetFont(FF_TIMES,FS_NORMAL,11);

// Default table alignment
$table->SetAlign('right');

// Adjust font in (0,0)
$table->SetCellFont(0,0,FF_TIMES,FS_BOLD,14);

// Rotate all textxs in row  0
$table->SetRowTextOrientation(0,90);

// Adjust alignment in cell (0,0)
$table->SetCellAlign(0,0,'center','center');

// Add table to graph
$graph->Add($table);

// Send back table to client
$graph->Stroke();

?>

