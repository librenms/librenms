<?php
//=============================================================================
// File:	ODOEX06.PHP
// Description: Example 1 for odometer graphs
// Created:	2002-02-22
// Version:	$Id$
// 
// Comment:
// Example file for odometer graph. This examples extends odoex05
// by changing the type of odometer to a full circle. This is as simple
// as changing (less than) one line of code.
//
// Copyright (C) 2002 Johan Persson. All rights reserved.
//=============================================================================
require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_odo.php');

//---------------------------------------------------------------------
// Create a new odometer graph (width=250, height=200 pixels)
//---------------------------------------------------------------------
$graph = new OdoGraph(250,250);

//---------------------------------------------------------------------
// Change the color of the odometer plotcanvas. NOT the odometer
// fill color itself.
//---------------------------------------------------------------------
$graph->SetColor("lightyellow");

//---------------------------------------------------------------------
// Specify title and subtitle using default fonts
// * Note each title may be multilines by using a '\n' as a line
// divider.
//---------------------------------------------------------------------
$graph->title->Set("Odometer title");
$graph->title->SetColor("white");
$graph->subtitle->Set("2002-02-13");
$graph->subtitle->SetColor("white");

//---------------------------------------------------------------------
// Specify caption.
// * (This is the text at the bottom of the graph.) The margins will
// automatically adjust to fit the height of the text. A caption
// may have multiple lines by including a '\n' character in the 
// string.
//---------------------------------------------------------------------
$graph->caption->Set("First caption row\n... second row");
$graph->caption->SetColor("white");

//---------------------------------------------------------------------
// Now we need to create an odometer to add to the graph.
// By default the scale will be 0 to 100
//---------------------------------------------------------------------
$odo = new Odometer(ODO_FULL); 

//---------------------------------------------------------------------
// Set color indication 
//---------------------------------------------------------------------
$odo->AddIndication(0,50,"green");
$odo->AddIndication(50,80,"yellow");
$odo->AddIndication(80,100,"red");

//---------------------------------------------------------------------
// Set the center area that will not be affected by the color bands
//---------------------------------------------------------------------
$odo->SetCenterAreaWidth(0.4);  // Fraction of radius

//---------------------------------------------------------------------
// Adjust scale ticks to be shown at 10 steps interval and scale
// labels at every second tick
//---------------------------------------------------------------------
$odo->scale->SetTicks(10,2);

//---------------------------------------------------------------------
// Make the tick marks 2 pixel wide
//---------------------------------------------------------------------
$odo->scale->SetTickWeight(2);

//---------------------------------------------------------------------
// Use a bold font for tick labels
//---------------------------------------------------------------------
$odo->scale->label->SetFont(FF_FONT1, FS_BOLD);

//---------------------------------------------------------------------
// Set display value for the odometer
//---------------------------------------------------------------------
$odo->needle->Set(78);

//---------------------------------------------------------------------
// Specify scale caption. Note that depending on the position of the
// indicator needle this label might be partially hidden. 
//---------------------------------------------------------------------
$odo->label->Set("% Passed");

//---------------------------------------------------------------------
// Set a new style for the needle
//---------------------------------------------------------------------
$odo->needle->SetStyle(NEEDLE_STYLE_MEDIUM_TRIANGLE);
$odo->needle->SetLength(0.7);  // Length as 70% of the radius
$odo->needle->SetFillColor("orange");

//---------------------------------------------------------------------
// Setup the second indicator needle
//---------------------------------------------------------------------
$odo->needle2->Set(24);
$odo->needle2->SetStyle(NEEDLE_STYLE_SMALL_TRIANGLE);
$odo->needle2->SetLength(0.55);  // Length as 70% of the radius
$odo->needle2->SetFillColor("lightgray");
$odo->needle2->Show();  


//---------------------------------------------------------------------
// Add the odometer to the graph
//---------------------------------------------------------------------
$graph->Add($odo);

//---------------------------------------------------------------------
// ... and finally stroke and stream the image back to the browser
//---------------------------------------------------------------------
$graph->Stroke();

// EOF
?>