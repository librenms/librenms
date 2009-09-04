<?php
/*=======================================================================
// File: 	JPGRAPH_POLAR.PHP
// Description:	Polar plot extension for JpGraph
// Created: 	2003-02-02
// Ver:		$Id: jpgraph_polar.php 1091 2009-01-18 22:57:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================
*/

require_once ('jpgraph_plotmark.inc.php');
require_once "jpgraph_log.php";


define('POLAR_360',1);
define('POLAR_180',2);

//
// Note. Don't attempt to make sense of this code.
// In order not to have to be able to inherit the scaling code
// from the main graph package we have had to make some "tricks" since
// the original scaling and axis was not designed to do what is
// required here.
// There were two option. 1: Re-implement everything and get a clean design
// and 2: do some "small" trickery and be able to inherit most of
// the functionlity from the main graph package. 
// We choose 2: here in order to save some time.
// 

//--------------------------------------------------------------------------
// class PolarPlot
//--------------------------------------------------------------------------
class PolarPlot {
    public $line_style='solid',$mark;
    public $legendcsimtarget='';
    public $legendcsimalt='';
    public $legend="";
    public $csimtargets=array();	// Array of targets for CSIM
    public $csimareas="";			// Resultant CSIM area tags	
    public $csimalts=null;			// ALT:s for corresponding target
    public $scale=null;
    private $numpoints=0;
    private $iColor='navy',$iFillColor='';
    private $iLineWeight=1;
    private $coord=null;

    function PolarPlot($aData) {
	$n = count($aData);
	if( $n & 1 ) {
	    JpGraphError::RaiseL(17001);
//('Polar plots must have an even number of data point. Each data point is a tuple (angle,radius).');
	}
	$this->numpoints = $n/2;
	$this->coord = $aData;
	$this->mark = new PlotMark();
    }

    function SetWeight($aWeight) {
	$this->iLineWeight = $aWeight;
    }

    function SetColor($aColor){
	$this->iColor = $aColor;
    }

    function SetFillColor($aColor){
	$this->iFillColor = $aColor;
    }

    function Max() {
	$m = $this->coord[1];
	$i=1;
	while( $i < $this->numpoints ) {
	    $m = max($m,$this->coord[2*$i+1]);  
	    ++$i;
	} 
	return $m;
    }
    // Set href targets for CSIM	
    function SetCSIMTargets($aTargets,$aAlts=null) {
	$this->csimtargets=$aTargets;
	$this->csimalts=$aAlts;		
    }
 	
    // Get all created areas
    function GetCSIMareas() {
	return $this->csimareas;
    }	
	
    function SetLegend($aLegend,$aCSIM="",$aCSIMAlt="") {
	$this->legend = $aLegend;
	$this->legendcsimtarget = $aCSIM;
	$this->legendcsimalt = $aCSIMAlt;
    }

    // Private methods

    function Legend($aGraph) {
	$color = $this->iColor ;
	if( $this->legend != "" ) {
	    if( $this->iFillColor!='' ) {
		$color = $this->iFillColor;
		$aGraph->legend->Add($this->legend,$color,$this->mark,0,
				     $this->legendcsimtarget,$this->legendcsimalt);    
	    }
	    else {
		$aGraph->legend->Add($this->legend,$color,$this->mark,$this->line_style,
				     $this->legendcsimtarget,$this->legendcsimalt);    
	    }
	}
    }

    function Stroke($img,$scale) {

	$i=0;
	$p=array();
	$this->csimareas='';
	while($i < $this->numpoints) {
	    list($x1,$y1) = $scale->PTranslate($this->coord[2*$i],$this->coord[2*$i+1]);
	    $p[2*$i] = $x1;
	    $p[2*$i+1] = $y1;
	
	    if( isset($this->csimtargets[$i]) ) {
	        $this->mark->SetCSIMTarget($this->csimtargets[$i]);
	        $this->mark->SetCSIMAlt($this->csimalts[$i]);
		$this->mark->SetCSIMAltVal($this->coord[2*$i], $this->coord[2*$i+1]);
		$this->mark->Stroke($img,$x1,$y1);
		$this->csimareas .= $this->mark->GetCSIMAreas();
	    }
	    else
		$this->mark->Stroke($img,$x1,$y1);

	    ++$i;
	}

	if( $this->iFillColor != '' ) {
	    $img->SetColor($this->iFillColor);
	    $img->FilledPolygon($p);
	}
	$img->SetLineWeight($this->iLineWeight);
	$img->SetColor($this->iColor);
	$img->Polygon($p,$this->iFillColor!='');
    }
}

