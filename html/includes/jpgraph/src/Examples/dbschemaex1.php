<?php
/*=======================================================================
// File: 	DBSCHEMAEX1.PHP
// Description:	Draw a DB schema of the DDDA architecture
// Created: 	2002-08-25
// Ver:		$Id: dbschemaex1.php,v 1.1 2002/08/27 20:08:57 aditus Exp $
//
// License:     This code is released under QPL
//              Copyright (C) 2001,2002 Johan Persson
// Note:        The actual drawing of the tables are semi-automatically
//              but you can easily adjust the individual tables position
//              with the 'tblposadj' array. 
//
//========================================================================
*/
include "../jpgraph.php";
include "../jpgraph_canvas.php";
include "../jpgraph_canvtools.php";
include "../imgdbschema.inc";
include "../jpdb.php";


// Global callback to format the table header names
function FormatTblName($aName) {
    // We want to replace any specifi references to the
    // 'JpGraph' project with the generic '<project>'
    return str_replace('JpGraph','<project>', $aName);
}

// Global callback to format each field name in the table
function FormatFldName($aName,$aTable) {
    return $aName;
}


class Driver {

    var $ig, $img, $iscale, $ishape;
    var $iymax,$ixmax;
    var $iwidth,$iheight;

    function Driver() {

	// Define Image size and coordinate grid space to work within
	$this->iwidth = 600;
	$this->iheight= 750;
	$this->iymax  = 50;
	$this->ixmax  = 55;

	// Setup a basic canvas
	$this->ig = new CanvasGraph($this->iwidth,$this->iheight,'auto');
	$this->img = $this->ig->img;

	// Define the scale to be used
	$this->iscale = new CanvasScale($this->ig);
	$this->iscale->Set(0,$this->ixmax,0,$this->iymax);
	$this->ishape = new Shape($this->ig,$this->iscale);

	// A small frame around the canvas
	$this->ig->SetMargin(2,3,2,3);
	$this->ig->SetMarginColor("teal");
	$this->ig->InitFrame();

    }

    function Run() {

	$leftm=1.5;	// Left margin (for table schemes) 
	$topm=5;	// Top margin (for table schemes) 
	$tblwidth=15;	// Individual table width
	$tlo=1;		// Offset for top line

	// Add the background color for the project specific tables
	$this->ishape->IndentedRectangle($leftm,$topm-1,3*$tblwidth+$tlo+6,45,
					 $tlo+2*$tblwidth+2,30,CORNER_BOTTOMLEFT,
					 'lightblue');

	// Stroke the tables (series of x,y offsets, If =-1 then use the
	// automtic positioning
	$tblposadj=array($tlo,0,$tblwidth+$tlo+2,0,2*$tblwidth+$tlo+4,
			 0,-1,16,-1,16);
	$dbschema = new ImgDBSchema('jpgraph_doc','FormatTblName','FormatFldName');
	$dbschema->SetMargin($leftm,$topm);
	$dbschema->SetTableWidth($tblwidth);
	$dbschema->Stroke($this->img,$this->iscale,$tblposadj);

	$tt = new CanvasRectangleText();
	$tt->SetFillColor('');
	$tt->SetColor('');
	$tt->SetFontColor('navy');

	// Add explanation
	$tt->SetFont(FF_ARIAL,FS_NORMAL,12);
	$tt->Set('Project specific tables',$tblwidth+$leftm+3,16,15);
	$tt->Stroke($this->img,$this->iscale);

	// Add title
	$tt->SetColor('');
	$tt->SetFont(FF_VERDANA,FS_BOLD,26);
	$tt->Set('DDDA - DB Schema',9,0.5,30);
	$tt->Stroke($this->img,$this->iscale);

	// Add a version and date
	$tt->SetFillColor('yellow');
	$tt->SetFont(FF_FONT1,FS_NORMAL,10);
	$tt->Set("Generated: ".date("ymd H:i",time()),1,$this->iymax*0.96,15); 
	$tt->Stroke($this->img,$this->iscale);

	$this->ig->Stroke();
    }
}

$driver = new Driver();
$driver->Run();

?>

