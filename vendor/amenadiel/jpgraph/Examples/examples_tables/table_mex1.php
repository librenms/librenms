<?php
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_canvas.php');
require_once ('jpgraph/jpgraph_table.php');

// Setup graph context
$graph = new CanvasGraph(430,150);

// Setup the basic table
$data = array(
    array('', 'w631', 'w632', 'w633', 'w634', 'w635', 'w636'),
    array('Critical (sum)',13,17,15,8,3,9),
    array('High (sum)',34,35,26,20,22,16),
    array('Low (sum)',41,43,49,45,51,47),
    array('Sum',88,95,90,73,76,72)
    );

// Setup the basic table and font
$table = new GTextTable();
$table->Set($data);
$table->SetFont(FF_ARIAL,FS_NORMAL,11);

// Setup default column width
$table->SetMinColWidth(40);

// Setup defalt table alignment
$table->SetAlign('right');

// Turn off border
$table->SetBorder(0);

// Turn off grid
$table->setGrid(0);

// Setup font for row 4 and 0
$table->SetRowFont(4,FF_ARIAL,FS_BOLD,11);
$table->SetRowFont(0,FF_ARIAL,FS_BOLD,11);

// Setup color
$table->SetRowFillColor(4,'orange@0.5');
$table->SetFillColor(0,1,0,6,'teal@0.8');


// Setup grids
$table->SetRowGrid(4,1,'black',TGRID_DOUBLE2);
$table->SetColGrid(1,1,'black',TGRID_SINGLE);
$table->SetRowGrid(1,1,'black',TGRID_SINGLE);

// Add table to the graph
$graph->Add($table);

// Send it back to the client
$graph->Stroke();

?>

