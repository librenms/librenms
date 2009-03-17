<?php
// $Id: piec_csimex1.php,v 1.1.2.1 2003/10/09 21:05:39 aditus Exp $
// Example of pie with center circle
include ("../jpgraph.php");
include ("../jpgraph_pie.php");

// Some data
$data = array(50,28,25,27,31,20);

// A new pie graph
$graph = new PieGraph(400,400);

// If you don't want any  border just uncomment this line
// $graph->SetFrame(false);

// Uncomment this line to add a drop shadow to the border
// $graph->SetShadow();

// Setup title
$graph->title->Set("CSIM Center Pie plot ex 1");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,18);
$graph->title->SetMargin(8); // Add a little bit more margin from the top

// Create the pie plot
$p1 = new PiePlotC($data);

// Set the radius of pie (as fraction of image size)
$p1->SetSize(0.32);

// Move the center of the pie slightly to the top of the image
$p1->SetCenter(0.5,0.45);

// Label font and color setup
$p1->value->SetFont(FF_ARIAL,FS_BOLD,12);
$p1->value->SetColor('white');

// Setup the title on the center circle
$p1->midtitle->Set("Test mid\nRow 1\nRow 2");
$p1->midtitle->SetFont(FF_ARIAL,FS_NORMAL,14);

// Set color for mid circle
$p1->SetMidColor('yellow');

// Use percentage values in the legends values (This is also the default)
$p1->SetLabelType(PIE_VALUE_PER);

// The label array values may have printf() formatting in them. The argument to the
// form,at string will be the value of the slice (either the percetage or absolute
// depending on what was specified in the SetLabelType() above.
$lbl = array("adam\n%.1f%%","bertil\n%.1f%%","johan\n%.1f%%",
	     "peter\n%.1f%%","daniel\n%.1f%%","erik\n%.1f%%");
$p1->SetLabels($lbl);

// Uncomment this line to remove the borders around the slices
// $p1->ShowBorder(false);

// Add drop shadow to slices
$p1->SetShadow();

// Explode all slices 15 pixels
$p1->ExplodeAll(15);

// Setup the CSIM targets
$targ=array("piec_csimex1.php#1","piec_csimex1.php#2","piec_csimex1.php#3",
	    "piec_csimex1.php#4","piec_csimex1.php#5","piec_csimex1.php#6");
$alts=array("val=%d","val=%d","val=%d","val=%d","val=%d","val=%d");
$p1->SetCSIMTargets($targ,$alts);
$p1->SetMidCSIM("piec_csimex1.php#7","Center");


// Setup a small help text in the image
$txt = new Text("Note: This is an example of image map. Hold\nyour mouse over the slices to see the values.\nThe URL just points back to this page");
$txt->SetFont(FF_FONT1,FS_BOLD);
$txt->Pos(0.5,0.97,'center','bottom');
$txt->SetBox('yellow','black');
$txt->SetShadow();
$graph->AddText($txt);

// Add plot to pie graph
$graph->Add($p1);

// .. and send the image on it's marry way to the browser
$graph->StrokeCSIM();

?>


