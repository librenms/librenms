<?php
//=============================================================================
// File:	ODOEX011.PHP
// Description: Example 0 for odometer graphs
// Created:	2002-02-22
// Version:	$Id$
// 
// Comment:
// Example file for odometer graph. Extends odoex10.php with graph titles
// and captions and also adds individual captions for each odometer.

//
// Copyright (C) 2002 Johan Persson. All rights reserved.
//=============================================================================
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

//---------------------------------------------------------------------
// Create a new odometer graph (width=200, height=400 pixels)
//---------------------------------------------------------------------
$graph = new OdoGraph(200,370);
$graph->SetShadow();

//---------------------------------------------------------------------
// Specify title and subtitle using default fonts
// * Note each title may be multilines by using a '\n' as a line
// divider.
//---------------------------------------------------------------------
$graph->title->Set("Result from 2002");
$graph->title->SetColor("white");
$graph->subtitle->Set("O1 - W-Site");
$graph->subtitle->SetColor("white");

//---------------------------------------------------------------------
// Specify caption.
// * (This is the text at the bottom of the graph.) The margins will
// automatically adjust to fit the height of the text. A caption
// may have multiple lines by including a '\n' character in the 
// string.
//---------------------------------------------------------------------
$graph->caption->Set("Fig1. Values within 85%\nconfidence intervall");
$graph->caption->SetColor("white");

//---------------------------------------------------------------------
// We will display three odometers stacked vertically
// The first thing to do is to create them
//---------------------------------------------------------------------
$odo1 = new Odometer(); 
$odo2 = new Odometer(); 
$odo3 = new Odometer(); 


//---------------------------------------------------------------------
// Set caption for each odometer
//---------------------------------------------------------------------
$odo1->caption->Set("April");
$odo1->caption->SetFont(FF_FONT2,FS_BOLD);
$odo2->caption->Set("May");
$odo2->caption->SetFont(FF_FONT2,FS_BOLD);
$odo3->caption->Set("June");
$odo3->caption->SetFont(FF_FONT2,FS_BOLD);

//---------------------------------------------------------------------
// Set Indicator bands for the odometers
//---------------------------------------------------------------------
$odo1->AddIndication(80,100,"red");
$odo2->AddIndication(20,30,"green");
$odo2->AddIndication(65,100,"red");
$odo3->AddIndication(60,90,"yellow");
$odo3->AddIndication(90,100,"red");

//---------------------------------------------------------------------
// Set display values for the odometers
//---------------------------------------------------------------------
$odo1->needle->Set(17);
$odo2->needle->Set(47);
$odo3->needle->Set(86);

$odo1->needle->SetFillColor("blue");
$odo2->needle->SetFillColor("yellow:0.7");
$odo3->needle->SetFillColor("black");
$odo3->needle->SetColor("black");


//---------------------------------------------------------------------
// Set scale label properties
//---------------------------------------------------------------------
$odo1->scale->label->SetColor("navy");
$odo2->scale->label->SetColor("blue");
$odo3->scale->label->SetColor("darkred");

$odo1->scale->label->SetFont(FF_FONT1);
$odo2->scale->label->SetFont(FF_FONT2,FS_BOLD);
$odo3->scale->label->SetFont(FF_ARIAL,FS_BOLD,10);

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