//--------------------------------------------------------------------------
// class PolarAxis
//--------------------------------------------------------------------------
class PolarAxis extends Axis {
    private $angle_step=15,$angle_color='lightgray',$angle_label_color='black';
    private $angle_fontfam=FF_FONT1,$angle_fontstyle=FS_NORMAL,$angle_fontsize=10;
    private $angle_fontcolor = 'navy';
    private $gridminor_color='lightgray',$gridmajor_color='lightgray';
    private $show_minor_grid = false, $show_major_grid = true ;
    private $show_angle_mark=true, $show_angle_grid=true, $show_angle_label=true;
    private $angle_tick_len=3, $angle_tick_len2=3, $angle_tick_color='black';
    private $show_angle_tick=true;
    private $radius_tick_color='black';

    function PolarAxis($img,$aScale) {
	parent::Axis($img,$aScale);
    }

    function ShowAngleDegreeMark($aFlg=true) {
	$this->show_angle_mark = $aFlg;
    }

    function SetAngleStep($aStep) {
	$this->angle_step=$aStep;
    }

    function HideTicks($aFlg=true,$aAngleFlg=true) {
	parent::HideTicks($aFlg,$aFlg);
	$this->show_angle_tick = !$aAngleFlg;
    }

    function ShowAngleLabel($aFlg=true) {
	$this->show_angle_label = $aFlg;
    }

    function ShowGrid($aMajor=true,$aMinor=false,$aAngle=true) {
	$this->show_minor_grid = $aMinor;
	$this->show_major_grid = $aMajor;
	$this->show_angle_grid = $aAngle ;
    }

    function SetAngleFont($aFontFam,$aFontStyle=FS_NORMAL,$aFontSize=10) {
	$this->angle_fontfam = $aFontFam;
	$this->angle_fontstyle = $aFontStyle;
	$this->angle_fontsize = $aFontSize;
    }

    function SetColor($aColor,$aRadColor='',$aAngleColor='') {
	if( $aAngleColor == '' )
	    $aAngleColor=$aColor;
	parent::SetColor($aColor,$aRadColor);
	$this->angle_fontcolor = $aAngleColor;
    }

    function SetGridColor($aMajorColor,$aMinorColor='',$aAngleColor='') {
	if( $aMinorColor == '' ) 
	    $aMinorColor = $aMajorColor;
	if( $aAngleColor == '' ) 
	    $aAngleColor = $aMajorColor;

	$this->gridminor_color = $aMinorColor;
	$this->gridmajor_color = $aMajorColor;
	$this->angle_color = $aAngleColor;
    }

    function SetTickColors($aRadColor,$aAngleColor='') {
	$this->radius_tick_color = $aRadColor;
	$this->angle_tick_color = $aAngleColor;
    }
    
