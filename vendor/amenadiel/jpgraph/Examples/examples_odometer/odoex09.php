<?php
//=============================================================================
// File:	ODOEX09.PHP
// Description: Example 0 for odometer graphs
// Created:	2002-02-22
// Version:	$Id$
// 
// Comment:
// Example file for odometer graph. Extends odoex00.php to show how multiple
// odometers may be combined in the same graph.
//
// Copyright (C) 2002 Johan Persson. All rights reserved.
//=============================================================================
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

//---------------------------------------------------------------------
// Create a new odometer graph (width=250, height=200 pixels)
//---------------------------------------------------------------------
$graph = new OdoGraph(200,300);

//---------------------------------------------------------------------
// We will display three odometers stacked vertically
// The first thing to do is to create them
//---------------------------------------------------------------------
$odo1 = new Odometer(); 
$odo2 = new Odometer(); 
$odo3 = new Odometer(); 

//---------------------------------------------------------------------
// Set display value for the odometers
//---------------------------------------------------------------------
$odo1->needle->Set(17);
$odo2->needle->Set(47);
$odo3->needle->Set(86);

//---------------------------------------------------------------------
// Add the odometers to the graph using a vertical layout
//---------------------------------------------------------------------
$l1 = new LayoutVert( array($odo1,$odo2,$odo3) ) ;
$graph->Add( $l1 );

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>