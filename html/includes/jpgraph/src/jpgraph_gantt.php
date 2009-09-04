<?php
/*=======================================================================
// File:	JPGRAPH_GANTT.PHP
// Description:	JpGraph Gantt plot extension
// Created: 	2001-11-12
// Ver:		$Id: jpgraph_gantt.php 1091 2009-01-18 22:57:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================
*/

require_once('jpgraph_plotband.php'); 
require_once('jpgraph_iconplot.php'); 
require_once('jpgraph_plotmark.inc.php');

// Maximum size for Automatic Gantt chart
define('MAX_GANTTIMG_SIZE_W',4000);
define('MAX_GANTTIMG_SIZE_H',5000);

// Scale Header types
define("GANTT_HDAY",1);
define("GANTT_HWEEK",2);
define("GANTT_HMONTH",4);
define("GANTT_HYEAR",8);
define("GANTT_HHOUR",16);
define("GANTT_HMIN",32);

// Bar patterns
define("GANTT_RDIAG",BAND_RDIAG);	// Right diagonal lines
define("GANTT_LDIAG",BAND_LDIAG); // Left diagonal lines
define("GANTT_SOLID",BAND_SOLID); // Solid one color
define("GANTT_VLINE",BAND_VLINE); // Vertical lines
define("GANTT_HLINE",BAND_HLINE);  // Horizontal lines
define("GANTT_3DPLANE",BAND_3DPLANE);  // "3D" Plane
define("GANTT_HVCROSS",BAND_HVCROSS);  // Vertical/Hor crosses
define("GANTT_DIAGCROSS",BAND_DIAGCROSS); // Diagonal crosses

// Conversion constant
define("SECPERDAY",3600*24);

// Locales. ONLY KEPT FOR BACKWARDS COMPATIBILITY
// You should use the proper locale strings directly 
// from now on. 
define("LOCALE_EN","en_UK");
define("LOCALE_SV","sv_SE");

// Layout of bars
define("GANTT_EVEN",1);
define("GANTT_FROMTOP",2);

// Style for minute header
define("MINUTESTYLE_MM",0);		// 15
define("MINUTESTYLE_CUSTOM",2);		// Custom format


// Style for hour header
define("HOURSTYLE_HM24",0);		// 13:10
define("HOURSTYLE_HMAMPM",1);		// 1:10pm
define("HOURSTYLE_H24",2);		// 13
define("HOURSTYLE_HAMPM",3);		// 1pm
define("HOURSTYLE_CUSTOM",4);		// User defined

// Style for day header
define("DAYSTYLE_ONELETTER",0);		// "M"
define("DAYSTYLE_LONG",1);		// "Monday"
define("DAYSTYLE_LONGDAYDATE1",2);	// "Monday 23 Jun"
define("DAYSTYLE_LONGDAYDATE2",3);	// "Monday 23 Jun 2003"
define("DAYSTYLE_SHORT",4);		// "Mon"
define("DAYSTYLE_SHORTDAYDATE1",5);	// "Mon 23/6"
define("DAYSTYLE_SHORTDAYDATE2",6);	// "Mon 23 Jun"
define("DAYSTYLE_SHORTDAYDATE3",7);	// "Mon 23"
define("DAYSTYLE_SHORTDATE1",8);	// "23/6"
define("DAYSTYLE_SHORTDATE2",9);	// "23 Jun"
define("DAYSTYLE_SHORTDATE3",10);	// "Mon 23"
define("DAYSTYLE_SHORTDATE4",11);	// "23"
define("DAYSTYLE_CUSTOM",12);		// "M"

// Styles for week header
define("WEEKSTYLE_WNBR",0);
define("WEEKSTYLE_FIRSTDAY",1);
define("WEEKSTYLE_FIRSTDAY2",2);
define("WEEKSTYLE_FIRSTDAYWNBR",3);
define("WEEKSTYLE_FIRSTDAY2WNBR",4);

// Styles for month header
define("MONTHSTYLE_SHORTNAME",0);
define("MONTHSTYLE_LONGNAME",1);
define("MONTHSTYLE_LONGNAMEYEAR2",2);
define("MONTHSTYLE_SHORTNAMEYEAR2",3);
define("MONTHSTYLE_LONGNAMEYEAR4",4);
define("MONTHSTYLE_SHORTNAMEYEAR4",5);
define("MONTHSTYLE_FIRSTLETTER",6);


// Types of constrain links
define('CONSTRAIN_STARTSTART',0);
define('CONSTRAIN_STARTEND',1);
define('CONSTRAIN_ENDSTART',2);
define('CONSTRAIN_ENDEND',3);

// Arrow direction for constrain links
define('ARROW_DOWN',0);
define('ARROW_UP',1);
define('ARROW_LEFT',2);
define('ARROW_RIGHT',3);

// Arrow type for constrain type
define('ARROWT_SOLID',0);
define('ARROWT_OPEN',1);

// Arrow size for constrain lines
define('ARROW_S1',0);
define('ARROW_S2',1);
define('ARROW_S3',2);
define('ARROW_S4',3);
define('ARROW_S5',4);

// Activity types for use with utility method CreateSimple()
define('ACTYPE_NORMAL',0);
define('ACTYPE_GROUP',1);
define('ACTYPE_MILESTONE',2);

define('ACTINFO_3D',1);
define('ACTINFO_2D',0);


// Check if array_fill() exists
if (!function_exists('array_fill')) {
    function array_fill($iStart, $iLen, $vValue) {
	$aResult = array();
	for ($iCount = $iStart; $iCount < $iLen + $iStart; $iCount++) {
	    $aResult[$iCount] = $vValue;
	}
	return $aResult;
    }
}

//===================================================
// CLASS GanttActivityInfo
// Description: 
//===================================================
class GanttActivityInfo {
    public $iShow=true;
    public $iLeftColMargin=4,$iRightColMargin=1,$iTopColMargin=1,$iBottomColMargin=3;
    public $vgrid = null;
    private $iColor='black';
    private $iBackgroundColor='lightgray';
    private $iFFamily=FF_FONT1,$iFStyle=FS_NORMAL,$iFSize=10,$iFontColor='black';
    private $iTitles=array();
    private $iWidth=array(),$iHeight=-1;
    private $iTopHeaderMargin = 4;
    private $iStyle=1;
    private $iHeaderAlign='center';

    function GanttActivityInfo() {
	$this->vgrid = new LineProperty();
    }

    function Hide($aF=true) {
	$this->iShow=!$aF;
    }

    function Show($aF=true) {
	$this->iShow=$aF;
    }

    // Specify font
    function SetFont($aFFamily,$aFStyle=FS_NORMAL,$aFSize=10) {
	$this->iFFamily = $aFFamily;
	$this->iFStyle	 = $aFStyle;
	$this->iFSize	 = $aFSize;
    }

    function SetStyle($aStyle) {
	$this->iStyle = $aStyle;
    }

    function SetColumnMargin($aLeft,$aRight) {
	$this->iLeftColMargin = $aLeft;
	$this->iRightColMargin = $aRight;
    }

    function SetFontColor($aFontColor) {
	$this->iFontColor = $aFontColor;
    }

    function SetColor($aColor) {
	$this->iColor = $aColor;
    }

    function SetBackgroundColor($aColor) {
	$this->iBackgroundColor = $aColor;
    }

    function SetColTitles($aTitles,$aWidth=null) {
	$this->iTitles = $aTitles;
	$this->iWidth = $aWidth;
    }

    function SetMinColWidth($aWidths) {
	$n = min(count($this->iTitles),count($aWidths));
	for($i=0; $i < $n; ++$i ) {
	    if( !empty($aWidths[$i]) ) {
		if( empty($this->iWidth[$i]) ) {
		    $this->iWidth[$i] = $aWidths[$i];
		}
		else {
		    $this->iWidth[$i] = max($this->iWidth[$i],$aWidths[$i]);
		}
	    }
	}
    }

    function GetWidth($aImg) {
	$txt = new TextProperty();
	$txt->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	$n = count($this->iTitles) ;
	$rm=$this->iRightColMargin;
	$w = 0;
	for($h=0, $i=0; $i < $n; ++$i ) {
	    $w += $this->iLeftColMargin;
	    $txt->Set($this->iTitles[$i]);
	    if( !empty($this->iWidth[$i]) ) {
		$w1 = max($txt->GetWidth($aImg)+$rm,$this->iWidth[$i]);
	    }
	    else {
		$w1 = $txt->GetWidth($aImg)+$rm;
	    }
	    $this->iWidth[$i] = $w1;
	    $w += $w1;
	    $h = max($h,$txt->GetHeight($aImg));
	}
	$this->iHeight = $h+$this->iTopHeaderMargin;
        $txt='';
	return $w;
    }
    
    function GetColStart($aImg,&$aStart,$aAddLeftMargin=false) {
	$n = count($this->iTitles) ;
	$adj = $aAddLeftMargin ? $this->iLeftColMargin : 0;
	$aStart=array($aImg->left_margin+$adj);
	for( $i=1; $i < $n; ++$i ) {
	    $aStart[$i] = $aStart[$i-1]+$this->iLeftColMargin+$this->iWidth[$i-1];
	}
    }
    
    // Adjust headers left, right or centered
    function SetHeaderAlign($aAlign) {
	$this->iHeaderAlign=$aAlign;
    }

    function Stroke($aImg,$aXLeft,$aYTop,$aXRight,$aYBottom,$aUseTextHeight=false) {

	if( !$this->iShow ) return;

	$txt = new TextProperty();
	$txt->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	$txt->SetColor($this->iFontColor);
	$txt->SetAlign($this->iHeaderAlign,'top');
	$n=count($this->iTitles);

	if( $n == 0 ) 
	    return;
	
	$x = $aXLeft;
	$h = $this->iHeight;
	$yTop = $aUseTextHeight ? $aYBottom-$h-$this->iTopColMargin-$this->iBottomColMargin : $aYTop ;

	if( $h < 0 ) {
	    JpGraphError::RaiseL(6001);
//('Internal error. Height for ActivityTitles is < 0');
	}

	$aImg->SetLineWeight(1);
	// Set background color
	$aImg->SetColor($this->iBackgroundColor);
	$aImg->FilledRectangle($aXLeft,$yTop,$aXRight,$aYBottom-1);

	if( $this->iStyle == 1 ) {
	    // Make a 3D effect
	    $aImg->SetColor('white');
	    $aImg->Line($aXLeft,$yTop+1,
			$aXRight,$yTop+1);
	}
	
	for($i=0; $i < $n; ++$i ) {
	    if( $this->iStyle == 1 ) {
		// Make a 3D effect
		$aImg->SetColor('white');
		$aImg->Line($x+1,$yTop,$x+1,$aYBottom);
	    }
	    $x += $this->iLeftColMargin;
	    $txt->Set($this->iTitles[$i]);
	    
	    // Adjust the text anchor position according to the choosen alignment
	    $xp = $x;
	    if( $this->iHeaderAlign == 'center' ) {
		$xp = (($x-$this->iLeftColMargin)+($x+$this->iWidth[$i]))/2;
	    }
	    elseif( $this->iHeaderAlign == 'right' ) {
		$xp = $x +$this->iWidth[$i]-$this->iRightColMargin;
	    }
		    
	    $txt->Stroke($aImg,$xp,$yTop+$this->iTopHeaderMargin);
	    $x += $this->iWidth[$i];
	    if( $i < $n-1 ) {
		$aImg->SetColor($this->iColor);
		$aImg->Line($x,$yTop,$x,$aYBottom);
	    }
	}

	$aImg->SetColor($this->iColor);
	$aImg->Line($aXLeft,$yTop, $aXRight,$yTop);

	// Stroke vertical column dividers
	$cols=array();
	$this->GetColStart($aImg,$cols);
	$n=count($cols);
	for( $i=1; $i < $n; ++$i ) {
	    $this->vgrid->Stroke($aImg,$cols[$i],$aYBottom,$cols[$i],
				    $aImg->height - $aImg->bottom_margin);
	}
    }
}


//===================================================
// CLASS GanttGraph
// Description: Main class to handle gantt graphs
//===================================================
class GanttGraph extends Graph {
    public $scale;		// Public accessible
    public $hgrid=null;
    private $iObj=array();				// Gantt objects
    private $iLabelHMarginFactor=0.2;	// 10% margin on each side of the labels
    private $iLabelVMarginFactor=0.4;	// 40% margin on top and bottom of label
    private $iLayout=GANTT_FROMTOP;	// Could also be GANTT_EVEN
    private $iSimpleFont = FF_FONT1,$iSimpleFontSize=11;
    private $iSimpleStyle=GANTT_RDIAG,$iSimpleColor='yellow',$iSimpleBkgColor='red';
    private $iSimpleProgressBkgColor='gray',$iSimpleProgressColor='darkgreen';
    private $iSimpleProgressStyle=GANTT_SOLID;
//---------------
// CONSTRUCTOR	
    // Create a new gantt graph
    function GanttGraph($aWidth=0,$aHeight=0,$aCachedName="",$aTimeOut=0,$aInline=true) {

	// Backward compatibility
	if( $aWidth == -1 ) $aWidth=0;
	if( $aHeight == -1 ) $aHeight=0;

	if( $aWidth<  0 || $aHeight < 0 ) {
	    JpgraphError::RaiseL(6002);
//("You can't specify negative sizes for Gantt graph dimensions. Use 0 to indicate that you want the library to automatically determine a dimension.");
	}
	Graph::Graph($aWidth,$aHeight,$aCachedName,$aTimeOut,$aInline);		
	$this->scale = new GanttScale($this->img);

	// Default margins
	$this->img->SetMargin(15,17,25,15);

	$this->hgrid = new HorizontalGridLine();
		
	$this->scale->ShowHeaders(GANTT_HWEEK|GANTT_HDAY);
	$this->SetBox();
    }
	
//---------------
// PUBLIC METHODS

    // 

    function SetSimpleFont($aFont,$aSize) {
	$this->iSimpleFont = $aFont;
	$this->iSimpleFontSize = $aSize;
    }

    function SetSimpleStyle($aBand,$aColor,$aBkgColor) {
	$this->iSimpleStyle = $aBand;
	$this->iSimpleColor = $aColor;
	$this->iSimpleBkgColor = $aBkgColor;
    }

    // A utility function to help create basic Gantt charts
    function CreateSimple($data,$constrains=array(),$progress=array()) {
	$num = count($data);
	for( $i=0; $i < $num; ++$i) {
	    switch( $data[$i][1] ) {
		case ACTYPE_GROUP:
		    // Create a slightly smaller height bar since the
		    // "wings" at the end will make it look taller
		    $a = new GanttBar($data[$i][0],$data[$i][2],$data[$i][3],$data[$i][4],'',8);
		    $a->title->SetFont($this->iSimpleFont,FS_BOLD,$this->iSimpleFontSize);		
		    $a->rightMark->Show();
		    $a->rightMark->SetType(MARK_RIGHTTRIANGLE);
		    $a->rightMark->SetWidth(8);
		    $a->rightMark->SetColor('black');
		    $a->rightMark->SetFillColor('black');
	    
		    $a->leftMark->Show();
		    $a->leftMark->SetType(MARK_LEFTTRIANGLE);
		    $a->leftMark->SetWidth(8);
		    $a->leftMark->SetColor('black');
		    $a->leftMark->SetFillColor('black');
	    
		    $a->SetPattern(BAND_SOLID,'black');
		    $csimpos = 6;
		    break;
		
		case ACTYPE_NORMAL:
		    $a = new GanttBar($data[$i][0],$data[$i][2],$data[$i][3],$data[$i][4],'',10);
		    $a->title->SetFont($this->iSimpleFont,FS_NORMAL,$this->iSimpleFontSize);
		    $a->SetPattern($this->iSimpleStyle,$this->iSimpleColor);
		    $a->SetFillColor($this->iSimpleBkgColor);
		    // Check if this activity should have a constrain line
		    $n = count($constrains);
		    for( $j=0; $j < $n; ++$j ) {
			if( empty($constrains[$j]) || (count($constrains[$j]) != 3) ) {
			    JpGraphError::RaiseL(6003,$j);
//("Invalid format for Constrain parameter at index=$j in CreateSimple(). Parameter must start with index 0 and contain arrays of (Row,Constrain-To,Constrain-Type)");	 
			}
			if( $constrains[$j][0]==$data[$i][0] ) {
			    $a->SetConstrain($constrains[$j][1],$constrains[$j][2],'black',ARROW_S2,ARROWT_SOLID);    
			}
		    }

		    // Check if this activity have a progress bar
		    $n = count($progress);
		    for( $j=0; $j < $n; ++$j ) {
			
			if( empty($progress[$j]) || (count($progress[$j]) != 2) ) {
			    JpGraphError::RaiseL(6004,$j);
//("Invalid format for Progress parameter at index=$j in CreateSimple(). Parameter must start with index 0 and contain arrays of (Row,Progress)");	
			}
			if( $progress[$j][0]==$data[$i][0] ) {
			    $a->progress->Set($progress[$j][1]);
			    $a->progress->SetPattern($this->iSimpleProgressStyle,
						     $this->iSimpleProgressColor);
			    $a->progress->SetFillColor($this->iSimpleProgressBkgColor);
			    //$a->progress->SetPattern($progress[$j][2],$progress[$j][3]);
			    break;
			}
		    }
		    $csimpos = 6;
		    break;

		case ACTYPE_MILESTONE:
		    $a = new MileStone($data[$i][0],$data[$i][2],$data[$i][3]);
		    $a->title->SetFont($this->iSimpleFont,FS_NORMAL,$this->iSimpleFontSize);
		    $a->caption->SetFont($this->iSimpleFont,FS_NORMAL,$this->iSimpleFontSize);
		    $csimpos = 5;
		    break;
		default:
		    die('Unknown activity type');
		    break;
	    }

	    // Setup caption
	    $a->caption->Set($data[$i][$csimpos-1]);

	    // Check if this activity should have a CSIM target ?
	    if( !empty($data[$i][$csimpos]) ) {
		$a->SetCSIMTarget($data[$i][$csimpos]);
		$a->SetCSIMAlt($data[$i][$csimpos+1]);
	    }
	    if( !empty($data[$i][$csimpos+2]) ) {
		$a->title->SetCSIMTarget($data[$i][$csimpos+2]);
		$a->title->SetCSIMAlt($data[$i][$csimpos+3]);
	    }

	    $this->Add($a);
	}
    }

	
    // Set what headers should be shown
    function ShowHeaders($aFlg) {
	$this->scale->ShowHeaders($aFlg);
    }
	
    // Specify the fraction of the font height that should be added 
    // as vertical margin
    function SetLabelVMarginFactor($aVal) {
	$this->iLabelVMarginFactor = $aVal;
    }

    // Synonym to the method above
    function SetVMarginFactor($aVal) {
	$this->iLabelVMarginFactor = $aVal;
    }
	
	
    // Add a new Gantt object
    function Add($aObject) {
	if( is_array($aObject) && count($aObject) > 0 ) {
	    $cl = $aObject[0];
	    if( class_exists('IconPlot',false) && ($cl instanceof IconPlot) ) {
		$this->AddIcon($aObject);
	    }
	    else {
		$n = count($aObject);
		for($i=0; $i < $n; ++$i)
		    $this->iObj[] = $aObject[$i];
	    }
	}
	else {
	    if( class_exists('IconPlot',false) && ($aObject instanceof IconPlot) ) {
		$this->AddIcon($aObject);
	    }
	    else {	    
		$this->iObj[] = $aObject;
	    }
	}
    }

    // Override inherit method from Graph and give a warning message
    function SetScale($aAxisType,$aYMin=1,$aYMax=1,$aXMin=1,$aXMax=1) {
	JpGraphError::RaiseL(6005);
//("SetScale() is not meaningfull with Gantt charts.");
    }

    // Specify the date range for Gantt graphs (if this is not set it will be
    // automtically determined from the input data)
    function SetDateRange($aStart,$aEnd) {
	// Adjust the start and end so that the indicate the
	// begining and end of respective start and end days
	if( strpos($aStart,':') === false )
	    $aStart = date('Y-m-d 00:00',strtotime($aStart));
	if( strpos($aEnd,':') === false )
	    $aEnd = date('Y-m-d 23:59',strtotime($aEnd));
	$this->scale->SetRange($aStart,$aEnd);
    }
	
    // Get the maximum width of the activity titles columns for the bars
    // The name is lightly misleading since we from now on can have
    // multiple columns in the label section. When this was first written
    // it only supported a single label, hence the name.
    function GetMaxLabelWidth() {
	$m=10;
	if( $this->iObj != null ) {
	    $marg = $this->scale->actinfo->iLeftColMargin+$this->scale->actinfo->iRightColMargin;
 	    $n = count($this->iObj);
 	    for($i=0; $i < $n; ++$i) {
		if( !empty($this->iObj[$i]->title) ) {
		    if( $this->iObj[$i]->title->HasTabs() ) {
			list($tot,$w) = $this->iObj[$i]->title->GetWidth($this->img,true);
			$m=max($m,$tot);
		    }
		    else 
			$m=max($m,$this->iObj[$i]->title->GetWidth($this->img));
		}
	    }
	}
	return $m;
    }
	
    // Get the maximum height of the titles for the bars
    function GetMaxLabelHeight() {
	$m=10;
	if( $this->iObj != null ) {
	    $n = count($this->iObj);
	    for($i=0; $i < $n; ++$i) {
		if( !empty($this->iObj[$i]->title) ) {
		    $m=max($m,$this->iObj[$i]->title->GetHeight($this->img));
		}
	    }
	}
	return $m;
    }

    function GetMaxBarAbsHeight() {
	$m=0;
	if( $this->iObj != null ) {
	    $m = $this->iObj[0]->GetAbsHeight($this->img);
	    $n = count($this->iObj);
	    for($i=1; $i < $n; ++$i) {
		$m=max($m,$this->iObj[$i]->GetAbsHeight($this->img));
	    }
	}
	return $m;		
    }
	