    // Private methods
    function StrokeGrid($pos) {
	$x = round($this->img->left_margin + $this->img->plotwidth/2);
	$this->scale->ticks->Stroke($this->img,$this->scale,$pos);

	// Stroke the minor arcs 
	$pmin = array();
	$p = $this->scale->ticks->ticks_pos;
	$n = count($p);
	$i = 0;
	$this->img->SetColor($this->gridminor_color);
	while( $i < $n ) {
	    $r = $p[$i]-$x+1;
	    $pmin[]=$r;
	    if( $this->show_minor_grid ) {
		$this->img->Circle($x,$pos,$r);
	    }
	    $i++;
	}
	
	$limit = max($this->img->plotwidth,$this->img->plotheight)*1.4 ;
	while( $r < $limit ) {
	    $off = $r;
	    $i=1;
	    $r = $off + round($p[$i]-$x+1);
	    while( $r < $limit && $i < $n ) {
		$r = $off+$p[$i]-$x;
		$pmin[]=$r;
		if( $this->show_minor_grid ) {
		    $this->img->Circle($x,$pos,$r);
		}
		$i++;
	    }
	}

	// Stroke the major arcs 
	if( $this->show_major_grid ) {
	    // First determine how many minor step on
	    // every major step. We have recorded the minor radius
	    // in pmin and use these values. This is done in order
	    // to avoid rounding errors if we were to recalculate the
	    // different major radius.
	    $pmaj = $this->scale->ticks->maj_ticks_pos;
	    $p = $this->scale->ticks->ticks_pos;
	    if( $this->scale->name == 'lin' ) {
		$step=round(($pmaj[1] - $pmaj[0])/($p[1] - $p[0]));
	    }
	    else {
		$step=9;
	    }
	    $n = round(count($pmin)/$step);
	    $i = 0;
	    $this->img->SetColor($this->gridmajor_color);
	    $limit = max($this->img->plotwidth,$this->img->plotheight)*1.4 ;
	    $off = $r;
	    $i=0;
	    $r = $pmin[$i*$step];
	    while( $r < $limit && $i < $n ) {
		$r = $pmin[$i*$step];
		$this->img->Circle($x,$pos,$r);
		$i++;
	    }
	}

	// Draw angles
	if( $this->show_angle_grid ) {
	    $this->img->SetColor($this->angle_color);
	    $d = max($this->img->plotheight,$this->img->plotwidth)*1.4 ;
	    $a = 0;
	    $p = $this->scale->ticks->ticks_pos;
	    $start_radius = $p[1]-$x;
	    while( $a < 360 ) {
		if( $a == 90 || $a == 270 ) {
		    // Make sure there are no rounding problem with
		    // exactly vertical lines
		    $this->img->Line($x+$start_radius*cos($a/180*M_PI)+1,
				     $pos-$start_radius*sin($a/180*M_PI),
				     $x+$start_radius*cos($a/180*M_PI)+1,
				     $pos-$d*sin($a/180*M_PI));
		    
		}
		else {
		    $this->img->Line($x+$start_radius*cos($a/180*M_PI)+1,
				     $pos-$start_radius*sin($a/180*M_PI),
				     $x+$d*cos($a/180*M_PI),
				     $pos-$d*sin($a/180*M_PI));
		}
		$a += $this->angle_step;
	    }
	}
    }

    function StrokeAngleLabels($pos,$type) {

	if( !$this->show_angle_label ) 
	    return;
	
	$x0 = round($this->img->left_margin+$this->img->plotwidth/2)+1;

	$d = max($this->img->plotwidth,$this->img->plotheight)*1.42;
	$a = $this->angle_step;
	$t = new Text();
	$t->SetColor($this->angle_fontcolor);
	$t->SetFont($this->angle_fontfam,$this->angle_fontstyle,$this->angle_fontsize);
	$xright = $this->img->width - $this->img->right_margin;
	$ytop = $this->img->top_margin;
	$xleft = $this->img->left_margin;
	$ybottom = $this->img->height - $this->img->bottom_margin;
	$ha = 'left';
	$va = 'center';
	$w = $this->img->plotwidth/2;
	$h = $this->img->plotheight/2;
	$xt = $x0; $yt = $pos;
	$margin=5;

	$tl  = $this->angle_tick_len ; // Outer len
	$tl2 = $this->angle_tick_len2 ; // Interior len

	$this->img->SetColor($this->angle_tick_color);
	$rot90 = $this->img->a == 90 ;

	if( $type == POLAR_360 ) {
	    $ca1 = atan($h/$w)/M_PI*180;
	    $ca2 = 180-$ca1;
	    $ca3 = $ca1+180;
	    $ca4 = 360-$ca1;
	    $end = 360;
	    while( $a < $end ) {
		$ca = cos($a/180*M_PI);
		$sa = sin($a/180*M_PI);
		$x = $d*$ca;
		$y = $d*$sa;
		$xt=1000;$yt=1000;
		if( $a <= $ca1 || $a >= $ca4 ) {
		    $yt = $pos - $w * $y/$x;
		    $xt = $xright + $margin;
 		    if( $rot90 ) {
			$ha = 'center';
			$va = 'top';
		    }
		    else {
			$ha = 'left';
			$va = 'center';
		    }
		    $x1=$xright-$tl2; $x2=$xright+$tl;
		    $y1=$y2=$yt;
		}
		elseif( $a > $ca1 && $a < $ca2 ) { 
		    $xt = $x0 + $h * $x/$y;
		    $yt = $ytop - $margin;
 		    if( $rot90 ) {
			$ha = 'left';
			$va = 'center';
		    }
		    else {
			$ha = 'center';
			$va = 'bottom';
		    }
		    $y1=$ytop+$tl2;$y2=$ytop-$tl;
		    $x1=$x2=$xt;
		}
		elseif( $a >= $ca2 && $a <= $ca3 ) { 
		    $yt = $pos + $w * $y/$x;
		    $xt = $xleft - $margin;
 		    if( $rot90 ) {
			$ha = 'center';
			$va = 'bottom';
		    }
		    else {
			$ha = 'right';
			$va = 'center';
		    }
		    $x1=$xleft+$tl2;$x2=$xleft-$tl;
		    $y1=$y2=$yt;
		}
		else { 
		    $xt = $x0 - $h * $x/$y;
		    $yt = $ybottom + $margin;
 		    if( $rot90 ) {
			$ha = 'right';
			$va = 'center';
		    }
		    else {
			$ha = 'center';
			$va = 'top';
		    }
		    $y1=$ybottom-$tl2;$y2=$ybottom+$tl;
		    $x1=$x2=$xt;
		}
		if( $a != 0 && $a != 180 ) {
		    $t->Align($ha,$va);
		    if( $this->show_angle_mark )
			$a .= '°';
		    $t->Set($a);
		    $t->Stroke($this->img,$xt,$yt);   
		    if( $this->show_angle_tick )
			$this->img->Line($x1,$y1,$x2,$y2);
		}
		$a += $this->angle_step;
	    }
	}
	else {
	    // POLAR_HALF
	    $ca1 = atan($h/$w*2)/M_PI*180;
	    $ca2 = 180-$ca1;
	    $end = 180;	    
	    while( $a < $end ) {
		$ca = cos($a/180*M_PI);
		$sa = sin($a/180*M_PI);
		$x = $d*$ca;
		$y = $d*$sa;
		if( $a <= $ca1 ) {
		    $yt = $pos - $w * $y/$x;
		    $xt = $xright + $margin;
 		    if( $rot90 ) {
			$ha = 'center';
			$va = 'top';
		    }
		    else {
			$ha = 'left';
			$va = 'center';
		    }
		    $x1=$xright-$tl2; $x2=$xright+$tl;
		    $y1=$y2=$yt;
		}
		elseif( $a > $ca1 && $a < $ca2 ) { 
		    $xt = $x0 + 2*$h * $x/$y;
		    $yt = $ytop - $margin;
 		    if( $rot90 ) {
			$ha = 'left';
			$va = 'center';
		    }
		    else {
			$ha = 'center';
			$va = 'bottom';
		    }
		    $y1=$ytop+$tl2;$y2=$ytop-$tl;
		    $x1=$x2=$xt;
		}
		elseif( $a >= $ca2 ) { 
		    $yt = $pos + $w * $y/$x;
		    $xt = $xleft - $margin;
 		    if( $rot90 ) {
			$ha = 'center';
			$va = 'bottom';
		    }
		    else {
			$ha = 'right';
			$va = 'center';
		    }
		    $x1=$xleft+$tl2;$x2=$xleft-$tl;
		    $y1=$y2=$yt;
		}
		$t->Align($ha,$va);
		if( $this->show_angle_mark )
		    $a .= '°';
		$t->Set($a);
		$t->Stroke($this->img,$xt,$yt);  
		if( $this->show_angle_tick )
		    $this->img->Line($x1,$y1,$x2,$y2);  
		$a += $this->angle_step;
	    }
	}
    }