    // Get the maximum used line number (vertical position) for bars
    function GetBarMaxLineNumber() {
	$m=1;
	if( $this->iObj != null ) {
	    $m = $this->iObj[0]->GetLineNbr();
	    $n = count($this->iObj);
	    for($i=1; $i < $n; ++$i) {
		$m=max($m,$this->iObj[$i]->GetLineNbr());
	    }
	}
	return $m;
    }
	
    // Get the minumum and maximum used dates for all bars
    function GetBarMinMax() {
	$start = 0 ;
	$n = count($this->iObj);
	while( $start < $n && $this->iObj[$start]->GetMaxDate() === false )
	    ++$start;
	if( $start >= $n ) {
	    JpgraphError::RaiseL(6006);
//('Cannot autoscale Gantt chart. No dated activities exist. [GetBarMinMax() start >= n]');
	}

	$max=$this->scale->NormalizeDate($this->iObj[$start]->GetMaxDate());
	$min=$this->scale->NormalizeDate($this->iObj[$start]->GetMinDate());

	for($i=$start+1; $i < $n; ++$i) {
	    $rmax = $this->scale->NormalizeDate($this->iObj[$i]->GetMaxDate());
	    if( $rmax != false ) 
		$max=Max($max,$rmax);
	    $rmin = $this->scale->NormalizeDate($this->iObj[$i]->GetMinDate());
	    if( $rmin != false ) 
		$min=Min($min,$rmin);
	}
	$minDate = date("Y-m-d",$min);
	$min = strtotime($minDate);
	$maxDate = date("Y-m-d 23:59",$max);
	$max = strtotime($maxDate);	
	return array($min,$max);
    }

    // Create a new auto sized canvas if the user hasn't specified a size
    // The size is determined by what scale the user has choosen and hence
    // the minimum width needed to display the headers. Some margins are
    // also added to make it better looking.
    function AutoSize() {

	if( $this->img->img == null ) {
	    // The predefined left, right, top, bottom margins.
	    // Note that the top margin might incease depending on
	    // the title.
	    $lm = $this->img->left_margin; 
	    $rm = $this->img->right_margin; 
	    $rm += 2 ;
	    $tm = $this->img->top_margin; 
	    $bm = $this->img->bottom_margin; 
	    $bm += 1; 
	    if( BRAND_TIMING ) $bm += 10;
			
	    // First find out the height			
	    $n=$this->GetBarMaxLineNumber()+1;
	    $m=max($this->GetMaxLabelHeight(),$this->GetMaxBarAbsHeight());
	    $height=$n*((1+$this->iLabelVMarginFactor)*$m);			
			
	    // Add the height of the scale titles			
	    $h=$this->scale->GetHeaderHeight();
	    $height += $h;

	    // Calculate the top margin needed for title and subtitle
	    if( $this->title->t != "" ) {
		$tm += $this->title->GetFontHeight($this->img);
	    }
	    if( $this->subtitle->t != "" ) {
		$tm += $this->subtitle->GetFontHeight($this->img);
	    }

	    // ...and then take the bottom and top plot margins into account
	    $height += $tm + $bm + $this->scale->iTopPlotMargin + $this->scale->iBottomPlotMargin;
	    // Now find the minimum width for the chart required

	    // If day scale or smaller is shown then we use the day font width
	    // as the base size unit.
	    // If only weeks or above is displayed we use a modified unit to
	    // get a smaller image.
	    if( $this->scale->IsDisplayHour() || $this->scale->IsDisplayMinute() ) {
		// Add 2 pixel margin on each side
		$fw=$this->scale->day->GetFontWidth($this->img)+4; 
	    }
	    elseif( $this->scale->IsDisplayWeek() ) {
		$fw = 8;
	    }
	    elseif( $this->scale->IsDisplayMonth() ) {
		$fw = 4;
	    }
	    else {
		$fw = 2;
	    }

	    $nd=$this->scale->GetNumberOfDays();

	    if( $this->scale->IsDisplayDay() ) {
		// If the days are displayed we also need to figure out
		// how much space each day's title will require.
		switch( $this->scale->day->iStyle ) {
		    case DAYSTYLE_LONG :
			$txt = "Monday";
			break;
		    case DAYSTYLE_LONGDAYDATE1 :
			$txt =  "Monday 23 Jun";
			break;
		    case DAYSTYLE_LONGDAYDATE2 :
			$txt =  "Monday 23 Jun 2003";
			break;
		    case DAYSTYLE_SHORT : 
			$txt =  "Mon";
			break;
		    case DAYSTYLE_SHORTDAYDATE1 : 
                        $txt =  "Mon 23/6";
			break;
		    case DAYSTYLE_SHORTDAYDATE2 :
			$txt =  "Mon 23 Jun";
			break;
		    case DAYSTYLE_SHORTDAYDATE3 :
			$txt =  "Mon 23";
			break;
		    case DAYSTYLE_SHORTDATE1 :
                        $txt =  "23/6";
			break;
		    case DAYSTYLE_SHORTDATE2 :
			$txt =  "23 Jun";
			break;
		    case DAYSTYLE_SHORTDATE3 :
			$txt =  "Mon 23";
			break;
		    case DAYSTYLE_SHORTDATE4 :
			$txt =  "88";
			break;
		    case DAYSTYLE_CUSTOM :
			$txt = date($this->scale->day->iLabelFormStr,
				    strtotime('2003-12-20 18:00'));
			break;
		    case DAYSTYLE_ONELETTER :
		    default:
			$txt = "M";
			break;
		}
		$fw = $this->scale->day->GetStrWidth($this->img,$txt)+6;
	    }

	    // If we have hours enabled we must make sure that each day has enough
	    // space to fit the number of hours to be displayed.
	    if( $this->scale->IsDisplayHour() ) {
		// Depending on what format the user has choose we need different amount
		// of space. We therefore create a typical string for the choosen format
		// and determine the length of that string.
		switch( $this->scale->hour->iStyle ) {
		    case HOURSTYLE_HMAMPM:
			$txt = '12:00pm';
			break;
		    case HOURSTYLE_H24:
			// 13
			$txt = '24';
			break;
		    case HOURSTYLE_HAMPM:
			$txt = '12pm';
			break;
		    case HOURSTYLE_CUSTOM:
			$txt = date($this->scale->hour->iLabelFormStr,strtotime('2003-12-20 18:00'));
			break;
		    case HOURSTYLE_HM24:
		    default:
			$txt = '24:00';
			break;
		}

		$hfw = $this->scale->hour->GetStrWidth($this->img,$txt)+6;
		$mw = $hfw;
		if( $this->scale->IsDisplayMinute() ) {
		    // Depending on what format the user has choose we need different amount
		    // of space. We therefore create a typical string for the choosen format
		    // and determine the length of that string.
		    switch( $this->scale->minute->iStyle ) {
			case HOURSTYLE_CUSTOM:
			    $txt2 = date($this->scale->minute->iLabelFormStr,strtotime('2005-05-15 18:55'));
			    break;
			case MINUTESTYLE_MM:
			default:
			    $txt2 = '15';
			    break;
		    }
		    
		    $mfw = $this->scale->minute->GetStrWidth($this->img,$txt2)+6;
		    $n2 = ceil(60 / $this->scale->minute->GetIntervall() );
		    $mw = $n2 * $mfw;
		}
		$hfw = $hfw < $mw ? $mw : $hfw ;   
		$n = ceil(24*60 / $this->scale->TimeToMinutes($this->scale->hour->GetIntervall()) );
		$hw = $n * $hfw;
		$fw = $fw < $hw ? $hw : $fw ;
	    }

	    // We need to repeat this code block here as well. 
	    // THIS iS NOT A MISTAKE !
	    // We really need it since we need to adjust for minutes both in the case
	    // where hour scale is shown and when it is not shown.

	    if( $this->scale->IsDisplayMinute() ) {
		// Depending on what format the user has choose we need different amount
		// of space. We therefore create a typical string for the choosen format
		// and determine the length of that string.
		switch( $this->scale->minute->iStyle ) {
		    case HOURSTYLE_CUSTOM:
			$txt = date($this->scale->minute->iLabelFormStr,strtotime('2005-05-15 18:55'));
			break;
		    case MINUTESTYLE_MM:
		    default:
			$txt = '15';
			break;
		}
		
		$mfw = $this->scale->minute->GetStrWidth($this->img,$txt)+6;
		$n = ceil(60 / $this->scale->TimeToMinutes($this->scale->minute->GetIntervall()) );
		$mw = $n * $mfw;
		$fw = $fw < $mw ? $mw : $fw ;
	    }

	    // If we display week we must make sure that 7*$fw is enough
	    // to fit up to 10 characters of the week font (if the week is enabled)
	    if( $this->scale->IsDisplayWeek() ) {
		// Depending on what format the user has choose we need different amount
		// of space
		$fsw = strlen($this->scale->week->iLabelFormStr);
		if( $this->scale->week->iStyle==WEEKSTYLE_FIRSTDAY2WNBR ) {
		    $fsw += 8;
		}
		elseif( $this->scale->week->iStyle==WEEKSTYLE_FIRSTDAYWNBR ) {
		    $fsw += 7;
		}
		else {
		    $fsw += 4;
		}
		    
		$ww = $fsw*$this->scale->week->GetFontWidth($this->img);
		if( 7*$fw < $ww ) {
		    $fw = ceil($ww/7);
		}
	    }

	    if( !$this->scale->IsDisplayDay() && !$this->scale->IsDisplayHour() &&
		!( ($this->scale->week->iStyle==WEEKSTYLE_FIRSTDAYWNBR || 
		    $this->scale->week->iStyle==WEEKSTYLE_FIRSTDAY2WNBR) && $this->scale->IsDisplayWeek() ) ) {
		// If we don't display the individual days we can shrink the
		// scale a little bit. This is a little bit pragmatic at the 
		// moment and should be re-written to take into account
		// a) What scales exactly are shown and 
		// b) what format do they use so we know how wide we need to
		// make each scale text space at minimum.
		$fw /= 2;
		if( !$this->scale->IsDisplayWeek() ) {
		    $fw /= 1.8;
		}
	    }

	    $cw = $this->GetMaxActInfoColWidth() ;
	    $this->scale->actinfo->SetMinColWidth($cw); 
	    if( $this->img->width <= 0 ) {
		// Now determine the width for the activity titles column

		// Firdst find out the maximum width of each object column
		$titlewidth = max(max($this->GetMaxLabelWidth(),
				      $this->scale->tableTitle->GetWidth($this->img)), 
				  $this->scale->actinfo->GetWidth($this->img));

		// Add the width of the vertivcal divider line
		$titlewidth += $this->scale->divider->iWeight*2;


		// Now get the total width taking 
		// titlewidth, left and rigt margin, dayfont size 
		// into account
		$width = $titlewidth + $nd*$fw + $lm+$rm;
	    }
	    else {
		$width = $this->img->width;
	    }

	    $width = round($width);
	    $height = round($height);
	    // Make a sanity check on image size
	    if( $width > MAX_GANTTIMG_SIZE_W || $height > MAX_GANTTIMG_SIZE_H ) {
		JpgraphError::RaiseL(6007,$width,$height);
//("Sanity check for automatic Gantt chart size failed. Either the width (=$width) or height (=$height) is larger than MAX_GANTTIMG_SIZE. This could potentially be caused by a wrong date in one of the activities.");
	    }
	    $this->img->CreateImgCanvas($width,$height);			
	    $this->img->SetMargin($lm,$rm,$tm,$bm);
	}
    }

    // Return an array width the maximum width for each activity
    // column. This is used when we autosize the columns where we need
    // to find out the maximum width of each column. In order to do that we
    // must walk through all the objects, sigh...
    function GetMaxActInfoColWidth() {
	$n = count($this->iObj);
	if( $n == 0 ) return;
	$w = array();
	$m = $this->scale->actinfo->iLeftColMargin + $this->scale->actinfo->iRightColMargin;
	
	for( $i=0; $i < $n; ++$i ) {
	    $tmp = $this->iObj[$i]->title->GetColWidth($this->img,$m);
	    $nn = count($tmp);
	    for( $j=0; $j < $nn; ++$j ) {
		if( empty($w[$j]) ) 
		    $w[$j] = $tmp[$j];
		else 
		    $w[$j] = max($w[$j],$tmp[$j]);
	    }
	}
	return $w;
    }

    // Stroke the gantt chart
    function Stroke($aStrokeFileName="") {	

	// If the filename is the predefined value = '_csim_special_'
	// we assume that the call to stroke only needs to do enough
	// to correctly generate the CSIM maps.
	// We use this variable to skip things we don't strictly need
	// to do to generate the image map to improve performance
	// a best we can. Therefor you will see a lot of tests !$_csim in the
	// code below.
	$_csim = ($aStrokeFileName===_CSIM_SPECIALFILE);

	// Should we autoscale dates?

	if( !$this->scale->IsRangeSet() ) {
	    list($min,$max) = $this->GetBarMinMax();
	    $this->scale->SetRange($min,$max);
	}

	$this->scale->AdjustStartEndDay();

	// Check if we should autoscale the image
	$this->AutoSize();

	// Should we start from the top or just spread the bars out even over the
	// available height
	$this->scale->SetVertLayout($this->iLayout);			
	if( $this->iLayout == GANTT_FROMTOP ) {
	    $maxheight=max($this->GetMaxLabelHeight(),$this->GetMaxBarAbsHeight());
	    $this->scale->SetVertSpacing($maxheight*(1+$this->iLabelVMarginFactor));
	}
	// If it hasn't been set find out the maximum line number
	if( $this->scale->iVertLines == -1 ) 
	    $this->scale->iVertLines = $this->GetBarMaxLineNumber()+1; 	
		
	$maxwidth=max($this->scale->actinfo->GetWidth($this->img),
		      max($this->GetMaxLabelWidth(),
		      $this->scale->tableTitle->GetWidth($this->img)));

	$this->scale->SetLabelWidth($maxwidth+$this->scale->divider->iWeight);//*(1+$this->iLabelHMarginFactor));

	if( !$_csim ) {
	    $this->StrokePlotArea();
	    if( $this->iIconDepth == DEPTH_BACK ) {
		$this->StrokeIcons();
	    }
	}

	$this->scale->Stroke();

	if( !$_csim ) {
	    // Due to a minor off by 1 bug we need to temporarily adjust the margin
	    $this->img->right_margin--;
	    $this->StrokePlotBox();
	    $this->img->right_margin++;
	}

	// Stroke Grid line
	$this->hgrid->Stroke($this->img,$this->scale);

	$n = count($this->iObj);
	for($i=0; $i < $n; ++$i) {
	    //$this->iObj[$i]->SetLabelLeftMargin(round($maxwidth*$this->iLabelHMarginFactor/2));
	    $this->iObj[$i]->Stroke($this->img,$this->scale);
	}

	$this->StrokeTitles();

	if( !$_csim ) {
	    $this->StrokeConstrains();
	    $this->footer->Stroke($this->img);


	    if( $this->iIconDepth == DEPTH_FRONT) {
		$this->StrokeIcons();
	    }

	    // Should we do any final image transformation
	    if( $this->iImgTrans ) {
		if( !class_exists('ImgTrans',false) ) {
		    require_once('jpgraph_imgtrans.php');
		}
	    
		$tform = new ImgTrans($this->img->img);
		$this->img->img = $tform->Skew3D($this->iImgTransHorizon,$this->iImgTransSkewDist,
						 $this->iImgTransDirection,$this->iImgTransHighQ,
						 $this->iImgTransMinSize,$this->iImgTransFillColor,
						 $this->iImgTransBorder);
	    }
	    
	    
	    // If the filename is given as the special "__handle"
	    // then the image handler is returned and the image is NOT
	    // streamed back
	    if( $aStrokeFileName == _IMG_HANDLER ) {
		return $this->img->img;
	    }
	    else {
		// Finally stream the generated picture					
		$this->cache->PutAndStream($this->img,$this->cache_name,$this->inline,
					   $aStrokeFileName);		
	    }
	}
    }

    function StrokeConstrains() {
	$n = count($this->iObj);

	// Stroke all constrains
	for($i=0; $i < $n; ++$i) {

	    // Some gantt objects may not have constraints associated with them
	    // for example we can add IconPlots which doesn't have this property.
	    if( empty($this->iObj[$i]->constraints) ) continue;

	    $numConstrains = count($this->iObj[$i]->constraints);

	    for( $k = 0; $k < $numConstrains; $k++ ) {
		$vpos = $this->iObj[$i]->constraints[$k]->iConstrainRow;
		if( $vpos >= 0 ) {
		    $c1 = $this->iObj[$i]->iConstrainPos;

		    // Find out which object is on the target row
		    $targetobj = -1;
		    for( $j=0; $j < $n && $targetobj == -1; ++$j ) {
			if( $this->iObj[$j]->iVPos == $vpos ) {
			    $targetobj = $j;
			}
		    }
		    if( $targetobj == -1 ) {
			JpGraphError::RaiseL(6008,$this->iObj[$i]->iVPos,$vpos);
//('You have specifed a constrain from row='.$this->iObj[$i]->iVPos.' to row='.$vpos.' which does not have any activity.');
		    }
		    $c2 = $this->iObj[$targetobj]->iConstrainPos;
		    if( count($c1) == 4 && count($c2 ) == 4) {
			switch( $this->iObj[$i]->constraints[$k]->iConstrainType ) {
			    case CONSTRAIN_ENDSTART:
				if( $c1[1] < $c2[1] ) {
				    $link = new GanttLink($c1[2],$c1[3],$c2[0],$c2[1]);
				}
				else {
				    $link = new GanttLink($c1[2],$c1[1],$c2[0],$c2[3]);
				}
				$link->SetPath(3);
				break;
			    case CONSTRAIN_STARTEND:
				if( $c1[1] < $c2[1] ) {
				    $link = new GanttLink($c1[0],$c1[3],$c2[2],$c2[1]);
				}
				else {
				    $link = new GanttLink($c1[0],$c1[1],$c2[2],$c2[3]);
				}
				$link->SetPath(0);
				break;
			    case CONSTRAIN_ENDEND:
				if( $c1[1] < $c2[1] ) {
				    $link = new GanttLink($c1[2],$c1[3],$c2[2],$c2[1]);
				}
				else {
				    $link = new GanttLink($c1[2],$c1[1],$c2[2],$c2[3]);
				}
				$link->SetPath(1);
				break;
			    case CONSTRAIN_STARTSTART:
				if( $c1[1] < $c2[1] ) {
				    $link = new GanttLink($c1[0],$c1[3],$c2[0],$c2[1]);
				}
				else {
				    $link = new GanttLink($c1[0],$c1[1],$c2[0],$c2[3]);
				}
				$link->SetPath(3);
				break;
			    default:
				JpGraphError::RaiseL(6009,$this->iObj[$i]->iVPos,$vpos);
//('Unknown constrain type specified from row='.$this->iObj[$i]->iVPos.' to row='.$vpos);
				break;
			}

			$link->SetColor($this->iObj[$i]->constraints[$k]->iConstrainColor);
			$link->SetArrow($this->iObj[$i]->constraints[$k]->iConstrainArrowSize,
					$this->iObj[$i]->constraints[$k]->iConstrainArrowType);
 
			$link->Stroke($this->img);
		    }
		}
	    }
	}
    }

    function GetCSIMAreas() {
	if( !$this->iHasStroked )
	    $this->Stroke(_CSIM_SPECIALFILE);
 
	$csim = $this->title->GetCSIMAreas();
	$csim .= $this->subtitle->GetCSIMAreas();
	$csim .= $this->subsubtitle->GetCSIMAreas();

	$n = count($this->iObj);
	for( $i=$n-1; $i >= 0; --$i ) 
	    $csim .= $this->iObj[$i]->GetCSIMArea();
	return $csim;
    }
}

//===================================================
// CLASS PredefIcons
// Description: Predefined icons for use with Gantt charts
//===================================================
define('GICON_WARNINGRED',0);
define('GICON_TEXT',1);
define('GICON_ENDCONS',2);
define('GICON_MAIL',3);
define('GICON_STARTCONS',4);
define('GICON_CALC',5);
define('GICON_MAGNIFIER',6);
define('GICON_LOCK',7);
define('GICON_STOP',8);
define('GICON_WARNINGYELLOW',9);
define('GICON_FOLDEROPEN',10);
define('GICON_FOLDER',11);
define('GICON_TEXTIMPORTANT',12);

class PredefIcons {
    private $iBuiltinIcon = null, $iLen = -1 ;

    function GetLen() {
	return $this->iLen ; 
    }

    function GetImg($aIdx) {
	if( $aIdx < 0 || $aIdx >= $this->iLen ) {
	    JpGraphError::RaiseL(6010,$aIdx);
//('Illegal icon index for Gantt builtin icon ['.$aIdx.']');
	}
	return Image::CreateFromString(base64_decode($this->iBuiltinIcon[$aIdx][1]));   
    }