    function Stroke($pos,$dummy=true) {

	$this->img->SetLineWeight($this->weight);
	$this->img->SetColor($this->color);		
	$this->img->SetFont($this->font_family,$this->font_style,$this->font_size);
	if( !$this->hide_line ) 
	    $this->img->FilledRectangle($this->img->left_margin,$pos,
		     $this->img->width-$this->img->right_margin,$pos+$this->weight-1);
	$y=$pos+$this->img->GetFontHeight()+$this->title_margin+$this->title->margin;
	if( $this->title_adjust=="high" )
	    $this->title->SetPos($this->img->width-$this->img->right_margin,$y,"right","top");
	elseif( $this->title_adjust=="middle" || $this->title_adjust=="center" ) 
	    $this->title->SetPos(($this->img->width-$this->img->left_margin-
			       $this->img->right_margin)/2+$this->img->left_margin,
			      $y,"center","top");
	elseif($this->title_adjust=="low")
	    $this->title->SetPos($this->img->left_margin,$y,"left","top");
	else {	
	    JpGraphError::RaiseL(17002,$this->title_adjust);
//('Unknown alignment specified for X-axis title. ('.$this->title_adjust.')');
	}

	
	if (!$this->hide_labels) {
	    $this->StrokeLabels($pos,false);
	}
	$this->img->SetColor($this->radius_tick_color);
	$this->scale->ticks->Stroke($this->img,$this->scale,$pos);

	//
	// Mirror the positions for the left side of the scale
        //
	$mid = 2*($this->img->left_margin+$this->img->plotwidth/2);
	$n = count($this->scale->ticks->ticks_pos);
	$i=0;
	while( $i < $n ) {
	    $this->scale->ticks->ticks_pos[$i] = 
		$mid-$this->scale->ticks->ticks_pos[$i] ;
	    ++$i;
	}

	$n = count($this->scale->ticks->maj_ticks_pos);
	$i=0;
	while( $i < $n ) {
	    $this->scale->ticks->maj_ticks_pos[$i] = 
		$mid-$this->scale->ticks->maj_ticks_pos[$i] ;
	    ++$i;
	}
	
	$n = count($this->scale->ticks->maj_ticklabels_pos);
	$i=1;
	while( $i < $n ) {
	    $this->scale->ticks->maj_ticklabels_pos[$i] =
		$mid-$this->scale->ticks->maj_ticklabels_pos[$i] ;
	    ++$i;
	}

	// Draw the left side of the scale
	$n = count($this->scale->ticks->ticks_pos);
	$yu = $pos - $this->scale->ticks->direction*$this->scale->ticks->GetMinTickAbsSize();


	// Minor ticks
	if( ! $this->scale->ticks->supress_minor_tickmarks ) {
	    $i=1;
	    while( $i < $n/2 ) {
		$x = round($this->scale->ticks->ticks_pos[$i]) ;
		$this->img->Line($x,$pos,$x,$yu);
		++$i;
	    }
	}

	$n = count($this->scale->ticks->maj_ticks_pos);
	$yu = $pos - $this->scale->ticks->direction*$this->scale->ticks->GetMajTickAbsSize();


	// Major ticks
	if( ! $this->scale->ticks->supress_tickmarks ) {
	    $i=1;
	    while( $i < $n/2 ) {
		$x = round($this->scale->ticks->maj_ticks_pos[$i]) ;
		$this->img->Line($x,$pos,$x,$yu);
		++$i;
	    }
	}
	if (!$this->hide_labels) {
	    $this->StrokeLabels($pos,false);
	}
	$this->title->Stroke($this->img);	
    }
}

class PolarScale extends LinearScale {
    private $graph;

    function PolarScale($aMax=0,$graph) {
	parent::LinearScale(0,$aMax,'x');
	$this->graph = $graph;
    }

    function _Translate($v) {
	return parent::Translate($v);
    }

    function PTranslate($aAngle,$aRad) {
	
	$m = $this->scale[1];
	$w = $this->graph->img->plotwidth/2;
	$aRad = $aRad/$m*$w;

	$x = cos( $aAngle/180 * M_PI ) * $aRad;
	$y = sin( $aAngle/180 * M_PI ) * $aRad;

	$x += $this->_Translate(0);

	if( $this->graph->iType == POLAR_360 ) {
	    $y = ($this->graph->img->top_margin + $this->graph->img->plotheight/2) - $y;
	}
	else {
	    $y = ($this->graph->img->top_margin + $this->graph->img->plotheight) - $y;
	}
	return array($x,$y);
    }
}

class PolarLogScale extends LogScale {
    private $graph;
    function PolarLogScale($aMax=1,$graph) {
	parent::LogScale(0,$aMax,'x');
	$this->graph = $graph;
	$this->ticks->SetLabelLogType(LOGLABELS_MAGNITUDE);

    }

    function PTranslate($aAngle,$aRad) {

	if( $aRad == 0 ) 
	    $aRad = 1;
	$aRad = log10($aRad);
	$m = $this->scale[1];
	$w = $this->graph->img->plotwidth/2;
	$aRad = $aRad/$m*$w;

	$x = cos( $aAngle/180 * M_PI ) * $aRad;
	$y = sin( $aAngle/180 * M_PI ) * $aRad;

	$x += $w+$this->graph->img->left_margin;//$this->_Translate(0);
	if( $this->graph->iType == POLAR_360 ) {
	    $y = ($this->graph->img->top_margin + $this->graph->img->plotheight/2) - $y;
	}
	else {
	    $y = ($this->graph->img->top_margin + $this->graph->img->plotheight) - $y;
	}
	return array($x,$y);
    }
}

class PolarGraph extends Graph {
    public $scale;
    public $axis;
    public $iType=POLAR_360;
    
    function PolarGraph($aWidth=300,$aHeight=200,$aCachedName="",$aTimeOut=0,$aInline=true) {
	parent::Graph($aWidth,$aHeight,$aCachedName,$aTimeOut,$aInline) ;
	$this->SetDensity(TICKD_DENSE);
	$this->SetBox();
	$this->SetMarginColor('white');
    }

    function SetDensity($aDense) {
	$this->SetTickDensity(TICKD_NORMAL,$aDense);
    }

    function Set90AndMargin($lm=0,$rm=0,$tm=0,$bm=0) {
	$adj = ($this->img->height - $this->img->width)/2;
	$this->SetAngle(90);
	$this->img->SetMargin($lm-$adj,$rm-$adj,$tm+$adj,$bm+$adj);
	$this->img->SetCenter(floor($this->img->width/2),floor($this->img->height/2));
	$this->axis->SetLabelAlign('right','center');
	//JpGraphError::Raise('Set90AndMargin() is not supported for polar graphs.');
    }

    function SetScale($aScale,$rmax=0,$dummy1=1,$dummy2=1,$dummy3=1) {
	if( $aScale == 'lin' ) 
	    $this->scale = new PolarScale($rmax,$this);
	elseif( $aScale == 'log' ) {
	    $this->scale = new PolarLogScale($rmax,$this);
	}
	else {
	    JpGraphError::RaiseL(17004);//('Unknown scale type for polar graph. Must be "lin" or "log"');
	}

	$this->axis = new PolarAxis($this->img,$this->scale);
	$this->SetMargin(40,40,50,40);
    }

    function SetType($aType) {
	$this->iType = $aType;
    }

    function SetPlotSize($w,$h) {
	$this->SetMargin(($this->img->width-$w)/2,($this->img->width-$w)/2,
			 ($this->img->height-$h)/2,($this->img->height-$h)/2);
    }

    // Private methods
    function GetPlotsMax() {
	$n = count($this->plots);
	$m = $this->plots[0]->Max();
	$i=1;
	while($i < $n) {
	    $m = max($this->plots[$i]->Max(),$m);
	    ++$i;
	}
	return $m;
    }