    function PredefIcons() {
	//==========================================================
	// warning.png
	//==========================================================
	$this->iBuiltinIcon[0][0]= 1043 ;
	$this->iBuiltinIcon[0][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsSAAALEgHS3X78AAAA'.
	    'B3RJTUUH0wgKFSgilWPhUQAAA6BJREFUeNrtl91rHFUYh5/3zMx+Z5JNUoOamCZNaqTZ6IWIkqRiQWmi1IDetHfeiCiltgXBP8AL'.
	    '0SIUxf/AvfRSBS9EKILFFqyIH9CEmFZtPqrBJLs7c+b1YneT3WTTbNsUFPLCcAbmzPt73o9zzgzs2Z793231UOdv3w9k9Z2uzOdA'.
	    '5+2+79yNeL7Hl7hw7oeixRMZ6PJM26W18DNAm/Vh7lR8fqh97NmMF11es1iFpMATqdirwMNA/J4DpIzkr5YsAF1PO6gIMYHRdPwl'.
	    'oO2elmB+qH3sm7XozbkgYvy8SzYnZPtcblyM6I+5z3jQ+0vJfgpEu56BfI9vUkbyi2HZd1QJoeWRiAjBd4SDCW8SSAOy6wBHMzF7'.
	    'YdV2A+ROuvRPLfHoiSU0EMY/cDAIhxJeGngKaN1VgHyPL7NBxI1K9P4QxBzw3K1zJ/zkG8B9uwaQ7/HNsRZv9kohBGD0o7JqMYS/'.
	    '/ynPidQw/LrBiPBcS/yFCT95DvB2BWAy4575PaQbQKW+tPd3GCItu2odKI++YxiKu0d26oWmAD7paZU/rLz37VqIijD2YbnzNBBE'.
	    'IBHf8K8qjL7vYhCGErEU8CTg3xXAeMp96GrJEqkyXkm9Bhui1xfsunjdGhcYLq+IzjsGmBt5YH/cmJkFq6gIqlon3u4LxdKGuCIo'.
	    'Qu41g0E41po+2R33Xt5uz9kRIB2UTle7PnfKrROP1HD4sRjZlq0lzhwoZ6rDNeTi3nEg1si/7FT7kYQbXS6E5E65tA5uRF9tutq0'.
	    'K/VwAF+/FbIYWt6+tjQM/AqUms7A4Wy6d7YSfSNxgMmzi0ycWWworio4QJvj4LpuL5BqugTnXzzqJsJwurrlNhJXFaavW67NRw3F'.
	    'q+aJcCQVe9fzvJGmAY7/dPH0gi0f64OveGxa+usCuQMeZ0+kt8BVrX+qPO9Bzx0MgqBvs+a2PfDdYIf+WAjXU1ub4tqNaPPzRs8A'.
	    'blrli+WVn79cXn0cWKl+tGx7HLc7pu3CSmnfitL+l1UihAhwjFkPQev4K/fSABjBM8JCaFuurJU+rgW41SroA8aNMVNAFtgHJCsn'.
	    'XGy/58QVxAC9MccJtZ5kIzNlW440WrJ2ea4YPA9cAooA7i0A/gS+iqLoOpB1HOegqrYB3UBmJrAtQAJwpwPr1Ry92wVlgZsiYlW1'.
	    'uX1gU36dymgqYxJIJJNJT1W9QqHgNwFQBGYqo94OwHZQUuPD7ACglSvc+5n5T9m/wfJJX4U9qzEAAAAASUVORK5CYII=' ; 

	//==========================================================
	// edit.png
	//==========================================================
	$this->iBuiltinIcon[1][0]= 959 ;
	$this->iBuiltinIcon[1][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAFgAWABY9j+ZuwAAAAlwSFlz'.
	    'AAALEAAACxABrSO9dQAAAAd0SU1FB9AKDAwbIEXOA6AAAAM8SURBVHicpdRPaBxlHMbx76ZvsmOTmm1dsEqQSIIsEmGVBAQjivEQ'.
	    'PAUJngpWsAWlBw8egpQepKwplN4ULEG9CjkEyUFKlSJrWTG0IU51pCsdYW2ncUPjdtp9Z+f3vuNhu8nKbmhaf5cZeGc+PO8zf1Lc'.
	    'm0KhkACICCKCMeaBjiLC0tLSnjNvPmuOHRpH0TZTU1M8zBi9wakzn7OFTs5sw8YYACYmJrre7HkeuVyu69qPF77hlT1XmZ0eQ03O'.
	    'wOLJTvhBx1rLz18VmJ0eY+jVd2FxDkKXnvYLHgb97OgLzE4ON9Hzc1B1QaQzsed5O0Lta3Ec89OnR5h5McfQ+Mw2qgQUnfBOPbZ3'.
	    'bK3l+xOvMT0+3ERLp5FNF6UEjcL32+DdVmGt5WLhDYYPZrbRqreFumXwql0S3w9tnDvLWD5PZigPpdOwuYpSCo3C8wU3UHxQdHbf'.
	    'cZIkNM6dxcnlUM4k1eUFMlUPpUADbpkttFarHe6oYqeOr6yt4RzMQHYUcUsQVtGicHDwKprViuLDkkOtVnsHCHZVRVy/zcj1i5Af'.
	    'h8AjdIts+hUcGcYPK3iBtKM3gD/uAzf/AdY2mmmVgy6X8YNNKmGIvyloPcB8SUin07RQ4EZHFdsdG0wkJEnEaHAJxvKEpSLeaokV'.
	    'r4zWmhUZYLlY4b1D03y5eIEWCtS7vsciAgiIxkQRabWOrlQor66y4pUphoJb1jiO4uO5o0S3q6RSqVbiOmC7VCEgAhLSaDQ48dH7'.
	    'vD46REY0iysegSjKQciRt99ib7qXwX0O+pG4teM6YKHLB9JMq4mTmF9/+AKA4wvLZByH7OgYL7+UY2qvw/7Bfg5kHiXjJFyv3CGO'.
	    'Y1rof+BW4t/XLiPG0DCGr79d4XzRxRnIMn98huXSTYyJ6et1UNYQhRvcinpJq86H3wGPPPM0iBDd+QffD1g4eZjLvuG7S1Wef26E'.
	    'J7L7eSx7gAHVg7V3MSbi6m/r93baBd6qQjerAJg/9Ql/XrvG0ON1+vv7GH3qSfY5fahUnSTpwZgIEQesaVXRPbHRG/xyJSAxMYlp'.
	    'EOm71HUINiY7mGb95l/8jZCyQmJjMDGJjUmsdCROtZ0n/P/Z8v4Fs2MTUUf7vYoAAAAASUVORK5CYII=' ; 

	//==========================================================
	// endconstrain.png
	//==========================================================
	$this->iBuiltinIcon[2][0]= 666 ;
	$this->iBuiltinIcon[2][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz'.
	    'AAALDwAACw8BkvkDpQAAAAd0SU1FB9ALEREILkh0+eQAAAIXSURBVHictZU9aFNRFMd/N81HX77aptJUWmp1LHRpIcWhg5sIDlUQ'.
	    'LAXB4t7RRUpwEhy7iQ46CCIoSHcl0CFaoVARU2MFMYktadLXJNok7x2HtCExvuYFmnO4w/3gx+Gc/z1HKRTdMEdXqHbB/sgc/sic'.
	    'nDoYAI8XwDa8o1RMLT+2hAsigtTvbIGVqhX46szUifBGswUeCPgAGB7QeLk0X4Ork+HOxo1VgSqGASjMqkn8W4r4vVtEgI/RRQEL'.
	    'vaoGD85cl5V3nySR/S1mxWxab7f35PnntNyMJeRr9kCMqiHTy09EoeToLwggx6ymiMOD/VwcD7Oa/MHkcIiQx026WGYto5P/U+ZZ'.
	    '7gD0QwDuT5z9N3LrVPi0Xs543eQPKkRzaS54eviJIp4tMFQFMllAWN2qcRZHBnixNM8NYD162xq8u7ePSQ+GX2Pjwxc2dB2cLtB8'.
	    '7GgamCb0anBYBeChMtl8855CarclxU1gvViiUK4w2OMkNDnGeJ8bt9fH90yOnOkCwLFTwhzykhvtYzOWoBBbY//R3dbaNTYhf2RO'.
	    'QpeuUMzv188MlwuHy0H13HnE48UzMcL0WAtUHX8OxZHoG1URiFw7rnLLCswuSPD1ulze/iWjT2PSf+dBXRFtVVGIvzqph0pQL7VE'.
	    'avXYaXXxPwsnt0imdttCocMmZBdK7YU9D8wuNOW0nXc6QWzPsSa5naZ1beb9BbGB6dxGtMnXAAAAAElFTkSuQmCC' ; 

	//==========================================================
	// mail.png
	//==========================================================
	$this->iBuiltinIcon[3][0]= 1122 ;
	$this->iBuiltinIcon[3][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz'.
	    'AAALEAAACxABrSO9dQAAAAd0SU1FB9AJHAMfFvL9OU8AAAPfSURBVHictZRdaBRXFMd/987H7tbNx8aYtGCrEexDsOBDaKHFxirb'.
	    'h0qhsiY0ykppKq1osI99C4H2WSiFFMHWUhXBrjRi0uCmtSEUGgP1QWqhWjGkoW7M1kTX3WRn5p4+TJJNGolQ6IXDnDtz+N0z/3PP'.
	    'UWBIpdpYa23b9g09PZ2kUrOrvmUyGVKp1Ao/mUyi56YnVgWfO/P1CihAd/dJMpmaNROIRq8BkM1m0bH6TasC3j6QXgFdXI+DR6PR'.
	    'JX/Pno8B+KLnMKqlpUU8z8MYs2RBEDzWf9J+0RcRbMdxGBsbw/fmCXwPMUEYID4iAVp8wIRmDIHMo4yHSIBSASKC+CWE0C/PF9jU'.
	    '3B6Cp+4M07C5FUtKGNvGwQJctPgIsgD2wRhEIqAMGB+UQYkHJgYYZD7P1HwVlmWhHcfhyk83KeRGUW4t6CgoG5SNUS4KBWgQDUov'.
	    '7AGlwYASBVqH0Bk49dXpCviVV3dw/tI1Bvr7kMIIlh0NYUpjlF0BAYvcxSXmEVLKceHSCJm+PnbueBHbtkNwTXUNBzo6aGpq4sSZ'.
	    'GwT5H7BsF6Wdf1GWHQAoM0upeI9PT1yioS7B7tdaSdSuw7KsUGMAy7HYsmUztTW1nMwM0txssX1rlHjjS5jy/Uq2YkK/eJuLl6/z'.
	    'x+1xkslW6mrixGIODx8EFSlEBC0+tmXT0NhA2763iEUjnLv4C8XpUbSbAB1mKkGJ3J83Od77HW5EszvZSqK2iljMIeJaRGNuJePF'.
	    '6mspY7BJ1DXwQnCd2fxGRq5OUCz8xt72dyhMZcn++Cu3xu9SKhdp2b4ZHWnAtTSxmIWlhcIjlksR3lNBYzlxZsb7+f7ne+xtSzOd'.
	    'u83szH1OnThOPp/n+a0beeP1l4mvq+PU2Qyd+5PY1RuwlAqLYFaBfbTbyPSdfgaH77A//QF4f1O/vpr6RJyq+C5Kc/M8FbFxXItY'.
	    'xOHDrvfo/fxLDnbsJBp5BowBReVWYAzabeTh5ABDw7cWoNNL3YYYNtSv57lnn6Z+Qx01VeuIuBa2DV1HD3H63BAPZu4u1WGpeLHq'.
	    'Rh7+NcjA0O+0p4+CNwXigwnbWlQQdpuEpli+n+PIkcOc//YKuckJJFh2K2anrjFw+QZt6S6kPImIF/b+cqAJD1LihWAxC61twBTo'.
	    'fPcQF/oGsVW5ovHQlavs2/8+uYnRVSOUgHAmmAClBIOBwKC0gPjhIRgEIX2wg7NnwpZW3d3d4vs+vu8TBMGK51rvPM9b8hdteZxd'.
	    'LBbVR8feJDs0Rlv6GFKeXJ21rNRXESxMPR+CBUl0nN7PjtO+dye7Up/8v1I88bf/ixT/AO1/hZsqW+C6AAAAAElFTkSuQmCC' ; 

	//==========================================================
	// startconstrain.png
	//==========================================================
	$this->iBuiltinIcon[4][0]= 725 ;
	$this->iBuiltinIcon[4][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz'.
	    'AAALDgAACw4BQL7hQQAAAAd0SU1FB9ALEREICJp5fBkAAAJSSURBVHic3dS9a1NRGMfx77kxtS+xqS9FG6p1ER3qVJpBQUUc3CRU'.
	    'BwURVLB1EAuKIP0THJQiiNRJBK3iJl18AyeltRZa0bbaJMbUNmlNSm5e7s25j0NqpSSmyag/OMM9POdzDuflwn8djz8gClVRrVEV'.
	    'ur4Bl1FTNSzLrSS6vbml0jUUwSXj8Qfk3PkLtLW2AeBIybmrgz3+gFzpucjlE4f4btuFTuWuCF5XDr3a3UPf6cM8GQvxzbsRAJdh'.
	    'ScfxSywml5j7mVypN0eGEJ0tebIre+zxB6Tv7jPReS2hREpOvpmUXU+H5eC913JnNCSRVE60pUVbWoZjprR39Yq70bdqj4pW7PEH'.
	    '5FpvL9e79jOTTHM7ssDL6CJZ08LbvAGnrpZg2mI2Z/MlZfN8IkxuSwu4V9+WIrj7zFlOHfXzKrLIi2SGh5ECKjnNVNxkQEc55vOw'.
	    'rb6O8JLFdHyJ+ayFElUeHvjwkfteL/V7fKTSkFvIQE4DoLI2Mz/muTkTApcBKIwaN8pwIUrKw+ajWwDknAO0d/r4zFaMuRS63sWm'.
	    'RoOdm+vRIriUYjKexrQV+t1o0YEVwfZSVJmD/dIABJuO0LG3lRFx0GOfiAELE9OgCrfU0XnIp5FwGLEy5WEAOxlR5uN+ARhP7GN3'.
	    '5w7Gv4bQI2+xpt4jjv2nWBmIlcExE2vDAHYioszBZXw6CPE4ADoWVHmd/tuwlZR9eXYyoszBfpiNQqaAOU5+TXRN+DeeenADPT9b'.
	    'EVgKVsutKPl0TGWGhwofoquaoKK4apsq/tH/e/kFwBMXLgAEKK4AAAAASUVORK5CYII=' ; 

	//==========================================================
	// calc.png
	//==========================================================
	$this->iBuiltinIcon[5][0]= 589 ;
	$this->iBuiltinIcon[5][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAA4AIwBbgMF12wAAAAlwSFlz'.
	    'AAALEQAACxEBf2RfkQAAAAd0SU1FB9AHBxQeFsqn0wQAAAHKSURBVHicnZWff+RAGIef3U/gcOEgUAgUCgcLhYXCwsHBQeGgUDgs'.
	    'FgMHB4VA/4Bg4XChWFgIFIqBwkJhsRAYeOGF+TQHmWSTTbKd9pU37/x45jvfTDITXEynAbdWKVQB0NazcVm0alcL4rJaRVzm+w/e'.
	    '3iwAkzbYRcnnYgI04GCvsxxSPabYaEdt2Ra6D0atcvvvDmyrMWBX1zPq2ircP/Tk98DiJtjV/fim6ziOCL6dDHZNhxQ3arIMsox4'.
	    'vejleL2Ay9+jaw6A+4OSICG2cacGKhsGxg+CxeqAQS0Y7BYJvowq7iGMOhXHEfzpvpQkA9bLKgOgWKt+4Lo1mM9hs9m17QNsJ70P'.
	    'Fjc/O52joogoX8MZKiBiAFxd9Z1vcj9wfSpUlDRNMcYQxzFpmnJ0FPH8nDe1MQaWSz9woQpWSZKEojDkeaWoKAyr1tlu+s48wfVx'.
	    'u7n5i7jthmGIiEGcT+36PP+gFeJrxWLhb0UA/lb4ggGs1T0rZs0zwM/ZjNfilcIY5tutPxgOW3F6dUX464LrKILLiw+A7WErrl+2'.
	    'rABG1EL/BilZP8DjU2uR4U+2E49P1Z8QJmNXUzl24A9GBT0IruCfi86d9x+D12RGzt+pNAAAAABJRU5ErkJggg==' ; 

	//==========================================================
	// mag.png
	//==========================================================
	$this->iBuiltinIcon[6][0]= 1415 ;
	$this->iBuiltinIcon[6][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz'.
	    'AAALDAAACwwBP0AiyAAAAAd0SU1FB9ALDxEWDY6Ul+UAAAUESURBVHicdZVrbFRFGIafsyyF0nalV1R6WiggaAptlzsr1OgEogmC'.
	    '0IgoBAsBgkIrBAPEhBj/AP6xRTCUFEwRI4jcgsitXMrFCJptJWvBNpXYbbXtbtttt6e7e86ec/yxadlCfZPJZDIz73zzzjfvR2VL'.
	    'F7U+hf0HD2JduIzTFy6SlJRkPtkcDgdCCE65OxFC8NPV6wghyM7OptankJ2dzbSC5QghEEIgCSHog9PpNAF27dlN6miZuPgElB4/'.
	    'nmY3O7ZtByA1NVUCkGWZweD1eklJScESTbqxuIjrd+/x6uIl5M19hSy7nfGOeUxf+g7VjU1sKi7C4/GYsiyz7tAJAD4/cRaA1tZW'.
	    'AHIPnECUVGD1+/3U19ebG4uLeHf1akamjsIwoVnVCOvQEdLoVILYYmMo3PIxSBJflpSaDX5FAmju1QAYv/8k/s8+wLVxOU0jR2LZ'.
	    '8sMFAApWrCApbRRDrRZirBYSLBKaoRPQw3SFernf2sav7T0Ubt4KwL4FMwF4Vu8FoHBCKgCzDhwHwLIhZ7y5a89u4m2JhA0wTdDC'.
	    'OrphEjJMNElCHxKDEjaobmvlfo/Krj27CQQCJsCGJW8C0KXqAMxMiosQA8hZWcTFx9OsaniDKh1qmG7VoFsL0x0K06kbeAMhWpRe'.
	    '/KpG+gwHAKUnz7Dz3BUMw6DK18nuw99wt0Nh6VdHI8RJicmETQgFg7SFwjSrGv+oKp6ghldV6dZ0ugJBlF6FmCESQ2w2AIqXLsan'.
	    'BrFYLJTnTCBrdBqveeopWZiPFaBHUegJhegMqGgxEkHDwB/UaQ9rdIV06v0+TD2EEQjQFtAY0dsNgNvt5sialQAIIXh7wQKuVf6J'.
	    'gTsSccPDWlQstClBGjr9eHpVWvUQncEwdYEedF8noQ4vmYmpZMTH0nTvDn25vLbrNmu7bvfnsYEbAMnhcPDgwQPzUo2LJusw/mhp'.
	    'QwlHNO0KBAnoIfxtrcQMT2De1Mm891wyUzNlUlJSpIyMDBobGzlzr5rFM/Koq6vrP8ASGxsLwPmKcvIShjPGZiPOakE3VFB8hHwd'.
	    'vJAxhrk5L7Ly+RQuH/sWgPdXrwFg/6HDFBUsIj09nehfbAWwPWOT9n5RYhqGwarNWxkRM5TRCfF4U1PQsDDJFk9uYhwXvzvKjm3b'.
	    'KSsro3DJInNW5RXp7u2bAKSlpeH1esnPz6eqqgqLpmmcr3Fht9ulfaV7mZk1Bs+lM6T1djM9fhg5egDPpTNMy5TZsW07kydPYdWM'.
	    'aXx96ixOp9O8cfUa80srmDpjOgAulytiQqZpMnvObLbt/JTtHxXj9/tRVdU0DGOAufRpevPDTeac0hJyc3NxOOawfv161lVWS6eX'.
	    'z+9/UOCxu1VWVvaTRGv16NFfjB2bNeAQp9NpTpmSM4DcbrdL0WsGDKLRR+52uwe1yP8jb2lpYfikyY9t80n03UCWZeaXVjw1f+zs'.
	    'Oen+/d+pqanhzp2fKSsrw+l0mi6XiyPl5ZGITdN8fAVJwjRNJEmi1qfw1kw7siyTnJxMe3s71dXV3GpoZO64DG41NPJylvxU5D/e'.
	    'qJKsfWQD9IkaZ2RmUvr9aV4aGYcQgjfO3aWoYBF5eXm4ewIsu/CbdPz1aWb0/p1bNoOrQxlUiuiaFo3c3FyEEOx9+C9CCD6paaTW'.
	    'p/TXyYkTJ0Xe59jf7QOyAKDWp/QXxcFQ61P4pT3ShBBcvnUHIQTjxmX19/8BCeVg+/GPpskAAAAASUVORK5CYII=' ; 

	//==========================================================
	// lock.png
	//==========================================================
	$this->iBuiltinIcon[7][0]= 963 ;
	$this->iBuiltinIcon[7][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz'.
	    'AAALCwAACwsBbQSEtwAAAAd0SU1FB9AKAw0XDmwMOwIAAANASURBVHic7ZXfS1t3GMY/3+PprI7aisvo2YU6h6ATA8JW4rrlsF4U'.
	    'qiAsF9mhl0N2cYTRy9G/wptAYWPD9iJtRy5asDe7cYFmyjaXOLaMImOrmkRrjL9yTmIS3120JybWQgfb3R74wuc8Lzw858vLOUpE'.
	    'OK6pqSm2trbY39+nu7tbPHYch7m5OcLhMIA67kWj0aMQEWk6tm17rNm2LSIie3t7ksvlJJ1OSyqVkls3Z8SyLMnlcqTTaVKpFLdu'.
	    'zmBZVj1HeY2VUti2TSQSQSml2bZdi0QirK2tMT09zerqKtlslqGhISYnJ4nHv2N+foFsNquOe9FotLlxOBwmk8lgWRbhcFgymYxY'.
	    'liUi0mqaJoAuIi2macrdO7fFsizx3to0Te7euV1vrXtXEgqFmJmZYWVlhXK5LB4/U9kwDL784kYV0A3DYHd3m4sXRymXywKoRi8U'.
	    'Ch01DgQCJBIJLMsiEAhIIpHw2uLz+eqtYrEYIqKZpimxWEyCwaCMjY01zYPBIJpXqVQqsby8TLVabWKA/v5+RkZGMAyDrq4ulFKH'.
	    'HsfjcWZnZ+ns7KTRqwcnk0mKxSKFQqGJlVKtruuSTCYB6O3trW9UI/v9/iZPB/j8s2HOnX0FgHfeXpeffnzK+fWf+fijvhLs0PtG'.
	    'D/n1OJ9+MsrlSwb3733DwMCAt1EyPj6uACYmJp56168NU6nUqFSE9nZdPE7+WqC/r4NKTagcCJVqDaUUB5VDAA4Pa9x7sMLlSwan'.
	    'WjRmv13D7/erpaWlo604qOp88OF7LC48rPNosMq5Th+Dgxd4/XyA1rbzADi7j8jnf2P++wdcvSr8MJ/i8eomAKlUqn41OsDAQDeD'.
	    'g++yuPCwzm/2vU8+n2a7sMFfj79mp7BBuVzioFSiXHJx3SKuW2Rzy0Up9dxnQVvODALQerqNRn4ZKe0Mvtc6TpzpmqbxalcY9Ato'.
	    '2v06t515C73YQftZB9GLnDrt4LoujuPgOA4Ui+C6yOpXJwZrJ7r/gv4P/u+D9W7fLxTz+1ScQxrZ3atRLaVxdjbY2d184R6/sLHe'.
	    'opHP7/Do90Ua+WWUyezzZHObP/7cfX54/dowE1d66s8TV3oE+Mfn+L/zb4XmHPjRG9YjAAAAAElFTkSuQmCC' ; 

	//==========================================================
	// stop.png
	//==========================================================
	$this->iBuiltinIcon[8][0]= 889 ;
	$this->iBuiltinIcon[8][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz'.
	    'AAALDwAACw8BkvkDpQAAAAd0SU1FB9AJDwEvNyD6M/0AAAL2SURBVHic1ZTLaxVnGIefb2bO5OScHJN4oWrFNqcUJYoUEgU3/Qf6'.
	    'F7gwCkIrvdBLUtqqiLhSg9bgBduFSHZdiG5ctkJ3xRDbUFwUmghNzBDanPGMkzOX79LFJGPMOSd204U/+Bbzvd/78F4H/ieJdoad'.
	    'pZKxRFszAI/DcP0HazXY22v+HB01kee1PA/v3zfnjx4xgGnHcNZe7OvuNj+cOEF1ZATv5nUA4jhBSgmADCVWo8Ge2Of9wb18P/G7'.
	    'oUXmYi30zqlTVEdGWLh1g2D6MYlKkXGE0Vl8aa2GEB149+4xXSzyoOIw/mimiZV/DPb25pFOj13A9gOMEChhUEqhVYqWKUk9QAUp'.
	    'sT/P4s8PmKlUmNhQaIJbkDVqBbpw6wZ2zUc4Nm+ePku5p4eOrgpueQOFUoVCVxcD4+N07dpF9+5tVJeWGPBjhvr7WF1zC8ASgtcP'.
	    'H8a7eZ1odh4sh50nzwCw9ZNh3M4Stutiu0X2nB/LyjZ6lcIbVTpdQU/jWVPzLADM8+ZGBRdtC7wrF/O7bR99iu26VL86iU4SAH4b'.
	    'Po5d6AQhstMSvGyI4wS5FJBKSRwnzF8byx/u+PjzzMF1mfryQ1K/jnCahqp1xEopjFLoNEFJSRJHzF799gWHqa+/QKcSUXBI609f'.
	    'Al5W4teQSiHDOipNUKnMI13RvnOXAIEKQixvGWya98SC560MFwPiqEG86JM8q79Q06lvhnOndy5/B6GPCUOMUu3BQgg8z0M3GmBZ'.
	    'iGJn3v2VmsqnfzNx7FDueODuj8ROCFpjtG5TCmOYv32bJ09msP0ISydMfnAUgF8/O45RAA6WTPjlvXcB+Gn7FuRf/zAnNX6x3ARe'.
	    'PSdmqL+P/YHkwMGDOGWDZTlQcNBRhPEComgB/YeHfq2InF1kLlXUOkpMbio1bd7aATRD/X0M1lPeSlM2vt2X1XBZjZnpLG2tmZO6'.
	    'LbQVOIcP+HG2UauH3xgwBqOz9Cc3l1tC24Fz+MvUDroeGNb5if9H/1dM/wLPCYMw9fryKgAAAABJRU5ErkJggg==' ; 

	//==========================================================
	// error.png
	//==========================================================
	$this->iBuiltinIcon[9][0]= 541 ;
	$this->iBuiltinIcon[9][1]= 
	    'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAAaVBMVEX//////2Xy8mLl5V/Z2VvMzFi/v1WyslKlpU+ZmUyMjEh/'.
	    'f0VyckJlZT9YWDxMTDjAwMDy8sLl5bnY2K/MzKW/v5yyspKlpYiYmH+MjHY/PzV/f2xycmJlZVlZWU9MTEXY2Ms/PzwyMjLFTjea'.
	    'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTCAkUMSj9wWSOAAABLUlEQVR4'.
	    '2s2U3ZKCMAxGjfzJanFAXFkUle/9H9JUKA1gKTN7Yy6YMjl+kNPK5rlZVSuxf1ZRnlZxFYAm93NnIKvR+MEHUgqBXx93wZGIUrSe'.
	    'h+ctEgbpiMo3iQ4kioHCGxir/ZYUbr7AgPXs9bX0BCYM8vN/cPe8oQYzom3tVsSBMVHEoOJ5dm5F1RsIe9CtqGgRacCAkUvRtevT'.
	    'e2pd6vOWF+gCuc/brcuhyARakBU9FgK5bUBWdHEH8tHpDsZnRTZQGzdLVvQ3CzyYZiTAmSIODEwzFCAdJopuvbpeZDisJ4pKEcjD'.
	    'ijWPJhU1MjCo9dkYfiUVjQNTDKY6CVbR6A0niUSZjRwFanR0l9i/TyvGnFdqwStq5axMfDbyBksld/FUumvxS/Bd9VyJvQDWiiMx'.
	    'iOsCHgAAAABJRU5ErkJggg==' ; 

	//==========================================================
	// openfolder.png
	//==========================================================
	$this->iBuiltinIcon[10][0]= 2040 ;
	$this->iBuiltinIcon[10][1]=
	    'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEANAAtwClFht71AAAAAlwSFlz'.
	    'AAALEAAACxABrSO9dQAAAAd0SU1FB9AKDQ4RIXMeaLcAAAd1SURBVHicxZd7jBXVHcc/58zcvTNzH8vusqw8FsTsKiCUUh5WBZXG'.
	    'GkOptmqwNWsWLKXFGlEpzZI0AWNKSy0WhDS22gJKtWlTsSRqzYIuLGB2WVvDIwQMZQMsy2OFfdzde+/OnHP6x907vJaFpjb9JZM5'.
	    'c85Mfp/f9/s7Jxn4P4e41gtSyp78WGvtfdEAcqDFYUOH9HS0NhGk9tPb/ilSyp789UUB2AMuqhQy3Uzm7HGkE6W3dTNZMRI3EcWO'.
	    'jf9ClLmWBT3dzW8jUsevWHCG3UpWl+IkHSxnbDh/Mcz12NevBcuWXTmf6TjnXvJ88gDmVB3pw3+nt3UzHa1NqMzBS2zqPLGFjtMN'.
	    'ZNr3XdW+qyqwZcFk76HX/tHWfuQvyO4W7qhaHwL8efkMRlRUpPv7rqD0RrJ+FgAjLy1a20OIxZJEEuNCRfIApj+om4bGM3u2/sYU'.
	    '9J41d8973f3Dhg1pISTV1dXXBRNJxPGFCzhou+DCQrScZOkktNaeDZjamgeZ9MgiYmVDccvHhjAzJw0NTh8/alyZMaVJicp0iTHj'.
	    'JpgNv38tjWUhhGROdbUL9W5/MH5XCkjlcibi+KIop5LVHLKEu8A/f4r286doa9pGrGwYAAsfqbbH3b8MgO/Nqgy6WvdbbXHMkEFJ'.
	    '4xUOMVEvaTZu3BgmvF4Yk4hz9rO/Ulr5cE9owae/rcGxohSOuiWkC2IjcIqKyPZm+OmCH7GhoZEF077EEzVVweAbJ+riEeO0Ey8y'.
	    'UubqOHn0AOgMwvf59txnBrSp9dgxKmf/+kIP1NY8SFk0jh5ajmNHAWg5b2E5EexojGHjbiVRMoRMNs0LC+Yz46vTuH3enN7BI8fr'.
	    'qFdo0BoVZNC9aVSQ4fNjBzEmQJiARxb+/AqYPMAVB5FsPU5v37g9OxgLhe14ZM5/ju052E6MNZvf5pmHHuLmmWOkEysxUtpGAtme'.
	    'dtHTflJkezqQto3jFRnLssyf1jydxiiM7zNnye/c3ZsqLu2BN5fcMfzrv/hby1tPzmRUoihcTJ87CwQI2yLtDcIqsIjYUf51qBlf'.
	    'OnScOSrdQUOMURkiXsLUzJnvbGhoBGDHH5cGyZLhOpYoNl5hqYnYEXOu5fDl9eYAHntx98n8hFHZcPHUuTSxSASAeK/CGIOxJJ0f'.
	    'bOGNPU280dgkq6Y2yu8vfjCIlwwzr+/ZQ/PHO0gOLuO5qsftDQ2NbN+4OCgqG6WTxWVaq6zpF+DiSHWnicdylp3r6aZTWthIOrNp'.
	    'ktHcvBu0sHX1Sm6ozB3B42d90zZA9bQp7PvgPSzXZfnqX/HS4DKKK2+x69Y/HURs26iBAN5ccsfw7774UcumF37C6f07KSt2OHji'.
	    'DEUJD0tISjyPrrSPlAKvN0JP/U4O1NfjuhG2rvklN1SOpfXwftpbTqAyKRrff5fb7rs9V1R7m4wlz2ihA3HpmXflUWyOH2umpLiY'.
	    'ui3v8M+6bWzfsRNbSgqkxaCkiy0simMuEWEhpcRzIhQWOIAh6tiAwS4owInFiTou5dOnMnl2NR++ujBwXEc9terD6M43nrj6LgAB'.
	    'QnDPA9/irtkP8JRS7Hr/3T6YekDQ1pEiEXOwpUVJzCVlZZFS4mZtkpEo9ChAkDp/jtLMBACy6S4RiQghLyv5cgBRPnKUOX6smUGF'.
	    'hSil0MYw9d77mPy1e5mnFE3batm3czvb6nYgEJztSFGU9LCRlMRdUjIH0+lnEMIwPNXD3NumoVJnrMCJaiciMUZfvQnz4QcBSvV1'.
	    'vjE5GK358t0zmXDnDB79saLpo20c+aSRD+t25JTp7GZQwsEWFiVxl6hlUf/WO9z32CxmL1rOe6u/I2KuwGhzLQCB7/sYY9Bah3el'.
	    'FKbvrrVm4vS7GH/7ncx+chEHGz7myCeNbPtoO0JI2jq78WIRLGkzsqs7V5SfFV5EovXACoiqqsfNpk2vo5VCWtYFBfoU0VoTBAFa'.
	    'a7TRaK2p+MoURk+cxMzq+Rzbv49DDbuo27UTW9h0dedssPxuK+kIfN8XxhgDYPVXf2Fh4XKtFIl4AiklAlBKAYRKKK36wHIweTCt'.
	    'NfHiEkaOn8j0+7/BmDFjaT30GbHywSxcuZkpFfFg+m1jjZ/NmnVvNfRvwd69e8WBA/uNFAIh4JVXXmHsmDHE4vEQQgjQ2lxQIm9N'.
	    'nz35q3BEOZOHzaG2thaA4mRU+L29It+IV21CpbRQfeMFC35gRB/M2rVrubnyZmLxWJhECBEmz/eHyo/7lMlH3LFFujsthNFCCGOu'.
	    '+WNyeUgpjSVzMKtWraKyshLPdcPEeYWCIEBdpIxSivr6eta8vI7d6+cGnhdV06pe1QP+F/QXWmuRL+jZZ58LlVmxYgUVFRV4rhtu'.
	    '4TzMxXAA6XRaRAtsYUkx8I/JtSJQOlSwpmZpCLN8+fPcdNNoHMfB9/0QJgRoP295TlR7UVv8xxZcHMuWIZ9/Hn35vG3JEGZpzVJG'.
	    'jx5N1IlitKahsZE1L69j69qHgx+urFX/lQL9JYdLlfnZihUhzOLFi8N3Ml1dthOxVH/f/8/CtqSJ2JaJ2JZ59J7RPsC/AViJsQS/'.
	    'dBntAAAAAElFTkSuQmCC' ;

	//==========================================================
	// folder.png
	//==========================================================
        $this->iBuiltinIcon[11][0]= 1824 ;
	$this->iBuiltinIcon[11][1]=
	    'iVBORw0KGgoAAAANSUhEUgAAACIAAAAiCAYAAAA6RwvCAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz'.
	    'AAALEAAACxABrSO9dQAAAAd0SU1FB9ECAQgFFyd9cRUAAAadSURBVHiczdhvbBP3Hcfx9/2xfefEOA5JoCNNnIT8AdtZmYBETJsI'.
	    '6+jQOlQihT1AYgytqzZpD1atfyYqlT1h0lRpT7aRJ4NQpRvZGELVuo5Ua9jEJDIETQsNQyPBsUJMWGPnj//e+e72wNg4xElMR6ed'.
	    'ZNln3933dZ/f93f6yfB/sgmrHdDV1WXlPg8NDZUDScD8LFFFEZZlWYZhWMFg0Orq6sq/gDJAfFy1iiZy9OjrVnj4JzQ1rMWqfxm/'.
	    '309jYyNtbW0kEgnu3bvH4cOH88c/jqSKQl4/XGkd+eVtAN46up1LH92ktqYS++ZX8Pv9NDQ0sGnTJlKpFOFwmO7u7vy5IyMjeVRd'.
	    'XV1+WEOh0IrY4pDnq6wXX/sTiCJaMkFZdRNqxefoe7VtCSqXVDqdZnZ2ltraWkzTpKqqijt3JpFlG7dvj7NzZ1f++qFQyA3EClHL'.
	    'Ql743nFkhxPDtJAd5eTaYSVUfX09lZWVlJWVIUnSg7sVQMBCUcu4ceMGe/bsIRQK1QAzOcyykIM9P0KyudAyCWyqG8nhwqa4SkLt'.
	    '3r0bVVVxu924XC40TUOWZUQxe97CwgIdHR2LMHIxSCaVInVvFElxE0vMY1Pd2NUKJMWNTXHlUfF//4vETJCelwbpFm3MjP2dt37x'.
	    'AlN+PzU1NViWRSwW4+7du3g8HjweD4qi5EFAJzAExIpCANbooxhplfB0FJvTg6xWIqsVRVF6MopkU3FXPcnkJxGU0VEAdF2noqKC'.
	    'W3/8DpnqLjzep2lubsblcjE8PExHR8fboVDID9xYFpLBDpJF0jDQIncQpWlkm31FlFLtp9PfyuW/vYQj1kPSuRW/38+lj27S2Q7v'.
	    '/aWXUBVUffVNtm3blivVCEwsC5Eyc5iiApEpDEAXMqQdldhSiWVQHjJagud+8Fuexck/zv+K82dfoSbSCsDe75/km+4GVPd6+l5t'.
	    '4zJHcqVUYN2yEEtZQDCSJCueRAYsPY49HsFIZVG6p25JUumFafT4DKJN4amtT7Nz38sk5+5A70HMtEYyMkFiZhxzjQ/poXrLQrRU'.
	    'DFGEeFpAlkQkm4pRiCpIKodKzk0T/2QMh+piPjxKZPwiSkUtu/b9mNnJEWS7E8nhAmvpM60oJDkXJxqNozxRRUxPIesispBBlsXV'.
	    'UaKEFo8gzoaJhz8s2lOmrpUG+WBhJ9/60g+Z+fDXTAXfxllRjl1VkO0OFATsYhYliiK21ZKKhhHnFveUqSdKgwAEOp7F2v51vvw8'.
	    'XH7/N1wd/BlTweuUV65BdtgfoLTSkipsdD3tRi0VYpommUwGwzDwdT5HYEc3giAwcvH3jLz3BlPB67jWeZBEKYsSBWwpHZtNKo4q'.
	    'aHTDsJeeiGEYWJaFZVmYpommaRiGQdPnv0bb1m8gSRL/vPIOV979aR4lmAJ2p4qCgCxksNuKJ6VNpx4NYhgGpmkuQhmGQTqdxjAM'.
	    'qr2d7HtxEEEQuH1tkKvvvkF44tqDnrIcKJKAPf1g+LAUElq8dIiu60sApmnm93Pfzc7OYhgGrie+wFe++ztcLhcT1wf54PzPCU9c'.
	    'w7XWjWS3IdsdOAUBWZAxrRJnTQ6SG5bce2FCpmkughmGQSqVYm5uDtnj44sH38TtdhP6+Dwf//V4ttHXrkGURZJaic8RgHQ6jWma'.
	    'SJKUL5RLKNfIOczDKF3XSSaTRCIRhLJWntp3nGfWrSMxc5OLf3iNP4+68T9Ub9nF76lTpxgfHycajZJKpdA0LZ9GbjYV7hcDWZaF'.
	    'pmnMz88Ti8UYunSLmu1HFi2aVkxkaGjINTY2ttDb24vX6+XQoUNs3ryZ8vJyIDu1BUFYkkxhgxeiWlpaOHPmDE1NTdTX1xe98eWG'.
	    'JnF/9dQZCoXUYDA4AOD1ejlw4ACtra2Ul5fniwmCkEcUJiUIAoFAgL6+Pnw+H21tbfT39z8SxCS7hHsfWH9/8dL4MKqnp4eWlhac'.
	    'TmcekEvMNE2am5s5ceIEgUCA9vZ2Tp48ic/nY3j4UsmQHCYOjJHtpeBKqL1799Lc3IzT6UTXdRobGxkYGKC9vZ3W1tZ8Ko86NJ8a'.
	    'tXHjRo4dO8bp06fZsmULGzZsoL+/n0AggNfr5ezZs/8VpGTU5OSkc//+/acBfD4f1dXV7Nq1i4aGBs6dO4fP5+Pq1SuPBbIiyjTN'.
	    'RUnV1dUNXLhwAa/Xy44dO4jFYgBEo9FFF1r134BPuYlk16LrAYXsAlmtq6sbKDwoFAp9m+ykuP5ZQVZF3f8tCdwCov8LyHIoAANI'.
	    'AXf/A1TI0XCDh7OWAAAAAElFTkSuQmCC' ;

	//==========================================================
	// file_important.png
	//==========================================================
	$this->iBuiltinIcon[12][0]= 1785 ;
	$this->iBuiltinIcon[12][1]=
	    'iVBORw0KGgoAAAANSUhEUgAAACIAAAAiCAYAAAA6RwvCAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz'.
	    'AAALDwAACw8BkvkDpQAAAAd0SU1FB9ECDAcjDeD3lKsAAAZ2SURBVHicrZhPaFzHHcc/897s7lutJCsr2VHsOHWMk0MPbsBUrcnF'.
	    'OFRdSo6FNhdB6SGHlpDmYtJCDyoxyKe6EBxKQkt7KKL0T6ABo0NbciqigtC6PhWKI2NFqqxdSd7V2/dmftPDvPd212t55dCBYfbN'.
	    'zpvfZ77z+/1mdhUjytWrV93Hf/24eD5z9gwiMlDjOKbb7dLtdhER2u02u7u73Lp1CxEZBw4AeZwdNQqkMd9wbziFGINJUt6rRbz5'.
	    '1ptUq1XK5TJBEAAUMHt7e+zu7gKwvLzMysoKwAng/uNg9CgQgFKlgg1DUJ67Vqtx6tQpZmdniaIIpRTOOZRSdDoddnZ2aLfbLC8v'.
	    's7S0xJUrV7ZGwQSj1PhhfRodVdDlMrpc5vup5Z2fvMPdu3fZ29vDWjvwztjYGPV6nVqtRqVS4dKlSywtLQFsAdOH2XwsCEApg3jl'.
	    'w98Rak2gvYjNZpNms0mSJDjnHgkDMDc3dySYQ0Ea8w139YUX0OUKulzyg7UmCEO+l1huvHuDra0t9vf3h1TJYSqVypFhHquIrlQI'.
	    'S5qv/uIDAC7/4bcEQYAKvK+0Wq1DVQGIoog7d+4cCeaRII35hrt+8SsEOkRlUaEyR0UpFIrXHxyMVKVUKnHv3r0jwRwaNelBjBjL'.
	    'Sz/7KYuLiwAsLi7y4z/9kY9e+TpkCuSqjI+Po7XuAWeKXLt2DWNMUZMkwRjDhQsXWFtbK6JpCCT3jfQgxomPtPX19YHWicM5x3c2'.
	    '73Pj3Ru8/aO3mZqaolKpoHVvyuvXr/Ppnf/Q7uzz380NPtu4y/qnG+ztd1hfX2dtbQ3gIvDnRyqSxl1UoPjyz98D4PTp0wPtq39Z'.
	    '4fdzLxegrVaLVqvF5OQkYRgWqpRKJZ77wvNsbW1RG5tgfKLOTH2G7Z1twqBQrgrMDvhInjfSOCY5iIv+hYWFgRZArEWsZWF941Bf'.
	    'SdMUgMnJCWpjVU4cn+HUyePM1Gc4+fRUPkzBI5w1jbukcczLv/5l0XfmzJmBFuCba38r/CRXpT+CrDUoZ0jjB4RYonJAOYRobJKT'.
	    'z5zgqfqxAbsFSH6mpHFM2qdGXh4VnoViD6mSJF2cTQeqDqBaKVHWmonJCWpZjhkC6anR5WsffTgwaHV1FaUUq6urA/2v3f5k4LnV'.
	    'arG9tUn3oI2YBCcWHYAxMVYs1qZEZY2SFB2aYZDGfMN9d7uJiWPSeFiNo5Rclc3NTXZbO6RpF7EJVixYA9agwwDnUiqlEPdQ3imi'.
	    'Jo27BGHIt/7x9yEjc3Nzh27Na7c/4TdffKl4bja3ae5MUIu0T/HOEIaOpJt4gwoSsVTK4SBIY77hFtY3ABBjBiZ90rKwvsH77/+K'.
	    't37wOhO1iPpTk4SBw1mLsz6CnKQ4l3qV+kE+t9XHlNZOk+bUJLVIE1VCcIJWQmJ6qjj30NbcXLkZMt8YPig+Z3n1G5fZ39/j/vY2'.
	    '9ckqZT2Ochbn0p4qNkU/dDfUADdXbh4HXgRO4zNdEU0XL1784PLly5w9e7Z4SazFOfGrEotDcOKrcoJPmrYIXf/Zop3QNd1skuGt'.
	    'cUAb2MgAxvHZTgFUq1Wmp6eZnZ0F8JlTjDduDThBnDeECEoJtbGIp6enqEblzCcEZ1PECU4yVRiOGgd0gc+AB0CZvkv1sWPHOHfu'.
	    'HOfPn8da41cpkkltEBEPJhYnBkTQJcdYVKGkgRxCfBsq5xXNgAa2Bn+hjTOgHEKBP8pzRUxykIH4ifLJRTJAl+UMBJzPHQ6bfe/f'.
	    'cWIzPxlUpD+zugzIZtVk1d8znBAqRxgoQuVQgSJQ3h9C5QhDRYgjUILCAzlnEdsHYTKfMTEBcP7F54YUGVmc2GLlIn6ve6v0ahSt'.
	    '8X25TzjJ+rIx1grKpQPWR4LkGVVsMgghvS0qjPdvm5OeceOTWA5Evo2mFzkjQfL7hZPUy5yvvF/uPFQL3+nbDmsLCEmT3sTmCTNr'.
	    'rogT6yFsOix3ftw7OwQhkvSU6CuinhCk0+kAkFoBazEEICHaHHiPVmU0gnUp4EAc1mYrF0EBVpwPi34VrBkwPxKk3W5ju/e5/c+d'.
	    'bGUHIAIuydTIE5zfc5Wr4lJcahHnHTP3CVGm78DrgY38N+DEibp7dmYKdAQmBh1hjEFjis+9CTWYGK21H6PxPyOI0DobYwzZF/z7'.
	    '7jadTvJtYG0kCD7lfwl49ijgT1gc0AH+dZSJA/xB+Mz/GSIvFoj/B7H1mAd8CO/zAAAAAElFTkSuQmCC' ;

	$this->iLen = count($this->iBuiltinIcon);
    }
}

//===================================================
// Global cache for builtin images
//===================================================
$_gPredefIcons = new PredefIcons();

//===================================================
// CLASS IconImage
// Description: Holds properties for an icon image 
//===================================================
class IconImage {
    private $iGDImage=null;
    private $iWidth,$iHeight;
    private $ixalign='left',$iyalign='center';
    private $iScale=1.0;

    function IconImage($aIcon,$aScale=1) {
	GLOBAL $_gPredefIcons ; 
	if( is_string($aIcon) ) {
	    $this->iGDImage = Graph::LoadBkgImage('',$aIcon);
	}
	elseif( is_integer($aIcon) ) {
	    // Builtin image
	    $this->iGDImage = $_gPredefIcons->GetImg($aIcon);
	}
	else {
	    JpGraphError::RaiseL(6011);
//('Argument to IconImage must be string or integer');
	}
	$this->iScale = $aScale;
	$this->iWidth = Image::GetWidth($this->iGDImage);
	$this->iHeight = Image::GetHeight($this->iGDImage);
    }

    function GetWidth() {
	return round($this->iScale*$this->iWidth);
    }

    function GetHeight() {
	return round($this->iScale*$this->iHeight);
    }

    function SetAlign($aX='left',$aY='center') {

	$this->ixalign = $aX;
	$this->iyalign = $aY;

    }

    function Stroke($aImg,$x,$y) {

	if( $this->ixalign == 'right' ) {
	    $x -= $this->iWidth;
	}
	elseif( $this->ixalign == 'center' ) {
	    $x -= round($this->iWidth/2*$this->iScale);
	}

	if( $this->iyalign == 'bottom' ) {
	    $y -= $this->iHeight;
	}
	elseif( $this->iyalign == 'center' ) {
	    $y -= round($this->iHeight/2*$this->iScale);
	}

	$aImg->Copy($this->iGDImage,
		    $x,$y,0,0,
		    round($this->iWidth*$this->iScale),round($this->iHeight*$this->iScale),
		    $this->iWidth,$this->iHeight);
    }
}


//===================================================
// CLASS TextProperty
// Description: Holds properties for a text
//===================================================
class TextProperty {
    public $iShow=true;
    public $csimtarget='',$csimwintarget='',$csimalt='';
    private $iFFamily=FF_FONT1,$iFStyle=FS_NORMAL,$iFSize=10;
    private $iColor="black";
    private $iText="";
    private $iHAlign="left",$iVAlign="bottom";
	
//---------------
// CONSTRUCTOR	
    function TextProperty($aTxt='') {
	$this->iText = $aTxt;
    }		
	
//---------------
// PUBLIC METHODS	
    function Set($aTxt) {
	$this->iText = $aTxt;
    }

    function SetCSIMTarget($aTarget,$aAltText='',$aWinTarget='') {
	if( is_string($aTarget) )
	    $aTarget = array($aTarget);
	$this->csimtarget=$aTarget;

	if( is_string($aWinTarget) )
	    $aWinTarget = array($aWinTarget);
	$this->csimwintarget=$aWinTarget;

	if( is_string($aAltText) )
	    $aAltText = array($aAltText);
        $this->csimalt=$aAltText;
	
    }
    
    function SetCSIMAlt($aAltText) {
	if( is_string($aAltText) )
	    $aAltText = array($aAltText);
        $this->csimalt=$aAltText;
    }

    // Set text color
    function SetColor($aColor) {
	$this->iColor = $aColor;
    }
	
    function HasTabs() {
	if( is_string($this->iText) ) {
	    return substr_count($this->iText,"\t") > 0;
	}
	elseif( is_array($this->iText) ) {
	    return false;
	}
    }
	
    // Get number of tabs in string
    function GetNbrTabs() {
	if( is_string($this->iText) ) {
	    return substr_count($this->iText,"\t") ;
	}
	else{
	    return 0;
	}
    }
	
    // Set alignment
    function Align($aHAlign,$aVAlign="bottom") {
	$this->iHAlign=$aHAlign;
	$this->iVAlign=$aVAlign;
    }
	
    // Synonym
    function SetAlign($aHAlign,$aVAlign="bottom") {
	$this->iHAlign=$aHAlign;
	$this->iVAlign=$aVAlign;
    }
	
    // Specify font
    function SetFont($aFFamily,$aFStyle=FS_NORMAL,$aFSize=10) {
	$this->iFFamily = $aFFamily;
	$this->iFStyle	 = $aFStyle;
	$this->iFSize	 = $aFSize;
    }

    function IsColumns() {
	return is_array($this->iText) ; 
    }
	
    // Get width of text. If text contains several columns separated by
    // tabs then return both the total width as well as an array with a 
    // width for each column.
    function GetWidth($aImg,$aUseTabs=false,$aTabExtraMargin=1.1) {
	$extra_margin=4;
	$aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	if( is_string($this->iText) ) {
	    if( strlen($this->iText) == 0 ) return 0;
	    $tmp = split("\t",$this->iText);
	    if( count($tmp) <= 1 || !$aUseTabs ) {
		$w = $aImg->GetTextWidth($this->iText);
		return $w + 2*$extra_margin;
	    }
	    else {
		$tot=0;
		$n = count($tmp);
		for($i=0; $i < $n; ++$i) {
		    $res[$i] = $aImg->GetTextWidth($tmp[$i]);
		    $tot += $res[$i]*$aTabExtraMargin;
		}
		return array(round($tot),$res);
	    }
	}
	elseif( is_object($this->iText) ) {
	    // A single icon
	    return $this->iText->GetWidth()+2*$extra_margin;
	}
	elseif( is_array($this->iText) ) {
	    // Must be an array of texts. In this case we return the sum of the
	    // length + a fixed margin of 4 pixels on each text string
	    $n = count($this->iText);
	    for( $i=0, $w=0; $i < $n; ++$i ) {
		$tmp = $this->iText[$i];
		if( is_string($tmp) ) {
		    $w += $aImg->GetTextWidth($tmp)+$extra_margin;
		}
		else {
		    if( is_object($tmp) === false ) {
			JpGraphError::RaiseL(6012);
		    }
		    $w += $tmp->GetWidth()+$extra_margin;
		}
	    }
	    return $w;
	}
	else {
	    JpGraphError::RaiseL(6012);
	}
    }

    // for the case where we have multiple columns this function returns the width of each
    // column individually. If there is no columns just return the width of the single
    // column as an array of one
    function GetColWidth($aImg,$aMargin=0) {
	$aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	if( is_array($this->iText) ) {
	    $n = count($this->iText);
	    for( $i=0, $w=array(); $i < $n; ++$i ) {
		$tmp = $this->iText[$i];
		if( is_string($tmp) ) {
		    $w[$i] = $aImg->GetTextWidth($this->iText[$i])+$aMargin;
		}
		else {
		    if( is_object($tmp) === false ) {
			JpGraphError::RaiseL(6012);
		    }
		    $w[$i] = $tmp->GetWidth()+$aMargin;
		}
	    }
	    return $w;	
	}
	else {
	    return array($this->GetWidth($aImg));
	}
    }
	
    // Get total height of text
    function GetHeight($aImg) {
	$aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	return $aImg->GetFontHeight();
    }
	
    // Unhide/hide the text	
    function Show($aShow=true) {
	$this->iShow=$aShow;
    }
	
    // Stroke text at (x,y) coordinates. If the text contains tabs then the
    // x parameter should be an array of positions to be used for each successive
    // tab mark. If no array is supplied then the tabs will be ignored.
    function Stroke($aImg,$aX,$aY) {
	if( $this->iShow ) {
	    $aImg->SetColor($this->iColor);
	    $aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	    $aImg->SetTextAlign($this->iHAlign,$this->iVAlign);			
	    if( $this->GetNbrTabs() <= 1 ) {
		if( is_string($this->iText) ) {
		    // Get rid of any "\t" characters and stroke string
		    if( is_array($aX) ) $aX=$aX[0];
		    if( is_array($aY) ) $aY=$aY[0];
		    $aImg->StrokeText($aX,$aY,str_replace("\t"," ",$this->iText));
		}
		elseif( is_array($this->iText) && ($n = count($this->iText)) > 0 ) {
		    $ax = is_array($aX) ;
		    $ay = is_array($aY) ;
		    if( $ax && $ay ) {
			// Nothing; both are already arrays
		    }
		    elseif( $ax ) {
			$aY = array_fill(0,$n,$aY);
		    }
		    elseif( $ay ) {
			$aX = array_fill(0,$n,$aX);
		    }
		    else {
			$aX = array_fill(0,$n,$aX);
			$aY = array_fill(0,$n,$aY);
		    }
		    $n = min($n, count($aX) ) ;
		    $n = min($n, count($aY) ) ;
		    for($i=0; $i < $n; ++$i ) {
			$tmp = $this->iText[$i];
			if( is_object($tmp) ) {
			    $tmp->Stroke($aImg,$aX[$i],$aY[$i]);
			}
			else
			    $aImg->StrokeText($aX[$i],$aY[$i],str_replace("\t"," ",$tmp));
		    }
		}
	    }
	    else {
		$tmp = split("\t",$this->iText);
		$n = min(count($tmp),count($aX));
		for($i=0; $i < $n; ++$i) {
		    $aImg->StrokeText($aX[$i],$aY,$tmp[$i]);
		}	
	    }
	}
    }
}

//===================================================
// CLASS HeaderProperty
// Description: Data encapsulating class to hold property 
// for each type of the scale headers
//===================================================
class HeaderProperty {
    public $grid;
    public $iShowLabels=true,$iShowGrid=true;
    public $iTitleVertMargin=3,$iFFamily=FF_FONT0,$iFStyle=FS_NORMAL,$iFSize=8;
    public $iStyle=0;
    public $iFrameColor="black",$iFrameWeight=1;
    public $iBackgroundColor="white";
    public $iWeekendBackgroundColor="lightgray",$iSundayTextColor="red"; // these are only used with day scale
    public $iTextColor="black";
    public $iLabelFormStr="%d";
    public $iIntervall = 1;

//---------------
// CONSTRUCTOR	
    function HeaderProperty() {
	$this->grid = new LineProperty();
    }

//---------------
// PUBLIC METHODS		
    function Show($aShow=true) {
	$this->iShowLabels = $aShow;
    }

    function SetIntervall($aInt) {
	$this->iIntervall = $aInt;
    }

    function GetIntervall() {
	return $this->iIntervall ;
    }
	
    function SetFont($aFFamily,$aFStyle=FS_NORMAL,$aFSize=10) {
	$this->iFFamily = $aFFamily;
	$this->iFStyle	 = $aFStyle;
	$this->iFSize	 = $aFSize;
    }

    function SetFontColor($aColor) {
	$this->iTextColor = $aColor;
    }
	
    function GetFontHeight($aImg) {
	$aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	return $aImg->GetFontHeight();
    }

    function GetFontWidth($aImg) {
	$aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	return $aImg->GetFontWidth();
    }

    function GetStrWidth($aImg,$aStr) {
	$aImg->SetFont($this->iFFamily,$this->iFStyle,$this->iFSize);
	return $aImg->GetTextWidth($aStr);
    }
	
    function SetStyle($aStyle) {
	$this->iStyle = $aStyle;
    }
	
    function SetBackgroundColor($aColor) {
	$this->iBackgroundColor=$aColor;
    }

    function SetFrameWeight($aWeight) {
	$this->iFrameWeight=$aWeight;
    }

    function SetFrameColor($aColor) {
	$this->iFrameColor=$aColor;
    }
	
    // Only used by day scale
    function SetWeekendColor($aColor) {
	$this->iWeekendBackgroundColor=$aColor;
    }
	
    // Only used by day scale
    function SetSundayFontColor($aColor) {
	$this->iSundayTextColor=$aColor;
    }
	
    function SetTitleVertMargin($aMargin) {
	$this->iTitleVertMargin=$aMargin;
    }
	
    function SetLabelFormatString($aStr) {
	$this->iLabelFormStr=$aStr;
    }

    function SetFormatString($aStr) {
	$this->SetLabelFormatString($aStr);
    }


}

//===================================================
// CLASS GanttScale
// Description: Responsible for calculating and showing
// the scale in a gantt chart. This includes providing methods for
// converting dates to position in the chart as well as stroking the
// date headers (days, week, etc).
//===================================================
class GanttScale {
    public $minute,$hour,$day,$week,$month,$year;
    public $divider,$dividerh,$tableTitle;
    public $iStartDate=-1,$iEndDate=-1;
    // Number of gantt bar position (n.b not necessariliy the same as the number of bars)
    // we could have on bar in position 1, and one bar in position 5 then there are two
    // bars but the number of bar positions is 5
    public $actinfo;
    public $iTopPlotMargin=10,$iBottomPlotMargin=15;
    public $iVertLines=-1;	
    public $iVertHeaderSize=-1;
    // The width of the labels (defaults to the widest of all labels)
    private $iLabelWidth;	
    // Out image to stroke the scale to
    private $iImg;	
    private $iTableHeaderBackgroundColor="white",$iTableHeaderFrameColor="black";
    private $iTableHeaderFrameWeight=1;
    private $iAvailableHeight=-1,$iVertSpacing=-1;
    private $iDateLocale;
    private $iVertLayout=GANTT_EVEN;
    private $iUsePlotWeekendBackground=true;
    private $iWeekStart = 1;	// Default to have weekends start on Monday
	
//---------------
// CONSTRUCTOR	
    function GanttScale($aImg) {
	$this->iImg = $aImg;		
	$this->iDateLocale = new DateLocale();

	$this->minute = new HeaderProperty();
	$this->minute->SetIntervall(15);
	$this->minute->SetLabelFormatString('i');
	$this->minute->SetFont(FF_FONT0);
	$this->minute->grid->SetColor("gray");

	$this->hour = new HeaderProperty();
	$this->hour->SetFont(FF_FONT0);
	$this->hour->SetIntervall(6);
	$this->hour->SetStyle(HOURSTYLE_HM24);
	$this->hour->SetLabelFormatString('H:i');
	$this->hour->grid->SetColor("gray");

	$this->day = new HeaderProperty();
	$this->day->grid->SetColor("gray");
	$this->day->SetLabelFormatString('l');

	$this->week = new HeaderProperty();
	$this->week->SetLabelFormatString("w%d");
	$this->week->SetFont(FF_FONT1);

	$this->month = new HeaderProperty();
	$this->month->SetFont(FF_FONT1,FS_BOLD);

	$this->year = new HeaderProperty();
	$this->year->SetFont(FF_FONT1,FS_BOLD);		
		
	$this->divider=new LineProperty();
	$this->dividerh=new LineProperty();		
	$this->dividerh->SetWeight(2);
	$this->divider->SetWeight(6);
	$this->divider->SetColor('gray');
	$this->divider->SetStyle('fancy');

	$this->tableTitle=new TextProperty();
	$this->tableTitle->Show(false);
	$this->actinfo = new GanttActivityInfo();
    }
	
//---------------
// PUBLIC METHODS	
    // Specify what headers should be visible
    function ShowHeaders($aFlg) {
	$this->day->Show($aFlg & GANTT_HDAY);
	$this->week->Show($aFlg & GANTT_HWEEK);
	$this->month->Show($aFlg & GANTT_HMONTH);
	$this->year->Show($aFlg & GANTT_HYEAR);
	$this->hour->Show($aFlg & GANTT_HHOUR);
	$this->minute->Show($aFlg & GANTT_HMIN);

	// Make some default settings of gridlines whihc makes sense
	if( $aFlg & GANTT_HWEEK ) {
	    $this->month->grid->Show(false);
	    $this->year->grid->Show(false);
	}
	if( $aFlg & GANTT_HHOUR ) {
	    $this->day->grid->SetColor("black");
	}
    }
	
    // Should the weekend background stretch all the way down in the plotarea
    function UseWeekendBackground($aShow) {
	$this->iUsePlotWeekendBackground = $aShow;
    }
	
    // Have a range been specified?
    function IsRangeSet() {
	return $this->iStartDate!=-1 && $this->iEndDate!=-1;
    }
	
    // Should the layout be from top or even?
    function SetVertLayout($aLayout) {
	$this->iVertLayout = $aLayout;
    }
	
    // Which locale should be used?
    function SetDateLocale($aLocale) {
	$this->iDateLocale->Set($aLocale);
    }
	
    // Number of days we are showing
    function GetNumberOfDays() {
	return round(($this->iEndDate-$this->iStartDate)/SECPERDAY);
    }
	
    // The width of the actual plot area
    function GetPlotWidth() {
	$img=$this->iImg;
	return $img->width - $img->left_margin - $img->right_margin;
    }

    // Specify the width of the titles(labels) for the activities
    // (This is by default set to the minimum width enought for the
    // widest title)
    function SetLabelWidth($aLabelWidth) {
	$this->iLabelWidth=$aLabelWidth;
    }

	// Which day should the week start?
	// 0==Sun, 1==Monday, 2==Tuesday etc
    function SetWeekStart($aStartDay) {
	$this->iWeekStart = $aStartDay % 7;
	
	//Recalculate the startday since this will change the week start
	$this->SetRange($this->iStartDate,$this->iEndDate);
    }

    // Do we show min scale?
    function IsDisplayMinute() {
	return $this->minute->iShowLabels;
    }

    // Do we show day scale?
    function IsDisplayHour() {
	return $this->hour->iShowLabels;
    }

	
    // Do we show day scale?
    function IsDisplayDay() {
	return $this->day->iShowLabels;
    }
	
    // Do we show week scale?
    function IsDisplayWeek() {
	return $this->week->iShowLabels;
    }
	
    // Do we show month scale?
    function IsDisplayMonth() {
	return $this->month->iShowLabels;
    }
	
    // Do we show year scale?
    function IsDisplayYear() {
	return $this->year->iShowLabels;
    }

    // Specify spacing (in percent of bar height) between activity bars
    function SetVertSpacing($aSpacing) {
	$this->iVertSpacing = $aSpacing;
    }

    // Specify scale min and max date either as timestamp or as date strings
    // Always round to the nearest week boundary
    function SetRange($aMin,$aMax) {
	$this->iStartDate = $this->NormalizeDate($aMin);
	$this->iEndDate = $this->NormalizeDate($aMax);	
    }


    // Adjust the start and end date so they fit to beginning/ending
    // of the week taking the specified week start day into account.
    function AdjustStartEndDay() {

	if( !($this->IsDisplayYear() ||$this->IsDisplayMonth() || $this->IsDisplayWeek()) ) {
	    // Don't adjust
	    return;
	}

	// Get day in week for start and ending date (Sun==0)
	$ds=strftime("%w",$this->iStartDate);
	$de=strftime("%w",$this->iEndDate);	
	
	// We want to start on iWeekStart day. But first we subtract a week
	// if the startdate is "behind" the day the week start at. 
	// This way we ensure that the given start date is always included 
	// in the range. If we don't do this the nearest correct weekday in the week 
	// to start at might be later than the start date.
	if( $ds < $this->iWeekStart )
	    $d = strtotime('-7 day',$this->iStartDate);
	else
	    $d = $this->iStartDate;
	$adjdate = strtotime(($this->iWeekStart-$ds).' day',$d /*$this->iStartDate*/ );
	$this->iStartDate = $adjdate;
	
	// We want to end on the last day of the week
	$preferredEndDay = ($this->iWeekStart+6)%7;
	if( $preferredEndDay != $de ) { 
	    // Solve equivalence eq:    $de + x ~ $preferredDay (mod 7)
	    $adj = (7+($preferredEndDay - $de)) % 7;
	    $adjdate = strtotime("+$adj day",$this->iEndDate);
	    $this->iEndDate = $adjdate;	
	}	
    }

    // Specify background for the table title area (upper left corner of the table)	
    function SetTableTitleBackground($aColor) {
	$this->iTableHeaderBackgroundColor = $aColor;
    }

///////////////////////////////////////
// PRIVATE Methods
	
    // Determine the height of all the scale headers combined
    function GetHeaderHeight() {
	$img=$this->iImg;
	$height=1;
	if( $this->minute->iShowLabels ) {
	    $height += $this->minute->GetFontHeight($img);
	    $height += $this->minute->iTitleVertMargin;
	}
	if( $this->hour->iShowLabels ) {
	    $height += $this->hour->GetFontHeight($img);
	    $height += $this->hour->iTitleVertMargin;
	}
	if( $this->day->iShowLabels ) {
	    $height += $this->day->GetFontHeight($img);
	    $height += $this->day->iTitleVertMargin;
	}
	if( $this->week->iShowLabels ) {
	    $height += $this->week->GetFontHeight($img);
	    $height += $this->week->iTitleVertMargin;
	}
	if( $this->month->iShowLabels ) {
	    $height += $this->month->GetFontHeight($img);
	    $height += $this->month->iTitleVertMargin;
	}
	if( $this->year->iShowLabels ) {
	    $height += $this->year->GetFontHeight($img);
	    $height += $this->year->iTitleVertMargin;
	}
	return $height;
    }
	
    // Get width (in pixels) for a single day
    function GetDayWidth() {
	return ($this->GetPlotWidth()-$this->iLabelWidth+1)/$this->GetNumberOfDays();	
    }

    // Get width (in pixels) for a single hour
    function GetHourWidth() {
	return $this->GetDayWidth() / 24 ;
    }

    function GetMinuteWidth() {
	return $this->GetHourWidth() / 60 ;
    }

    // Nuber of days in a year
    function GetNumDaysInYear($aYear) {
	if( $this->IsLeap($aYear) )
	    return 366;
	else
	    return 365;
    }
	
    // Get week number 
    function GetWeekNbr($aDate,$aSunStart=true) {
	// We can't use the internal strftime() since it gets the weeknumber
	// wrong since it doesn't follow ISO on all systems since this is
	// system linrary dependent.
	// Even worse is that this works differently if we are on a Windows
	// or UNIX box (it even differs between UNIX boxes how strftime()
	// is natively implemented)
	//
	// Credit to Nicolas Hoizey <nhoizey@phpheaven.net> for this elegant
	// version of Week Nbr calculation. 

	$day = $this->NormalizeDate($aDate);
	if( $aSunStart )
	    $day += 60*60*24;
		
	/*-------------------------------------------------------------------------
	  According to ISO-8601 :
	  "Week 01 of a year is per definition the first week that has the Thursday in this year,
	  which is equivalent to the week that contains the fourth day of January.
	  In other words, the first week of a new year is the week that has the majority of its
	  days in the new year."
		  
	  Be carefull, with PHP, -3 % 7 = -3, instead of 4 !!!
		  
	  day of year             = date("z", $day) + 1
	  offset to thursday      = 3 - (date("w", $day) + 6) % 7
	  first thursday of year  = 1 + (11 - date("w", mktime(0, 0, 0, 1, 1, date("Y", $day)))) % 7
	  week number             = (thursday's day of year - first thursday's day of year) / 7 + 1
	  ---------------------------------------------------------------------------*/
		 
	$thursday = $day + 60 * 60 * 24 * (3 - (date("w", $day) + 6) % 7);              // take week's thursday
	$week = 1 + (date("z", $thursday) - (11 - date("w", mktime(0, 0, 0, 1, 1, date("Y", $thursday)))) % 7) / 7;
		  
	return $week;
    }
	
    // Is year a leap year?
    function IsLeap($aYear) {
	// Is the year a leap year?
	//$year = 0+date("Y",$aDate);
	if( $aYear % 4 == 0)
	    if( !($aYear % 100 == 0) || ($aYear % 400 == 0) )
		return true;
	return false;
    }

    // Get current year
    function GetYear($aDate) {
	return 0+Date("Y",$aDate);
    }
	
    // Return number of days in a year
    function GetNumDaysInMonth($aMonth,$aYear) {
	$days=array(31,28,31,30,31,30,31,31,30,31,30,31);
	$daysl=array(31,29,31,30,31,30,31,31,30,31,30,31);
	if( $this->IsLeap($aYear))
	    return $daysl[$aMonth];
	else
	    return $days[$aMonth];
    }
	
    // Get day in month
    function GetMonthDayNbr($aDate) {
	return 0+strftime("%d",$aDate);
    }

    // Get day in year
    function GetYearDayNbr($aDate) {
	return 0+strftime("%j",$aDate);
    }
	
    // Get month number
    function GetMonthNbr($aDate) {
	return 0+strftime("%m",$aDate);
    }
	
    // Translate a date to screen coordinates	(horizontal scale)
    function TranslateDate($aDate) {
	//
	// In order to handle the problem with Daylight savings time
	// the scale written with equal number of seconds per day beginning
	// with the start date. This means that we "cement" the state of
	// DST as it is in the start date. If later the scale includes the
	// switchover date (depends on the locale) we need to adjust back
	// if the date we try to translate has a different DST status since
	// we would otherwise be off by one hour.
	$aDate = $this->NormalizeDate($aDate);
	$tmp = localtime($aDate);
	$cloc = $tmp[8];
	$tmp = localtime($this->iStartDate);
	$sloc = $tmp[8];
	$offset = 0;
	if( $sloc != $cloc) {
	    if( $sloc ) 
		$offset = 3600;
	    else
		$offset = -3600;
	}
	$img=$this->iImg;		
	return ($aDate-$this->iStartDate-$offset)/SECPERDAY*$this->GetDayWidth()+$img->left_margin+$this->iLabelWidth;;
    }

    // Get screen coordinatesz for the vertical position for a bar		
    function TranslateVertPos($aPos) {
	$img=$this->iImg;
	$ph=$this->iAvailableHeight;
	if( $aPos > $this->iVertLines ) 
	    JpGraphError::RaiseL(6015,$aPos);
// 'Illegal vertical position %d'
	if( $this->iVertLayout == GANTT_EVEN ) {
	    // Position the top bar at 1 vert spacing from the scale
	    return round($img->top_margin + $this->iVertHeaderSize +  ($aPos+1)*$this->iVertSpacing);
	}
	else {
	    // position the top bar at 1/2 a vert spacing from the scale
	    return round($img->top_margin + $this->iVertHeaderSize  + $this->iTopPlotMargin + ($aPos+1)*$this->iVertSpacing);		
	}
    }
	
    // What is the vertical spacing?
    function GetVertSpacing() {
	return $this->iVertSpacing;
    }
					
    // Convert a date to timestamp
    function NormalizeDate($aDate) {
	if( $aDate === false ) return false; 
	if( is_string($aDate) ) {
	    $t = strtotime($aDate);
	    if( $t === FALSE || $t === -1 ) {
		JpGraphError::RaiseL(6016,$aDate);
//("Date string ($aDate) specified for Gantt activity can not be interpretated. Please make sure it is a valid time string, e.g. 2005-04-23 13:30");
	    }
	    return $t;
	}
	elseif( is_int($aDate) || is_float($aDate) )
	    return $aDate;
	else
	    JpGraphError::RaiseL(6017,$aDate);
//Unknown date format in GanttScale ($aDate).");
    }

    
    // Convert a time string to minutes

    function TimeToMinutes($aTimeString) {
	// Split in hours and minutes
	$pos=strpos($aTimeString,':');
	$minint=60;
	if( $pos === false ) {
	    $hourint = $aTimeString;
	    $minint = 0;
	}
	else {
	    $hourint = floor(substr($aTimeString,0,$pos));
	    $minint = floor(substr($aTimeString,$pos+1));
	}
	$minint += 60 * $hourint;
	return $minint;
    }

    // Stroke the day scale (including gridlines)			
    function StrokeMinutes($aYCoord,$getHeight=false) {
	$img=$this->iImg;	
	$xt=$img->left_margin+$this->iLabelWidth;
	$yt=$aYCoord+$img->top_margin;		
	if( $this->minute->iShowLabels ) {
	    $img->SetFont($this->minute->iFFamily,$this->minute->iFStyle,$this->minute->iFSize);
	    $yb = $yt + $img->GetFontHeight() + 
		  $this->minute->iTitleVertMargin + $this->minute->iFrameWeight;
	    if( $getHeight ) {
		return $yb - $img->top_margin;
	    }
	    $xb = $img->width-$img->right_margin+1;
	    $img->SetColor($this->minute->iBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);

	    $x = $xt;   
	    $img->SetTextAlign("center");
	    $day = date('w',$this->iStartDate);
	    $minint = $this->minute->GetIntervall() ;
	    
	    if( 60 % $minint !== 0 ) { 
                JpGraphError::RaiseL(6018,$minint);
//'Intervall for minutes must divide the hour evenly, e.g. 1,5,10,12,15,20,30 etc You have specified an intervall of '.$minint.' minutes.');
            } 


	    $n = 60 / $minint;
	    $datestamp = $this->iStartDate;
	    $width = $this->GetHourWidth() / $n ;
	    if( $width < 8 ) {
		// TO small width to draw minute scale
		JpGraphError::RaiseL(6019,$width);
//('The available width ('.$width.') for minutes are to small for this scale to be displayed. Please use auto-sizing or increase the width of the graph.');
	    }

	    $nh = ceil(24*60 / $this->TimeToMinutes($this->hour->GetIntervall()) );
	    $nd = $this->GetNumberOfDays();
	    // Convert to intervall to seconds
	    $minint *= 60;
	    for($j=0; $j < $nd; ++$j, $day += 1, $day %= 7) {
		for( $k=0; $k < $nh; ++$k ) {
		    for($i=0; $i < $n ;++$i, $x+=$width, $datestamp += $minint ) {   
			if( $day==6 || $day==0 ) {
			
			    $img->PushColor($this->day->iWeekendBackgroundColor);
			    if( $this->iUsePlotWeekendBackground )
				$img->FilledRectangle($x,$yt+$this->day->iFrameWeight,$x+$width,$img->height-$img->bottom_margin);						
			    else
				$img->FilledRectangle($x,$yt+$this->day->iFrameWeight,$x+$width,$yb-$this->day->iFrameWeight);
			    $img->PopColor();

			}

			if( $day==0 ) 
			    $img->SetColor($this->day->iSundayTextColor);
			else
			    $img->SetColor($this->day->iTextColor);

			switch( $this->minute->iStyle ) {
			    case MINUTESTYLE_CUSTOM:
				$txt = date($this->minute->iLabelFormStr,$datestamp);
				break;
			    case MINUTESTYLE_MM:
			    default:
				// 15
				$txt = date('i',$datestamp);
				break;
			}
			$img->StrokeText(round($x+$width/2),round($yb-$this->minute->iTitleVertMargin),$txt);

			// FIXME: The rounding problem needs to be solved properly ...
			//
			// Fix a rounding problem the wrong way ..
			// If we also have hour scale then don't draw the firsta or last
			// gridline since that will be overwritten by the hour scale gridline if such exists.
			// However, due to the propagation of rounding of the 'x+=width' term in the loop
			// this might sometimes be one pixel of so we fix this by not drawing it.
			// The proper way to fix it would be to re-calculate the scale for each step and
			// not using the additive term.
			if( !(($i == $n || $i==0) && $this->hour->iShowLabels && $this->hour->grid->iShow) ) {
			    $img->SetColor($this->minute->grid->iColor);
			    $img->SetLineWeight($this->minute->grid->iWeight);
			    $img->Line($x,$yt,$x,$yb);
			    $this->minute->grid->Stroke($img,$x,$yb,$x,$img->height-$img->bottom_margin);
			}
		    }		
		}	
	    }
	    $img->SetColor($this->minute->iFrameColor);
	    $img->SetLineWeight($this->minute->iFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);
	    return $yb - $img->top_margin;
	}
	return $aYCoord;
    }

    // Stroke the day scale (including gridlines)			
    function StrokeHours($aYCoord,$getHeight=false) {
	$img=$this->iImg;	
	$xt=$img->left_margin+$this->iLabelWidth;
	$yt=$aYCoord+$img->top_margin;		
	if( $this->hour->iShowLabels ) {
	    $img->SetFont($this->hour->iFFamily,$this->hour->iFStyle,$this->hour->iFSize);
	    $yb = $yt + $img->GetFontHeight() + 
		  $this->hour->iTitleVertMargin + $this->hour->iFrameWeight;
	    if( $getHeight ) {
		return $yb - $img->top_margin;
	    }
	    $xb = $img->width-$img->right_margin+1;
	    $img->SetColor($this->hour->iBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);

	    $x = $xt;   
	    $img->SetTextAlign("center");
	    $tmp = $this->hour->GetIntervall() ;
	    $minint = $this->TimeToMinutes($tmp);
	    if( 1440 % $minint !== 0 ) { 
                JpGraphError::RaiseL(6020,$tmp);
//('Intervall for hours must divide the day evenly, e.g. 0:30, 1:00, 1:30, 4:00 etc. You have specified an intervall of '.$tmp);
            } 

	    $n = ceil(24*60 / $minint );
	    $datestamp = $this->iStartDate;
	    $day = date('w',$this->iStartDate);
	    $doback = !$this->minute->iShowLabels;
	    $width = $this->GetDayWidth() / $n ;
	    for($j=0; $j < $this->GetNumberOfDays(); ++$j, $day += 1,$day %= 7) {
		for($i=0; $i < $n ;++$i, $x+=$width) {   
		    if( $day==6 || $day==0 ) {
			
			$img->PushColor($this->day->iWeekendBackgroundColor);
			if( $this->iUsePlotWeekendBackground && $doback )
			    $img->FilledRectangle($x,$yt+$this->day->iFrameWeight,$x+$width,$img->height-$img->bottom_margin);						
			else
			    $img->FilledRectangle($x,$yt+$this->day->iFrameWeight,$x+$width,$yb-$this->day->iFrameWeight);
			$img->PopColor();

		    }

		    if( $day==0 ) 
			$img->SetColor($this->day->iSundayTextColor);
		    else
			$img->SetColor($this->day->iTextColor);

		    switch( $this->hour->iStyle ) {
			case HOURSTYLE_HMAMPM:
			    // 1:35pm
			    $txt = date('g:ia',$datestamp);
			    break;
			case HOURSTYLE_H24:
			    // 13
			    $txt = date('H',$datestamp);
			    break;
			case HOURSTYLE_HAMPM:
			    $txt = date('ga',$datestamp);
			    break;
			case HOURSTYLE_CUSTOM:
			    $txt = date($this->hour->iLabelFormStr,$datestamp);
			    break;
			case HOURSTYLE_HM24:
			default:
			    $txt = date('H:i',$datestamp);
			    break;
		    }
		    $img->StrokeText(round($x+$width/2),round($yb-$this->hour->iTitleVertMargin),$txt);
		    $img->SetColor($this->hour->grid->iColor);
		    $img->SetLineWeight($this->hour->grid->iWeight);
		    $img->Line($x,$yt,$x,$yb);
		    $this->hour->grid->Stroke($img,$x,$yb,$x,$img->height-$img->bottom_margin);
		    //$datestamp += $minint*60
		    $datestamp = mktime(date('H',$datestamp),date('i',$datestamp)+$minint,0,
					date("m",$datestamp),date("d",$datestamp)+1,date("Y",$datestamp));
		    
		}			
	    }
	    $img->SetColor($this->hour->iFrameColor);
	    $img->SetLineWeight($this->hour->iFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);
	    return $yb - $img->top_margin;
	}
	return $aYCoord;
    }


    // Stroke the day scale (including gridlines)			
    function StrokeDays($aYCoord,$getHeight=false) {
	$img=$this->iImg;	
	$daywidth=$this->GetDayWidth();
	$xt=$img->left_margin+$this->iLabelWidth;
	$yt=$aYCoord+$img->top_margin;		
	if( $this->day->iShowLabels ) {
	    $img->SetFont($this->day->iFFamily,$this->day->iFStyle,$this->day->iFSize);
	    $yb=$yt + $img->GetFontHeight() + $this->day->iTitleVertMargin + $this->day->iFrameWeight;
	    if( $getHeight ) {
		return $yb - $img->top_margin;
	    }
	    $xb=$img->width-$img->right_margin+1;
	    $img->SetColor($this->day->iBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);

	    $x = $xt;   
	    $img->SetTextAlign("center");
	    $day = date('w',$this->iStartDate);
	    $datestamp = $this->iStartDate;
	    
	    $doback = !($this->hour->iShowLabels || $this->minute->iShowLabels);

	    setlocale(LC_TIME,$this->iDateLocale->iLocale);
	    
	    for($i=0; $i < $this->GetNumberOfDays(); ++$i, $x+=$daywidth, $day += 1,$day %= 7) {
		if( $day==6 || $day==0 ) {
		    $img->SetColor($this->day->iWeekendBackgroundColor);
		    if( $this->iUsePlotWeekendBackground && $doback)
			$img->FilledRectangle($x,$yt+$this->day->iFrameWeight,
					      $x+$daywidth,$img->height-$img->bottom_margin);	
		    else
			$img->FilledRectangle($x,$yt+$this->day->iFrameWeight,
					      $x+$daywidth,$yb-$this->day->iFrameWeight);
		}

		$mn = strftime('%m',$datestamp);
		if( $mn[0]=='0' ) 
		    $mn = $mn[1];

		switch( $this->day->iStyle ) {
		    case DAYSTYLE_LONG:
			// "Monday"
			$txt = strftime('%A',$datestamp);
			break;
		    case DAYSTYLE_SHORT:
			// "Mon"
			$txt = strftime('%a',$datestamp);
			break;
		    case DAYSTYLE_SHORTDAYDATE1:
			// "Mon 23/6"
			$txt = strftime('%a %d/'.$mn,$datestamp);
			break;
		    case DAYSTYLE_SHORTDAYDATE2:
			// "Mon 23 Jun"
			$txt = strftime('%a %d %b',$datestamp);
			break;
		    case DAYSTYLE_SHORTDAYDATE3:
			// "Mon 23 Jun 2003"
			$txt = strftime('%a %d %b %Y',$datestamp);
			break;
		    case DAYSTYLE_LONGDAYDATE1:
			// "Monday 23 Jun"
			$txt = strftime('%A %d %b',$datestamp);
			break;
		    case DAYSTYLE_LONGDAYDATE2:
			// "Monday 23 Jun 2003"
			$txt = strftime('%A %d %b %Y',$datestamp);
			break;
		    case DAYSTYLE_SHORTDATE1:
			// "23/6"
			$txt = strftime('%d/'.$mn,$datestamp);
			break;			
		    case DAYSTYLE_SHORTDATE2:
			// "23 Jun"
			$txt = strftime('%d %b',$datestamp);
			break;			
		    case DAYSTYLE_SHORTDATE3:
			// "Mon 23"
			$txt = strftime('%a %d',$datestamp);
			break;	
		    case DAYSTYLE_SHORTDATE4:
			// "23"
			$txt = strftime('%d',$datestamp);
			break;	
		    case DAYSTYLE_CUSTOM:
			// Custom format
			$txt = strftime($this->day->iLabelFormStr,$datestamp);
			break;	
		    case DAYSTYLE_ONELETTER:
		    default:
			// "M"
			$txt = strftime('%A',$datestamp);
			$txt = strtoupper($txt[0]);
			break;
		}

		if( $day==0 ) 
		    $img->SetColor($this->day->iSundayTextColor);
		else
		    $img->SetColor($this->day->iTextColor);
		$img->StrokeText(round($x+$daywidth/2+1),
				 round($yb-$this->day->iTitleVertMargin),$txt);
		$img->SetColor($this->day->grid->iColor);
		$img->SetLineWeight($this->day->grid->iWeight);
		$img->Line($x,$yt,$x,$yb);
		$this->day->grid->Stroke($img,$x,$yb,$x,$img->height-$img->bottom_margin);
		$datestamp = mktime(0,0,0,date("m",$datestamp),date("d",$datestamp)+1,date("Y",$datestamp));
		//$datestamp += SECPERDAY;
		
	    }			
	    $img->SetColor($this->day->iFrameColor);
	    $img->SetLineWeight($this->day->iFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);
	    return $yb - $img->top_margin;
	}
	return $aYCoord;
    }
	
    // Stroke week header and grid
    function StrokeWeeks($aYCoord,$getHeight=false) {
	if( $this->week->iShowLabels ) {
	    $img=$this->iImg;	
	    $yt=$aYCoord+$img->top_margin;		
	    $img->SetFont($this->week->iFFamily,$this->week->iFStyle,$this->week->iFSize);
	    $yb=$yt + $img->GetFontHeight() + $this->week->iTitleVertMargin + $this->week->iFrameWeight;

	    if( $getHeight ) {
		return $yb - $img->top_margin;  
	    }

	    $xt=$img->left_margin+$this->iLabelWidth;
	    $weekwidth=$this->GetDayWidth()*7;
	    $wdays=$this->iDateLocale->GetDayAbb();	
	    $xb=$img->width-$img->right_margin+1;
	    $week = $this->iStartDate;
	    $weeknbr=$this->GetWeekNbr($week);
	    $img->SetColor($this->week->iBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);
	    $img->SetColor($this->week->grid->iColor);
	    $x = $xt;
	    if( $this->week->iStyle==WEEKSTYLE_WNBR ) {
		$img->SetTextAlign("center");
		$txtOffset = $weekwidth/2+1;
	    }
	    elseif( $this->week->iStyle==WEEKSTYLE_FIRSTDAY  || 
		    $this->week->iStyle==WEEKSTYLE_FIRSTDAY2 ||
		    $this->week->iStyle==WEEKSTYLE_FIRSTDAYWNBR ||
		    $this->week->iStyle==WEEKSTYLE_FIRSTDAY2WNBR ) {
		$img->SetTextAlign("left");
		$txtOffset = 3;
	    }
	    else
		JpGraphError::RaiseL(6021);
//("Unknown formatting style for week.");
				
	    for($i=0; $i<$this->GetNumberOfDays()/7; ++$i, $x+=$weekwidth) {
		$img->PushColor($this->week->iTextColor);
				
		if( $this->week->iStyle==WEEKSTYLE_WNBR )
		    $txt = sprintf($this->week->iLabelFormStr,$weeknbr);
		elseif( $this->week->iStyle==WEEKSTYLE_FIRSTDAY || 
			$this->week->iStyle==WEEKSTYLE_FIRSTDAYWNBR ) 
		    $txt = date("j/n",$week);
		elseif( $this->week->iStyle==WEEKSTYLE_FIRSTDAY2 || 
			$this->week->iStyle==WEEKSTYLE_FIRSTDAY2WNBR ) {
		    $monthnbr = date("n",$week)-1;
		    $shortmonth = $this->iDateLocale->GetShortMonthName($monthnbr);
		    $txt = Date("j",$week)." ".$shortmonth;
		}

		if( $this->week->iStyle==WEEKSTYLE_FIRSTDAYWNBR ||
		    $this->week->iStyle==WEEKSTYLE_FIRSTDAY2WNBR ) {
		    $w = sprintf($this->week->iLabelFormStr,$weeknbr);
		    $txt .= ' '.$w;
		}
				
		$img->StrokeText(round($x+$txtOffset),
				 round($yb-$this->week->iTitleVertMargin),$txt);
				
		$week = strtotime('+7 day',$week); 
		$weeknbr = $this->GetWeekNbr($week);
		$img->PopColor();	
		$img->SetLineWeight($this->week->grid->iWeight);
		$img->Line($x,$yt,$x,$yb);
		$this->week->grid->Stroke($img,$x,$yb,$x,$img->height-$img->bottom_margin);
	    }			
	    $img->SetColor($this->week->iFrameColor);
	    $img->SetLineWeight($this->week->iFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);
	    return $yb-$img->top_margin;
	}
	return $aYCoord;
    }	
	
    // Format the mont scale header string
    function GetMonthLabel($aMonthNbr,$year) {
	$sn = $this->iDateLocale->GetShortMonthName($aMonthNbr);
	$ln = $this->iDateLocale->GetLongMonthName($aMonthNbr);
	switch($this->month->iStyle) {
	    case MONTHSTYLE_SHORTNAME:
		$m=$sn;
		break;
	    case MONTHSTYLE_LONGNAME:
		$m=$ln;
		break;
	    case MONTHSTYLE_SHORTNAMEYEAR2:
		$m=$sn." '".substr("".$year,2);
		break;
	    case MONTHSTYLE_SHORTNAMEYEAR4:
		$m=$sn." ".$year;
		break;
	    case MONTHSTYLE_LONGNAMEYEAR2:
		$m=$ln." '".substr("".$year,2);
		break;
	    case MONTHSTYLE_LONGNAMEYEAR4:
		$m=$ln." ".$year;
		break;
	    case MONTHSTYLE_FIRSTLETTER:
		$m=$sn[0];
		break;
	}
	return $m;
    }
	
    // Stroke month scale and gridlines
    function StrokeMonths($aYCoord,$getHeight=false) {
	if( $this->month->iShowLabels ) {
	    $img=$this->iImg;		
	    $img->SetFont($this->month->iFFamily,$this->month->iFStyle,$this->month->iFSize);
	    $yt=$aYCoord+$img->top_margin;		
	    $yb=$yt + $img->GetFontHeight() + $this->month->iTitleVertMargin + $this->month->iFrameWeight;
	    if( $getHeight ) {
		return $yb - $img->top_margin;  
	    }
	    $monthnbr = $this->GetMonthNbr($this->iStartDate)-1; 
	    $xt=$img->left_margin+$this->iLabelWidth;
	    $xb=$img->width-$img->right_margin+1;
			
	    $img->SetColor($this->month->iBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);

	    $img->SetLineWeight($this->month->grid->iWeight);
	    $img->SetColor($this->month->iTextColor);
	    $year = 0+strftime("%Y",$this->iStartDate);
	    $img->SetTextAlign("center");
	    if( $this->GetMonthNbr($this->iStartDate) == $this->GetMonthNbr($this->iEndDate)  
		&& $this->GetYear($this->iStartDate)==$this->GetYear($this->iEndDate) ) {
	    	$monthwidth=$this->GetDayWidth()*($this->GetMonthDayNbr($this->iEndDate) - $this->GetMonthDayNbr($this->iStartDate) + 1);
	    } 
	    else {
	    	$monthwidth=$this->GetDayWidth()*($this->GetNumDaysInMonth($monthnbr,$year)-$this->GetMonthDayNbr($this->iStartDate)+1);
	    }
	    // Is it enough space to stroke the first month?
	    $monthName = $this->GetMonthLabel($monthnbr,$year);
	    if( $monthwidth >= 1.2*$img->GetTextWidth($monthName) ) {
		$img->SetColor($this->month->iTextColor);				
		$img->StrokeText(round($xt+$monthwidth/2+1),
				 round($yb-$this->month->iTitleVertMargin),
				 $monthName);
	    }
	    $x = $xt + $monthwidth;
	    while( $x < $xb ) {
		$img->SetColor($this->month->grid->iColor);				
		$img->Line($x,$yt,$x,$yb);
		$this->month->grid->Stroke($img,$x,$yb,$x,$img->height-$img->bottom_margin);
		$monthnbr++;
		if( $monthnbr==12 ) {
		    $monthnbr=0;
		    $year++;
		}
		$monthName = $this->GetMonthLabel($monthnbr,$year);
		$monthwidth=$this->GetDayWidth()*$this->GetNumDaysInMonth($monthnbr,$year);				
		if( $x + $monthwidth < $xb )
		    $w = $monthwidth;
		else
		    $w = $xb-$x;
		if( $w >= 1.2*$img->GetTextWidth($monthName) ) {
		    $img->SetColor($this->month->iTextColor);				
		    $img->StrokeText(round($x+$w/2+1),
				     round($yb-$this->month->iTitleVertMargin),$monthName);
		}
		$x += $monthwidth;
	    }	
	    $img->SetColor($this->month->iFrameColor);
	    $img->SetLineWeight($this->month->iFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);			
	    return $yb-$img->top_margin;
	}
	return $aYCoord;
    }

    // Stroke year scale and gridlines
    function StrokeYears($aYCoord,$getHeight=false) {
	if( $this->year->iShowLabels ) {
	    $img=$this->iImg;	
	    $yt=$aYCoord+$img->top_margin;		
	    $img->SetFont($this->year->iFFamily,$this->year->iFStyle,$this->year->iFSize);
	    $yb=$yt + $img->GetFontHeight() + $this->year->iTitleVertMargin + $this->year->iFrameWeight;

	    if( $getHeight ) {
		return $yb - $img->top_margin;  
	    }

	    $xb=$img->width-$img->right_margin+1;
	    $xt=$img->left_margin+$this->iLabelWidth;
	    $year = $this->GetYear($this->iStartDate); 			
	    $img->SetColor($this->year->iBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);
	    $img->SetLineWeight($this->year->grid->iWeight);
	    $img->SetTextAlign("center");
	    if( $year == $this->GetYear($this->iEndDate) )
		$yearwidth=$this->GetDayWidth()*($this->GetYearDayNbr($this->iEndDate)-$this->GetYearDayNbr($this->iStartDate)+1);
	    else
		$yearwidth=$this->GetDayWidth()*($this->GetNumDaysInYear($year)-$this->GetYearDayNbr($this->iStartDate)+1);
			
	    // The space for a year must be at least 20% bigger than the actual text 
	    // so we allow 10% margin on each side
	    if( $yearwidth >= 1.20*$img->GetTextWidth("".$year) ) {
		$img->SetColor($this->year->iTextColor);				
		$img->StrokeText(round($xt+$yearwidth/2+1),
				 round($yb-$this->year->iTitleVertMargin),
				 $year);
	    }
	    $x = $xt + $yearwidth;
	    while( $x < $xb ) {
		$img->SetColor($this->year->grid->iColor);				
		$img->Line($x,$yt,$x,$yb);
		$this->year->grid->Stroke($img,$x,$yb,$x,$img->height-$img->bottom_margin);
		$year += 1;
		$yearwidth=$this->GetDayWidth()*$this->GetNumDaysInYear($year);				
		if( $x + $yearwidth < $xb )
		    $w = $yearwidth;
		else
		    $w = $xb-$x;
		if( $w >= 1.2*$img->GetTextWidth("".$year) ) {
		    $img->SetColor($this->year->iTextColor);
		    $img->StrokeText(round($x+$w/2+1),
				     round($yb-$this->year->iTitleVertMargin),
				     $year);
		}
		$x += $yearwidth;
	    }
	    $img->SetColor($this->year->iFrameColor);
	    $img->SetLineWeight($this->year->iFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);			
	    return $yb-$img->top_margin;
	}
	return $aYCoord;
    }
	
    // Stroke table title (upper left corner)
    function StrokeTableHeaders($aYBottom) {
	$img=$this->iImg;
	$xt=$img->left_margin;
	$yt=$img->top_margin;
	$xb=$xt+$this->iLabelWidth;
	$yb=$aYBottom+$img->top_margin;

	if( $this->tableTitle->iShow ) {
	    $img->SetColor($this->iTableHeaderBackgroundColor);
	    $img->FilledRectangle($xt,$yt,$xb,$yb);
	    $this->tableTitle->Align("center","top");
	    $this->tableTitle->Stroke($img,$xt+($xb-$xt)/2+1,$yt+2);		
	    $img->SetColor($this->iTableHeaderFrameColor);
	    $img->SetLineWeight($this->iTableHeaderFrameWeight);
	    $img->Rectangle($xt,$yt,$xb,$yb);
	}

	$this->actinfo->Stroke($img,$xt,$yt,$xb,$yb,$this->tableTitle->iShow);


	// Draw the horizontal dividing line		
	$this->dividerh->Stroke($img,$xt,$yb,$img->width-$img->right_margin,$yb);		
		
	// Draw the vertical dividing line
	// We do the width "manually" since we want the line only to grow
	// to the left
	$fancy = $this->divider->iStyle == 'fancy' ;
	if( $fancy ) {
	    $this->divider->iStyle = 'solid';
	}

	$tmp = $this->divider->iWeight;	
	$this->divider->iWeight=1;
	$y = $img->height-$img->bottom_margin;
	for($i=0; $i < $tmp; ++$i ) {
	    $this->divider->Stroke($img,$xb-$i,$yt,$xb-$i,$y);
	}

	// Should we draw "fancy" divider
	if( $fancy ) {
	    $img->SetLineWeight(1);
	    $img->SetColor($this->iTableHeaderFrameColor);
	    $img->Line($xb,$yt,$xb,$y);
	    $img->Line($xb-$tmp+1,$yt,$xb-$tmp+1,$y);
	    $img->SetColor('white');
	    $img->Line($xb-$tmp+2,$yt,$xb-$tmp+2,$y);
	}
    }

    // Main entry point to stroke scale
    function Stroke() {
	if( !$this->IsRangeSet() )
	    JpGraphError::RaiseL(6022);
//("Gantt scale has not been specified.");
	$img=$this->iImg;

	// If minutes are displayed then hour interval must be 1
	if( $this->IsDisplayMinute() && $this->hour->GetIntervall() > 1 ) {
	    JpGraphError::RaiseL(6023);
//('If you display both hour and minutes the hour intervall must be 1 (Otherwise it doesn\' make sense to display minutes).');
	}
		
	// Stroke all headers. As argument we supply the offset from the
	// top which depends on any previous headers
	
	// First find out the height of each header
	$offy=$this->StrokeYears(0,true);
	$offm=$this->StrokeMonths($offy,true);
	$offw=$this->StrokeWeeks($offm,true);
	$offd=$this->StrokeDays($offw,true);
	$offh=$this->StrokeHours($offd,true);
	$offmin=$this->StrokeMinutes($offh,true);


	// ... then we can stroke them in the "backwards order to ensure that
	// the larger scale gridlines is stroked over the smaller scale gridline
	$this->StrokeMinutes($offh);
	$this->StrokeHours($offd);
	$this->StrokeDays($offw);
	$this->StrokeWeeks($offm);		
	$this->StrokeMonths($offy);		
	$this->StrokeYears(0);

	// Now when we now the oaverall size of the scale headers
	// we can stroke the overall table headers
	$this->StrokeTableHeaders($offmin);
		
	// Now we can calculate the correct scaling factor for each vertical position
	$this->iAvailableHeight = $img->height - $img->top_margin - $img->bottom_margin - $offd;		
	$this->iVertHeaderSize = $offmin;
	if( $this->iVertSpacing == -1 )
	    $this->iVertSpacing = $this->iAvailableHeight / $this->iVertLines;
    }	
}


//===================================================
// CLASS GanttConstraint
// Just a structure to store all the values for a constraint
//===================================================
class GanttConstraint {
    public $iConstrainRow;
    public $iConstrainType;
    public $iConstrainColor;
    public $iConstrainArrowSize;
    public $iConstrainArrowType;

//---------------
// CONSTRUCTOR
    function GanttConstraint($aRow,$aType,$aColor,$aArrowSize,$aArrowType){
	$this->iConstrainType = $aType;
	$this->iConstrainRow = $aRow;
	$this->iConstrainColor=$aColor;
	$this->iConstrainArrowSize=$aArrowSize;
	$this->iConstrainArrowType=$aArrowType;
    }
}


//===================================================
// CLASS GanttPlotObject
// The common signature for a Gantt object
//===================================================
class GanttPlotObject {
    public $title,$caption;
    public $csimarea='',$csimtarget='',$csimwintarget='',$csimalt='';
    public $constraints = array();    
    public $iCaptionMargin=5;
    public $iConstrainPos=array();
    protected $iStart="";				// Start date
    public $iVPos=0;					// Vertical position
    protected $iLabelLeftMargin=2;	// Title margin
		
    function GanttPlotObject() {
 	$this->title = new TextProperty();
	$this->title->Align("left","center");
	$this->caption = new TextProperty();
    }

    function GetCSIMArea() {
	return $this->csimarea;
    }

    function SetCSIMTarget($aTarget,$aAlt='',$aWinTarget='') {
	if( !is_string($aTarget) ) {
	    $tv = substr(var_export($aTarget,true),0,40);
	    JpGraphError::RaiseL(6024,$tv);
//('CSIM Target must be specified as a string.'."\nStart of target is:\n$tv");
	}
	if( !is_string($aAlt) ) {
	    $tv = substr(var_export($aAlt,true),0,40);
	    JpGraphError::RaiseL(6025,$tv);
//('CSIM Alt text must be specified as a string.'."\nStart of alt text is:\n$tv");
	}

        $this->csimtarget=$aTarget;
        $this->csimwintarget=$aWinTarget;
        $this->csimalt=$aAlt;
    }
    
    function SetCSIMAlt($aAlt) {
	if( !is_string($aAlt) ) {
	    $tv = substr(var_export($aAlt,true),0,40);
	    JpGraphError::RaiseL(6025,$tv);
//('CSIM Alt text must be specified as a string.'."\nStart of alt text is:\n$tv");
	}
        $this->csimalt=$aAlt;
    }

    function SetConstrain($aRow,$aType,$aColor='black',$aArrowSize=ARROW_S2,$aArrowType=ARROWT_SOLID) {
	$this->constraints[] = new GanttConstraint($aRow, $aType, $aColor, $aArrowSize, $aArrowType);
    }

    function SetConstrainPos($xt,$yt,$xb,$yb) {
	$this->iConstrainPos = array($xt,$yt,$xb,$yb);
    }

    /*
    function GetConstrain() {
	return array($this->iConstrainRow,$this->iConstrainType);
    }
    */
	
    function GetMinDate() {
	return $this->iStart;
    }

    function GetMaxDate() {
	return $this->iStart;
    }
	
    function SetCaptionMargin($aMarg) {
	$this->iCaptionMargin=$aMarg;
    }

    function GetAbsHeight($aImg) {
	return 0; 
    }
	
    function GetLineNbr() {
	return $this->iVPos;
    }

    function SetLabelLeftMargin($aOff) {
	$this->iLabelLeftMargin=$aOff;
    }		

    function StrokeActInfo($aImg,$aScale,$aYPos) {
	$cols=array();
	$aScale->actinfo->GetColStart($aImg,$cols,true);
	$this->title->Stroke($aImg,$cols,$aYPos);		
    }
}

//===================================================
// CLASS Progress
// Holds parameters for the progress indicator 
// displyed within a bar
//===================================================
class Progress {
    public $iProgress=-1;
    public $iPattern=GANTT_SOLID;
    public $iColor="black", $iFillColor='black';
    public $iDensity=98, $iHeight=0.65; 
	
    function Set($aProg) {
	if( $aProg < 0.0 || $aProg > 1.0 )
	    JpGraphError::RaiseL(6027);
//("Progress value must in range [0, 1]");
	$this->iProgress = $aProg;
    }

    function SetPattern($aPattern,$aColor="blue",$aDensity=98) {		
	$this->iPattern = $aPattern;
	$this->iColor = $aColor;
	$this->iDensity = $aDensity;
    }

    function SetFillColor($aColor) {
	$this->iFillColor = $aColor;
    }
	
    function SetHeight($aHeight) {
	$this->iHeight = $aHeight;
    }
}

define('GANTT_HGRID1',0);
define('GANTT_HGRID2',1);

//===================================================
// CLASS HorizontalGridLine
// Responsible for drawinf horizontal gridlines and filled alternatibg rows
//===================================================
class HorizontalGridLine {
    private $iGraph=NULL;
    private $iRowColor1 = '', $iRowColor2 = '';
    private $iShow=false;
    private $line=null;
    private $iStart=0; // 0=from left margin, 1=just along header

    function HorizontalGridLine() {
	$this->line = new LineProperty();
	$this->line->SetColor('gray@0.4');
	$this->line->SetStyle('dashed');
    }
    
    function Show($aShow=true) {
	$this->iShow = $aShow;
    }

    function SetRowFillColor($aColor1,$aColor2='') {
	$this->iRowColor1 = $aColor1;
	$this->iRowColor2 = $aColor2;
    }

    function SetStart($aStart) {
	$this->iStart = $aStart;
    }

    function Stroke($aImg,$aScale) {
	
	if( ! $this->iShow ) return;

	// Get horizontal width of line
	/*
	$limst = $aScale->iStartDate;
	$limen = $aScale->iEndDate;
	$xt = round($aScale->TranslateDate($aScale->iStartDate));
	$xb = round($aScale->TranslateDate($limen)); 
	*/

	if( $this->iStart === 0 ) {
	    $xt = $aImg->left_margin-1;
	}
	else {
	    $xt = round($aScale->TranslateDate($aScale->iStartDate))+1;
	}

	$xb = $aImg->width-$aImg->right_margin;

	$yt = round($aScale->TranslateVertPos(0));
	$yb = round($aScale->TranslateVertPos(1));	    
	$height = $yb - $yt;

	// Loop around for all lines in the chart
	for($i=0; $i < $aScale->iVertLines; ++$i ) {
	    $yb = $yt - $height;
	    $this->line->Stroke($aImg,$xt,$yb,$xb,$yb);
	    if( $this->iRowColor1 !== '' ) {
		if( $i % 2 == 0 ) {
		    $aImg->PushColor($this->iRowColor1);
		    $aImg->FilledRectangle($xt,$yt,$xb,$yb);
		    $aImg->PopColor();
		}
		elseif( $this->iRowColor2 !== '' ) {
		    $aImg->PushColor($this->iRowColor2);
		    $aImg->FilledRectangle($xt,$yt,$xb,$yb);
		    $aImg->PopColor();
		}
	    }
	    $yt = round($aScale->TranslateVertPos($i+1));
	}
	$yb = $yt - $height;
	$this->line->Stroke($aImg,$xt,$yb,$xb,$yb);
    }
}


//===================================================
// CLASS GanttBar
// Responsible for formatting individual gantt bars
//===================================================
class GanttBar extends GanttPlotObject {
    public $progress;
    public $leftMark,$rightMark;
    private $iEnd;
    private $iHeightFactor=0.5;
    private $iFillColor="white",$iFrameColor="black";
    private $iShadow=false,$iShadowColor="darkgray",$iShadowWidth=1,$iShadowFrame="black";
    private $iPattern=GANTT_RDIAG,$iPatternColor="blue",$iPatternDensity=95;
//---------------
// CONSTRUCTOR	
    function GanttBar($aPos,$aLabel,$aStart,$aEnd,$aCaption="",$aHeightFactor=0.6) {
	parent::GanttPlotObject();	
	$this->iStart = $aStart;	
	// Is the end date given as a date or as number of days added to start date?
	if( is_string($aEnd) ) {
	    // If end date has been specified without a time we will asssume
	    // end date is at the end of that date
	    if( strpos($aEnd,':') === false )
		$this->iEnd = strtotime($aEnd)+SECPERDAY-1;
	    else 
		$this->iEnd = $aEnd;
	}
	elseif(is_int($aEnd) || is_float($aEnd) ) 
	    $this->iEnd = strtotime($aStart)+round($aEnd*SECPERDAY);
	$this->iVPos = $aPos;
	$this->iHeightFactor = $aHeightFactor;
	$this->title->Set($aLabel);
	$this->caption = new TextProperty($aCaption);
	$this->caption->Align("left","center");
	$this->leftMark =new PlotMark();
	$this->leftMark->Hide();
	$this->rightMark=new PlotMark();
	$this->rightMark->Hide();
	$this->progress = new Progress();
    }
	
//---------------
// PUBLIC METHODS	
    function SetShadow($aShadow=true,$aColor="gray") {
	$this->iShadow=$aShadow;
	$this->iShadowColor=$aColor;
    }
    
    function GetMaxDate() {
	return $this->iEnd;
    }
	
    function SetHeight($aHeight) {
	$this->iHeightFactor = $aHeight;
    }

    function SetColor($aColor) {
	$this->iFrameColor = $aColor;
    }

    function SetFillColor($aColor) {
	$this->iFillColor = $aColor;
    }

    function GetAbsHeight($aImg) {
	if( is_int($this->iHeightFactor) || $this->leftMark->show || $this->rightMark->show ) {
	    $m=-1;
	    if( is_int($this->iHeightFactor) )
		$m = $this->iHeightFactor;
	    if( $this->leftMark->show ) 
		$m = max($m,$this->leftMark->width*2);
	    if( $this->rightMark->show ) 
		$m = max($m,$this->rightMark->width*2);
	    return $m;
	}
	else
	    return -1;
    }
	
    function SetPattern($aPattern,$aColor="blue",$aDensity=95) {		
	$this->iPattern = $aPattern;
	$this->iPatternColor = $aColor;
	$this->iPatternDensity = $aDensity;
    }

    function Stroke($aImg,$aScale) {
	$factory = new RectPatternFactory();
	$prect = $factory->Create($this->iPattern,$this->iPatternColor);
	$prect->SetDensity($this->iPatternDensity);

	// If height factor is specified as a float between 0,1 then we take it as meaning
	// percetage of the scale width between horizontal line.
	// If it is an integer > 1 we take it to mean the absolute height in pixels
	if( $this->iHeightFactor > -0.0 && $this->iHeightFactor <= 1.1)
	    $vs = $aScale->GetVertSpacing()*$this->iHeightFactor;
	elseif(is_int($this->iHeightFactor) && $this->iHeightFactor>2 && $this->iHeightFactor < 200 )
	    $vs = $this->iHeightFactor;
	else
	    JpGraphError::RaiseL(6028,$this->iHeightFactor);
//("Specified height (".$this->iHeightFactor.") for gantt bar is out of range.");
	
	// Clip date to min max dates to show
	$st = $aScale->NormalizeDate($this->iStart);
	$en = $aScale->NormalizeDate($this->iEnd);
	

	$limst = max($st,$aScale->iStartDate);
	$limen = min($en,$aScale->iEndDate);
			
	$xt = round($aScale->TranslateDate($limst));
	$xb = round($aScale->TranslateDate($limen)); 
	$yt = round($aScale->TranslateVertPos($this->iVPos)-$vs-($aScale->GetVertSpacing()/2-$vs/2));
	$yb = round($aScale->TranslateVertPos($this->iVPos)-($aScale->GetVertSpacing()/2-$vs/2));
	$middle = round($yt+($yb-$yt)/2);
	$this->StrokeActInfo($aImg,$aScale,$middle);

	// CSIM for title
	if( ! empty($this->title->csimtarget) ) {
	    $colwidth = $this->title->GetColWidth($aImg);
	    $colstarts=array();
	    $aScale->actinfo->GetColStart($aImg,$colstarts,true);
	    $n = min(count($colwidth),count($this->title->csimtarget));
	    for( $i=0; $i < $n; ++$i ) {
		$title_xt = $colstarts[$i];
		$title_xb = $title_xt + $colwidth[$i];
		$coords = "$title_xt,$yt,$title_xb,$yt,$title_xb,$yb,$title_xt,$yb";

		if( ! empty($this->title->csimtarget[$i]) ) {
		    $this->csimarea .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->title->csimtarget[$i]."\"";
		    
		    if( ! empty($this->title->csimwintarget[$i]) ) {
			$this->csimarea .= "target=\"".$this->title->csimwintarget[$i]."\" ";
		    }
		    
		    if( ! empty($this->title->csimalt[$i]) ) {
			$tmp = $this->title->csimalt[$i];
			$this->csimarea .= " title=\"$tmp\" alt=\"$tmp\" ";
		    }
		    $this->csimarea .= " />\n";
		}
	    }
	}
	
	// Check if the bar is totally outside the current scale range
	if( $en <  $aScale->iStartDate || $st > $aScale->iEndDate )
		return;
			

	// Remember the positions for the bar
	$this->SetConstrainPos($xt,$yt,$xb,$yb);
		
	$prect->ShowFrame(false);
	$prect->SetBackground($this->iFillColor);
	if( $this->iShadow ) {
	    $aImg->SetColor($this->iFrameColor);
	    $aImg->ShadowRectangle($xt,$yt,$xb,$yb,$this->iFillColor,$this->iShadowWidth,$this->iShadowColor);				
	    $prect->SetPos(new Rectangle($xt+1,$yt+1,$xb-$xt-$this->iShadowWidth-2,$yb-$yt-$this->iShadowWidth-2));				
	    $prect->Stroke($aImg);
	}
	else {	
	    $prect->SetPos(new Rectangle($xt,$yt,$xb-$xt+1,$yb-$yt+1));				
	    $prect->Stroke($aImg);
	    $aImg->SetColor($this->iFrameColor);
	    $aImg->Rectangle($xt,$yt,$xb,$yb);
	}

	// CSIM for bar
	if( ! empty($this->csimtarget) ) {

	    $coords = "$xt,$yt,$xb,$yt,$xb,$yb,$xt,$yb";
	    $this->csimarea .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->csimtarget."\"";
	    
	    if( !empty($this->csimwintarget) ) {
		$this->csimarea .= " target=\"".$this->csimwintarget."\" ";
	    }

	    if( $this->csimalt != '' ) {
		$tmp = $this->csimalt;
		$this->csimarea .= " title=\"$tmp\" alt=\"$tmp\" ";
	    }
	    $this->csimarea .= " />\n";
	}

	// Draw progress bar inside activity bar
	if( $this->progress->iProgress > 0 ) {
		
	    $xtp = $aScale->TranslateDate($st);
	    $xbp = $aScale->TranslateDate($en);
	    $len = ($xbp-$xtp)*$this->progress->iProgress;

	    $endpos = $xtp+$len;
	    if( $endpos > $xt ) {

		// Take away the length of the progress that is not visible (before the start date)
		$len -= ($xt-$xtp); 

		// Is the the progress bar visible after the start date?
		if( $xtp < $xt ) 
		    $xtp = $xt;
		
		// Make sure that the progess bar doesn't extend over the end date
		if( $xtp+$len-1 > $xb )
		    $len = $xb - $xtp ;
		
		$prog = $factory->Create($this->progress->iPattern,$this->progress->iColor);
		$prog->SetDensity($this->progress->iDensity);
		$prog->SetBackground($this->progress->iFillColor);
	    	$barheight = ($yb-$yt+1);
		if( $this->iShadow ) 
		    $barheight -= $this->iShadowWidth;
		$progressheight = floor($barheight*$this->progress->iHeight);
		$marg = ceil(($barheight-$progressheight)/2);
	    	$pos = new Rectangle($xtp,$yt + $marg, $len,$barheight-2*$marg);
		$prog->SetPos($pos);
		$prog->Stroke($aImg);
	    }
	}
	
	// We don't plot the end mark if the bar has been capped
	if( $limst == $st ) {
	    $y = $middle;
	    // We treat the RIGHT and LEFT triangle mark a little bi
	    // special so that these marks are placed right under the
	    // bar.
	    if( $this->leftMark->GetType() == MARK_LEFTTRIANGLE ) {
		$y = $yb ; 
	    }
	    $this->leftMark->Stroke($aImg,$xt,$y);
	}
	if( $limen == $en ) {
	    $y = $middle;
	    // We treat the RIGHT and LEFT triangle mark a little bi
	    // special so that these marks are placed right under the
	    // bar.
	    if( $this->rightMark->GetType() == MARK_RIGHTTRIANGLE ) {
		$y = $yb ; 
	    }
	    $this->rightMark->Stroke($aImg,$xb,$y);
	    
	    $margin = $this->iCaptionMargin;
	    if( $this->rightMark->show ) 
	    	$margin += $this->rightMark->GetWidth();
	    $this->caption->Stroke($aImg,$xb+$margin,$middle);		
	}
    }
}

//===================================================
// CLASS MileStone
// Responsible for formatting individual milestones
//===================================================
class MileStone extends GanttPlotObject {
    public $mark;
	
//---------------
// CONSTRUCTOR	
    function MileStone($aVPos,$aLabel,$aDate,$aCaption="") {
	GanttPlotObject::GanttPlotObject();
	$this->caption->Set($aCaption);
	$this->caption->Align("left","center");
	$this->caption->SetFont(FF_FONT1,FS_BOLD);
	$this->title->Set($aLabel);
	$this->title->SetColor("darkred");
	$this->mark = new PlotMark();
	$this->mark->SetWidth(10);
	$this->mark->SetType(MARK_DIAMOND);
	$this->mark->SetColor("darkred");
	$this->mark->SetFillColor("darkred");
	$this->iVPos = $aVPos;
	$this->iStart = $aDate;
    }
	
//---------------
// PUBLIC METHODS	
	
    function GetAbsHeight($aImg) {
	return max($this->title->GetHeight($aImg),$this->mark->GetWidth());
    }
		
    function Stroke($aImg,$aScale) {
	// Put the mark in the middle at the middle of the day
	$d = $aScale->NormalizeDate($this->iStart)+SECPERDAY/2;
	$x = $aScale->TranslateDate($d);
	$y = $aScale->TranslateVertPos($this->iVPos)-($aScale->GetVertSpacing()/2);

	$this->StrokeActInfo($aImg,$aScale,$y);

	// CSIM for title
	if( ! empty($this->title->csimtarget) ) {
	    
	    $yt = round($y - $this->title->GetHeight($aImg)/2);
	    $yb = round($y + $this->title->GetHeight($aImg)/2);

	    $colwidth = $this->title->GetColWidth($aImg);
	    $colstarts=array();
	    $aScale->actinfo->GetColStart($aImg,$colstarts,true);
	    $n = min(count($colwidth),count($this->title->csimtarget));
	    for( $i=0; $i < $n; ++$i ) {
		$title_xt = $colstarts[$i];
		$title_xb = $title_xt + $colwidth[$i];
		$coords = "$title_xt,$yt,$title_xb,$yt,$title_xb,$yb,$title_xt,$yb";
		
		if( !empty($this->title->csimtarget[$i]) ) {
		    
		    $this->csimarea .= "<area shape=\"poly\" coords=\"$coords\" href=\"".$this->title->csimtarget[$i]."\"";
		    
		    if( !empty($this->title->csimwintarget[$i]) ) {
			$this->csimarea .= "target=\"".$this->title->csimwintarget[$i]."\"";
		    }
		    
		    if( ! empty($this->title->csimalt[$i]) ) {
			$tmp = $this->title->csimalt[$i];
			$this->csimarea .= " title=\"$tmp\" alt=\"$tmp\" ";
		    }
		    $this->csimarea .= " />\n";
		}
	    }
	}

	if( $d <  $aScale->iStartDate || $d > $aScale->iEndDate )
		return;

	// Remember the coordinates for any constrains linking to
	// this milestone
	$w = $this->mark->GetWidth()/2;
	$this->SetConstrainPos($x,round($y-$w),$x,round($y+$w));
	
	// Setup CSIM
	if( $this->csimtarget != '' ) {
	    $this->mark->SetCSIMTarget( $this->csimtarget );
	    $this->mark->SetCSIMAlt( $this->csimalt );
	}
		
	$this->mark->Stroke($aImg,$x,$y);		
	$this->caption->Stroke($aImg,$x+$this->mark->width/2+$this->iCaptionMargin,$y);

	$this->csimarea .= $this->mark->GetCSIMAreas();
    }
}


//===================================================
// CLASS GanttVLine
// Responsible for formatting individual milestones
//===================================================

class TextPropertyBelow extends TextProperty {
    function TextPropertyBelow($aTxt='') {
	parent::TextProperty($aTxt);
    }

    function GetColWidth($aImg,$aMargin=0) {
	// Since we are not stroking the title in the columns
	// but rather under the graph we want this to return 0.
	return array(0);
    }
}

class GanttVLine extends GanttPlotObject {

    private $iLine,$title_margin=3, $iDayOffset=1;
	
//---------------
// CONSTRUCTOR	
    function GanttVLine($aDate,$aTitle="",$aColor="black",$aWeight=3,$aStyle="dashed") {
	GanttPlotObject::GanttPlotObject();
	$this->iLine = new LineProperty();
	$this->iLine->SetColor($aColor);
	$this->iLine->SetWeight($aWeight);
	$this->iLine->SetStyle($aStyle);
	$this->iStart = $aDate;
	$this->title = new TextPropertyBelow();
	$this->title->Set($aTitle);
    }

//---------------
// PUBLIC METHODS	

    function SetDayOffset($aOff=0.5) {
	if( $aOff < 0.0 || $aOff > 1.0 )
	    JpGraphError::RaiseL(6029);
//("Offset for vertical line must be in range [0,1]");
	$this->iDayOffset = $aOff;
    }
	
    function SetTitleMargin($aMarg) {
	$this->title_margin = $aMarg;
    }
	
    function Stroke($aImg,$aScale) {
	$d = $aScale->NormalizeDate($this->iStart);
	if( $d <  $aScale->iStartDate || $d > $aScale->iEndDate )
	    return;	
	if($this->iDayOffset != 0.0)
	    $d += 24*60*60*$this->iDayOffset;	
	$x = $aScale->TranslateDate($d);	
	$y1 = $aScale->iVertHeaderSize+$aImg->top_margin;
	$y2 = $aImg->height - $aImg->bottom_margin;	
	$this->iLine->Stroke($aImg,$x,$y1,$x,$y2);
	$this->title->Align("center","top");
	$this->title->Stroke($aImg,$x,$y2+$this->title_margin);
    }	
}

//===================================================
// CLASS LinkArrow
// Handles the drawing of a an arrow 
//===================================================
class LinkArrow {
    private $ix,$iy;
    private $isizespec = array(
	array(2,3),array(3,5),array(3,8),array(6,15),array(8,22));
    private $iDirection=ARROW_DOWN,$iType=ARROWT_SOLID,$iSize=ARROW_S2;
    private $iColor='black';

    function LinkArrow($x,$y,$aDirection,$aType=ARROWT_SOLID,$aSize=ARROW_S2) {
	$this->iDirection = $aDirection;
	$this->iType = $aType;
	$this->iSize = $aSize;
	$this->ix = $x;
	$this->iy = $y;
    }
    
    function SetColor($aColor) {
	$this->iColor = $aColor;
    }

    function SetSize($aSize) {
	$this->iSize = $aSize;
    }

    function SetType($aType) {
	$this->iType = $aType;
    }

    function Stroke($aImg) {
	list($dx,$dy) = $this->isizespec[$this->iSize];
	$x = $this->ix;
	$y = $this->iy;
	switch ( $this->iDirection ) {
	    case ARROW_DOWN:
		$c = array($x,$y,$x-$dx,$y-$dy,$x+$dx,$y-$dy,$x,$y);
		break;
	    case ARROW_UP:
		$c = array($x,$y,$x-$dx,$y+$dy,$x+$dx,$y+$dy,$x,$y);
		break;
	    case ARROW_LEFT:
		$c = array($x,$y,$x+$dy,$y-$dx,$x+$dy,$y+$dx,$x,$y);
		break;
	    case ARROW_RIGHT:
		$c = array($x,$y,$x-$dy,$y-$dx,$x-$dy,$y+$dx,$x,$y);
		break;
	    default:
		JpGraphError::RaiseL(6030);
//('Unknown arrow direction for link.');
		die();
		break;
	}
	$aImg->SetColor($this->iColor);
	switch( $this->iType ) {
	    case ARROWT_SOLID:
		$aImg->FilledPolygon($c);
		break;
	    case ARROWT_OPEN:
		$aImg->Polygon($c);
		break;
	    default:
		JpGraphError::RaiseL(6031);
//('Unknown arrow type for link.');
		die();
		break;		
	}
    }
}

//===================================================
// CLASS GanttLink
// Handles the drawing of a link line between 2 points
//===================================================

class GanttLink {
    private $ix1,$ix2,$iy1,$iy2;
    private $iPathType=2,$iPathExtend=15;
    private $iColor='black',$iWeight=1;
    private $iArrowSize=ARROW_S2,$iArrowType=ARROWT_SOLID;

    function GanttLink($x1=0,$y1=0,$x2=0,$y2=0) {
	$this->ix1 = $x1;
	$this->ix2 = $x2;
	$this->iy1 = $y1;
	$this->iy2 = $y2;
    }

    function SetPos($x1,$y1,$x2,$y2) {
	$this->ix1 = $x1;
	$this->ix2 = $x2;
	$this->iy1 = $y1;
	$this->iy2 = $y2;
    }

    function SetPath($aPath) {
	$this->iPathType = $aPath;
    }

    function SetColor($aColor) {
	$this->iColor = $aColor;
    }

    function SetArrow($aSize,$aType=ARROWT_SOLID) {
	$this->iArrowSize = $aSize;
	$this->iArrowType = $aType;
    }
    
    function SetWeight($aWeight) {
	$this->iWeight = $aWeight;
    }

    function Stroke($aImg) {
	// The way the path for the arrow is constructed is partly based
	// on some heuristics. This is not an exact science but draws the
	// path in a way that, for me, makes esthetic sence. For example
	// if the start and end activities are very close we make a small
	// detour to endter the target horixontally. If there are more
	// space between axctivities then no suh detour is made and the 
	// target is "hit" directly vertical. I have tried to keep this
	// simple. no doubt this could become almost infinitive complex
	// and have some real AI. Feel free to modify this.
	// This will no-doubt be tweaked as times go by. One design aim
	// is to avoid having the user choose what types of arrow
	// he wants.

	// The arrow is drawn between (x1,y1) to (x2,y2)
	$x1 = $this->ix1 ;
	$x2 = $this->ix2 ;
	$y1 = $this->iy1 ;
	$y2 = $this->iy2 ;

	// Depending on if the target is below or above we have to
	// handle thi different.
	if( $y2 > $y1 ) {
	    $arrowtype = ARROW_DOWN;
	    $midy = round(($y2-$y1)/2+$y1);
	    if( $x2 > $x1 ) {
		switch ( $this->iPathType  ) {
		    case 0:
			$c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			break;
		    case 1:
		    case 2:
		    case 3:
			$c = array($x1,$y1,$x2,$y1,$x2,$y2);
			break;
		    default:
			JpGraphError::RaiseL(6032,$this->iPathType);
//('Internal error: Unknown path type (='.$this->iPathType .') specified for link.');
			exit(1);
			break;
		}
	    }
	    else {
		switch ( $this->iPathType  ) {
		    case 0:
		    case 1:
			$c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			break;
		    case 2:
			// Always extend out horizontally a bit from the first point
			// If we draw a link back in time (end to start) and the bars 
			// are very close we also change the path so it comes in from 
			// the left on the activity
			$c = array($x1,$y1,$x1+$this->iPathExtend,$y1,
				   $x1+$this->iPathExtend,$midy,
				   $x2,$midy,$x2,$y2);
			break;
		    case 3:
			if( $y2-$midy < 6 ) {
			    $c = array($x1,$y1,$x1,$midy,
				       $x2-$this->iPathExtend,$midy,
				       $x2-$this->iPathExtend,$y2,
				       $x2,$y2);
			    $arrowtype = ARROW_RIGHT;
			}
			else {
			    $c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			}
			break;
		    default:
			JpGraphError::RaiseL(6032,$this->iPathType);
//('Internal error: Unknown path type specified for link.');
			exit(1);
			break;
		}
	    }
	    $arrow = new LinkArrow($x2,$y2,$arrowtype);
	}
	else {
	    // Y2 < Y1
	    $arrowtype = ARROW_UP;
	    $midy = round(($y1-$y2)/2+$y2);
	    if( $x2 > $x1 ) {
		switch ( $this->iPathType  ) {
		    case 0:
		    case 1:
			$c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			break;
		    case 3:
			if( $midy-$y2 < 8 ) {
			    $arrowtype = ARROW_RIGHT;
			    $c = array($x1,$y1,$x1,$y2,$x2,$y2);
			}
			else {
			    $c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			}
			break;
		    default:
			JpGraphError::RaiseL(6032,$this->iPathType);
//('Internal error: Unknown path type specified for link.');
			break;
		}
	    }
	    else {
		switch ( $this->iPathType  ) {
		    case 0:
		    case 1:
			$c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			break;
		    case 2:
			// Always extend out horizontally a bit from the first point
			$c = array($x1,$y1,$x1+$this->iPathExtend,$y1,
				   $x1+$this->iPathExtend,$midy,
				   $x2,$midy,$x2,$y2);
			break;
		    case 3:
			if( $midy-$y2 < 16 ) {
			    $arrowtype = ARROW_RIGHT;
			    $c = array($x1,$y1,$x1,$midy,$x2-$this->iPathExtend,$midy,
				       $x2-$this->iPathExtend,$y2,
				       $x2,$y2);
			}
			else {
			    $c = array($x1,$y1,$x1,$midy,$x2,$midy,$x2,$y2);
			}
			break;
		    default:
			JpGraphError::RaiseL(6032,$this->iPathType);
//('Internal error: Unknown path type specified for link.');
			break;
		}
	    }
	    $arrow = new LinkArrow($x2,$y2,$arrowtype);
	}
	$aImg->SetColor($this->iColor);
	$aImg->SetLineWeight($this->iWeight);
	$aImg->Polygon($c);
	$aImg->SetLineWeight(1);
	$arrow->SetColor($this->iColor);
	$arrow->SetSize($this->iArrowSize);
	$arrow->SetType($this->iArrowType);
	$arrow->Stroke($aImg);
    }
}

// <EOF>
?>