    function Stroke($aStrokeFileName="") {

	// Start by adjusting the margin so that potential titles will fit.
	$this->AdjustMarginsForTitles();
	    
	// If the filename is the predefined value = '_csim_special_'
	// we assume that the call to stroke only needs to do enough
	// to correctly generate the CSIM maps.
	// We use this variable to skip things we don't strictly need
	// to do to generate the image map to improve performance
	// a best we can. Therefor you will see a lot of tests !$_csim in the
	// code below.
	$_csim = ($aStrokeFileName===_CSIM_SPECIALFILE);

	// We need to know if we have stroked the plot in the
	// GetCSIMareas. Otherwise the CSIM hasn't been generated
	// and in the case of GetCSIM called before stroke to generate
	// CSIM without storing an image to disk GetCSIM must call Stroke.
	$this->iHasStroked = true;

	//Check if we should autoscale axis
	if( !$this->scale->IsSpecified() && count($this->plots)>0 ) {
	    $max = $this->GetPlotsMax();
	    $t1 = $this->img->plotwidth;
	    $this->img->plotwidth /= 2;
	    $t2 = $this->img->left_margin;
	    $this->img->left_margin += $this->img->plotwidth+1;
	    $this->scale->AutoScale($this->img,0,$max,
				     $this->img->plotwidth/$this->xtick_factor/2);
	    $this->img->plotwidth = $t1;
	    $this->img->left_margin = $t2;
	}
	else {
	    // The tick calculation will use the user suplied min/max values to determine
	    // the ticks. If auto_ticks is false the exact user specifed min and max
	    // values will be used for the scale. 
	    // If auto_ticks is true then the scale might be slightly adjusted
	    // so that the min and max values falls on an even major step.
	    //$min = 0;
	    $max = $this->scale->scale[1];
	    $t1 = $this->img->plotwidth;
	    $this->img->plotwidth /= 2;
	    $t2 = $this->img->left_margin;
	    $this->img->left_margin += $this->img->plotwidth+1;
	    $this->scale->AutoScale($this->img,0,$max,
				     $this->img->plotwidth/$this->xtick_factor/2);
	    $this->img->plotwidth = $t1;
	    $this->img->left_margin = $t2;
	}

	if( $this->iType ==  POLAR_180 ) 
	    $pos = $this->img->height - $this->img->bottom_margin;
	else
	    $pos = $this->img->plotheight/2 + $this->img->top_margin;


	if( !$_csim ) {
	    $this->StrokePlotArea();
	}

	$this->iDoClipping = true;

	if( $this->iDoClipping ) {
	    $oldimage = $this->img->CloneCanvasH();
	}

	if( !$_csim ) {
	    $this->axis->StrokeGrid($pos);
	}

	// Stroke all plots for Y1 axis
	for($i=0; $i < count($this->plots); ++$i) {
	    $this->plots[$i]->Stroke($this->img,$this->scale);
	}						


	if( $this->iDoClipping ) {
	    // Clipping only supports graphs at 0 and 90 degrees
	    if( $this->img->a == 0  ) {
		$this->img->CopyCanvasH($oldimage,$this->img->img,
					$this->img->left_margin,$this->img->top_margin,
					$this->img->left_margin,$this->img->top_margin,
					$this->img->plotwidth+1,$this->img->plotheight+1);
	    }
	    elseif( $this->img->a == 90 ) {
		$adj = round(($this->img->height - $this->img->width)/2);
		$this->img->CopyCanvasH($oldimage,$this->img->img,
					$this->img->bottom_margin-$adj,$this->img->left_margin+$adj,
					$this->img->bottom_margin-$adj,$this->img->left_margin+$adj,
					$this->img->plotheight,$this->img->plotwidth);
	    }
	    $this->img->Destroy();
	    $this->img->SetCanvasH($oldimage);
	}

	if( !$_csim ) {
	    $this->axis->Stroke($pos);
	    $this->axis->StrokeAngleLabels($pos,$this->iType);
	}

	if( !$_csim ) {
	    $this->StrokePlotBox();
	    $this->footer->Stroke($this->img);

	    // The titles and legends never gets rotated so make sure
	    // that the angle is 0 before stroking them				
	    $aa = $this->img->SetAngle(0);
	    $this->StrokeTitles();
	}

	for($i=0; $i < count($this->plots) ; ++$i ) {
	    $this->plots[$i]->Legend($this);
	}

	$this->legend->Stroke($this->img);		

	if( !$_csim ) {

	    $this->StrokeTexts();	
	    $this->img->SetAngle($aa);	
			
	    // Draw an outline around the image map	
	    if(_JPG_DEBUG)
		$this->DisplayClientSideaImageMapAreas();		
	    
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
}



?>
