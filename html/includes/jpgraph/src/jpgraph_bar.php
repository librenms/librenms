<?php
/*=======================================================================
// File:	JPGRAPH_BAR.PHP
// Description:	Bar plot extension for JpGraph
// Created: 	2001-01-08
// Ver:		$Id: jpgraph_bar.php 1017 2008-07-08 06:09:28Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================
*/

require_once('jpgraph_plotband.php');

// Pattern for Bars
DEFINE('PATTERN_DIAG1',1);
DEFINE('PATTERN_DIAG2',2);
DEFINE('PATTERN_DIAG3',3);
DEFINE('PATTERN_DIAG4',4);
DEFINE('PATTERN_CROSS1',5);
DEFINE('PATTERN_CROSS2',6);
DEFINE('PATTERN_CROSS3',7);
DEFINE('PATTERN_CROSS4',8);
DEFINE('PATTERN_STRIPE1',9);
DEFINE('PATTERN_STRIPE2',10);

//===================================================
// CLASS BarPlot
// Description: Main code to produce a bar plot 
//===================================================
class BarPlot extends Plot {
    public $fill=false,$fill_color="lightblue"; // Default is to fill with light blue
    public $iPattern=-1,$iPatternDensity=80,$iPatternColor='black';
    public $valuepos='top';
    public $grad=false,$grad_style=1;
    public $grad_fromcolor=array(50,50,200),$grad_tocolor=array(255,255,255);
    public $ymin=0;
    protected $width=0.4; // in percent of major ticks
    protected $abswidth=-1; // Width in absolute pixels
    protected $ybase=0; // Bars start at 0 
    protected $align="center";
    protected $bar_shadow=false;
    protected $bar_shadow_color="black";
    protected $bar_shadow_hsize=3,$bar_shadow_vsize=3;	
	
//---------------
// CONSTRUCTOR
    function BarPlot($datay,$datax=false) {
	$this->Plot($datay,$datax);		
	++$this->numpoints;
    }

//---------------
// PUBLIC METHODS	
	
    // Set a drop shadow for the bar (or rather an "up-right" shadow)
    function SetShadow($color="black",$hsize=3,$vsize=3,$show=true) {
	$this->bar_shadow=$show;
	$this->bar_shadow_color=$color;
	$this->bar_shadow_vsize=$vsize;
	$this->bar_shadow_hsize=$hsize;
		
	// Adjust the value margin to compensate for shadow
	$this->value->margin += $vsize;
    }
		
    // DEPRECATED use SetYBase instead
    function SetYMin($aYStartValue) {
	//die("JpGraph Error: Deprecated function SetYMin. Use SetYBase() instead.");    	
	$this->ybase=$aYStartValue;
    }

    // Specify the base value for the bars
    function SetYBase($aYStartValue) {
	$this->ybase=$aYStartValue;
    }
	
    function Legend($graph) {
	if( $this->grad && $this->legend!="" && !$this->fill ) {
	    $color=array($this->grad_fromcolor,$this->grad_tocolor);
	    // In order to differentiate between gradients and cooors specified as an RGB triple
	    $graph->legend->Add($this->legend,$color,"",-$this->grad_style,
				$this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
	}
	elseif( $this->legend!="" && ($this->iPattern > -1 || is_array($this->iPattern)) ) {
	    if( is_array($this->iPattern) ) {
		$p1 = $this->iPattern[0];
		$p2 = $this->iPatternColor[0];
		$p3 = $this->iPatternDensity[0];
	    }
	    else {
		$p1 = $this->iPattern;
		$p2 = $this->iPatternColor;
		$p3 = $this->iPatternDensity;
	    }
	    $color = array($p1,$p2,$p3,$this->fill_color);
	    // A kludge: Too mark that we add a pattern we use a type value of < 100
	    $graph->legend->Add($this->legend,$color,"",-101,
				$this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
	}
	elseif( $this->fill_color && $this->legend!="" ) {
	    if( is_array($this->fill_color) ) {
		$graph->legend->Add($this->legend,$this->fill_color[0],"",0,
				    $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);
	    }
	    else {
		$graph->legend->Add($this->legend,$this->fill_color,"",0,
				    $this->legendcsimtarget,$this->legendcsimalt,$this->legendcsimwintarget);	
	    }
	}
    }

    // Gets called before any axis are stroked
    function PreStrokeAdjust($graph) {
	parent::PreStrokeAdjust($graph);

	// If we are using a log Y-scale we want the base to be at the
	// minimum Y-value unless the user have specifically set some other
	// value than the default.
	if( substr($graph->axtype,-3,3)=="log" && $this->ybase==0 )
	    $this->ybase = $graph->yaxis->scale->GetMinVal();
		
	// For a "text" X-axis scale we will adjust the
	// display of the bars a little bit.
	if( substr($graph->axtype,0,3)=="tex" ) {
	    // Position the ticks between the bars
	    $graph->xaxis->scale->ticks->SetXLabelOffset(0.5,0);

	    // Center the bars 
	    if( $this->abswidth > -1 ) {
		$graph->SetTextScaleAbsCenterOff($this->abswidth);
	    }
	    else {
		if( $this->align == "center" )
		    $graph->SetTextScaleOff(0.5-$this->width/2);
		elseif( $this->align == "right" )
		    $graph->SetTextScaleOff(1-$this->width);			
	    }
	}
	elseif( ($this instanceof AccBarPlot) || ($this instanceof GroupBarPlot) ) { 
	    // We only set an absolute width for linear and int scale
	    // for text scale the width will be set to a fraction of
	    // the majstep width.
	    if( $this->abswidth == -1 ) {
                // Not set
		// set width to a visuable sensible default
		$this->abswidth = $graph->img->plotwidth/(2*$this->numpoints);
	    }
	}
    }

    function Min() {
	$m = parent::Min();
	if( $m[1] >= $this->ybase )
	    $m[1] = $this->ybase;
	return $m;	
    }

    function Max() {
	$m = parent::Max();
	if( $m[1] <= $this->ybase )
	    $m[1] = $this->ybase;
	return $m;	
    }	
	
    // Specify width as fractions of the major stepo size
    function SetWidth($aWidth) {
	if( $aWidth > 1 ) {
	    // Interpret this as absolute width
	    $this->abswidth=$aWidth;
	}
	else
	    $this->width=$aWidth;
    }
	
    // Specify width in absolute pixels. If specified this
    // overrides SetWidth()
    function SetAbsWidth($aWidth) {
	$this->abswidth=$aWidth;
    }
		
    function SetAlign($aAlign) {
	$this->align=$aAlign;
    }
	
    function SetNoFill() {
	$this->grad = false;
	$this->fill_color=false;
	$this->fill=false;
    }
		
    function SetFillColor($aColor) {
	$this->fill = true ;
	$this->fill_color=$aColor;
    }
	
    function SetFillGradient($aFromColor,$aToColor=null,$aStyle=null) {
	$this->grad = true;
	$this->grad_fromcolor = $aFromColor;
	$this->grad_tocolor   = $aToColor;
	$this->grad_style     = $aStyle;
    }
	
    function SetValuePos($aPos) {
	$this->valuepos = $aPos;
    }

    function SetPattern($aPattern, $aColor='black'){
	if( is_array($aPattern) ) {
	    $n = count($aPattern);
	    $this->iPattern = array();
	    $this->iPatternDensity = array();
	    if( is_array($aColor) ) {
		$this->iPatternColor = array();
		if( count($aColor) != $n ) {
		    JpGraphError::RaiseL(2001);//('NUmber of colors is not the same as the number of patterns in BarPlot::SetPattern()');
		}
	    }
	    else
		$this->iPatternColor = $aColor;
	    for( $i=0; $i < $n; ++$i ) {
		$this->_SetPatternHelper($aPattern[$i], $this->iPattern[$i], $this->iPatternDensity[$i]);
		if( is_array($aColor) ) {
		    $this->iPatternColor[$i] = $aColor[$i];
		}
	    }
	}
	else {
	    $this->_SetPatternHelper($aPattern, $this->iPattern, $this->iPatternDensity);
	    $this->iPatternColor = $aColor;
	}
    }

    function _SetPatternHelper($aPattern, &$aPatternValue, &$aDensity){
	switch( $aPattern ) {
	    case PATTERN_DIAG1:
		$aPatternValue= 1;
		$aDensity = 90;
		break;
	    case PATTERN_DIAG2:
		$aPatternValue= 1;
		$aDensity = 75;
		break;
	    case PATTERN_DIAG3:
		$aPatternValue= 2;
		$aDensity = 90;
		break;
	    case PATTERN_DIAG4:
		$aPatternValue= 2;
		$aDensity = 75;
		break;
	    case PATTERN_CROSS1:
		$aPatternValue= 8;
		$aDensity = 90;
		break;
	    case PATTERN_CROSS2:
		$aPatternValue= 8;
		$aDensity = 78;
		break;
	    case PATTERN_CROSS3:
		$aPatternValue= 8;
		$aDensity = 65;
		break;
	    case PATTERN_CROSS4:
		$aPatternValue= 7;
		$aDensity = 90;
		break;
	    case PATTERN_STRIPE1:
		$aPatternValue= 5;
		$aDensity = 90;
		break;
	    case PATTERN_STRIPE2:
		$aPatternValue= 5;
		$aDensity = 75;
		break;
	    default:
		JpGraphError::RaiseL(2002);
//('Unknown pattern specified in call to BarPlot::SetPattern()');
	}
    }

    function Stroke($img,$xscale,$yscale) { 
		
	$numpoints = count($this->coords[0]);
	if( isset($this->coords[1]) ) {
	    if( count($this->coords[1])!=$numpoints )
		JpGraphError::RaiseL(2003,count($this->coords[1]),$numpoints);
//"Number of X and Y points are not equal. Number of X-points:".count($this->coords[1])."Number of Y-points:$numpoints");
	    else
		$exist_x = true;
	}
	else 
	    $exist_x = false;
		
		
	$numbars=count($this->coords[0]);

	// Use GetMinVal() instead of scale[0] directly since in the case
	// of log scale we get a correct value. Log scales will have negative
	// values for values < 1 while still not representing negative numbers.
	if( $yscale->GetMinVal() >= 0 ) 
	    $zp=$yscale->scale_abs[0]; 
	else {
	    $zp=$yscale->Translate(0);
	}

	if( $this->abswidth > -1 ) {
	    $abswidth=$this->abswidth;
	}
	else
	    $abswidth=round($this->width*$xscale->scale_factor,0);

	// Count pontetial pattern array to avoid doing the count for each iteration
	if( is_array($this->iPattern) ) {
	    $np = count($this->iPattern);
	}
			
	$grad = null;
	for($i=0; $i < $numbars; ++$i) {

 	    // If value is NULL, or 0 then don't draw a bar at all
 	    if ($this->coords[0][$i] === null || $this->coords[0][$i] === '' )
		continue;    

	    if( $exist_x ) $x=$this->coords[1][$i];
	    else $x=$i;
			
	    $x=$xscale->Translate($x);

// Comment Note: This confuses the positioning when using acc together with 
// grouped bars. Workaround for fixing #191
/*
	    if( !$xscale->textscale ) {
	    	if($this->align=="center")
		    $x -= $abswidth/2;
		elseif($this->align=="right")
		    $x -= $abswidth;			
	    }
*/
	    // Stroke fill color and fill gradient
	    $pts=array(
		$x,$zp,
		$x,$yscale->Translate($this->coords[0][$i]),
		$x+$abswidth,$yscale->Translate($this->coords[0][$i]),
		$x+$abswidth,$zp);
	    if( $this->grad ) {
		if( $grad === null ) 
		    $grad = new Gradient($img);
		if( is_array($this->grad_fromcolor) ) {
		    // The first argument (grad_fromcolor) can be either an array or a single color. If it is an array
		    // then we have two choices. It can either a) be a single color specified as an RGB triple or it can be
		    // an array to specify both (from, to style) for each individual bar. The way to know the difference is 
		    // to investgate the first element. If this element is an integer [0,255] then we assume it is an RGB 
		    // triple.
		    $ng = count($this->grad_fromcolor);
		    if( $ng === 3 ) {
			if( is_numeric($this->grad_fromcolor[0]) && $this->grad_fromcolor[0] > 0 && $this->grad_fromcolor[0] < 256 ) {
			    // RGB Triple
			    $fromcolor = $this->grad_fromcolor;
			    $tocolor = $this->grad_tocolor;
			    $style = $this->grad_style;
			}
		    }
		    else {
			$fromcolor = $this->grad_fromcolor[$i % $ng][0];
			$tocolor = $this->grad_fromcolor[$i % $ng][1];
			$style = $this->grad_fromcolor[$i % $ng][2];
		    }
		    $grad->FilledRectangle($pts[2],$pts[3],
					   $pts[6],$pts[7],
					   $fromcolor,$tocolor,$style); 
		}
		else {
		    $grad->FilledRectangle($pts[2],$pts[3],
					   $pts[6],$pts[7],
					   $this->grad_fromcolor,$this->grad_tocolor,$this->grad_style); 
		}
	    }
	    elseif( !empty($this->fill_color) ) {
		if(is_array($this->fill_color)) {
		    $img->PushColor($this->fill_color[$i % count($this->fill_color)]);
		} else {
		    $img->PushColor($this->fill_color);
		}
		$img->FilledPolygon($pts);
		$img->PopColor();
	    }
 
			
	    // Remember value of this bar
	    $val=$this->coords[0][$i];

	    if( !empty($val) && !is_numeric($val) ) {
		JpGraphError::RaiseL(2004,$i,$val);
		//'All values for a barplot must be numeric. You have specified value['.$i.'] == \''.$val.'\'');
	    }

	    // Determine the shadow
	    if( $this->bar_shadow && $val != 0) {

		$ssh = $this->bar_shadow_hsize;
		$ssv = $this->bar_shadow_vsize;
		// Create points to create a "upper-right" shadow
		if( $val > 0 ) {
		    $sp[0]=$pts[6];		$sp[1]=$pts[7];
		    $sp[2]=$pts[4];		$sp[3]=$pts[5];
		    $sp[4]=$pts[2];		$sp[5]=$pts[3];
		    $sp[6]=$pts[2]+$ssh;	$sp[7]=$pts[3]-$ssv;
		    $sp[8]=$pts[4]+$ssh;	$sp[9]=$pts[5]-$ssv;
		    $sp[10]=$pts[6]+$ssh;	$sp[11]=$pts[7]-$ssv;
		} 
		elseif( $val < 0 ) {
		    $sp[0]=$pts[4];		$sp[1]=$pts[5];
		    $sp[2]=$pts[6];		$sp[3]=$pts[7];
		    $sp[4]=$pts[0];		$sp[5]=$pts[1];
		    $sp[6]=$pts[0]+$ssh;	$sp[7]=$pts[1]-$ssv;
		    $sp[8]=$pts[6]+$ssh;	$sp[9]=$pts[7]-$ssv;
		    $sp[10]=$pts[4]+$ssh;	$sp[11]=$pts[5]-$ssv;
		}
		if( is_array($this->bar_shadow_color) ) {
		    $numcolors = count($this->bar_shadow_color);
		    if( $numcolors == 0 ) {
			JpGraphError::RaiseL(2005);//('You have specified an empty array for shadow colors in the bar plot.');
		    }
		    $img->PushColor($this->bar_shadow_color[$i % $numcolors]);
		}
		else {
		    $img->PushColor($this->bar_shadow_color);
		}
		$img->FilledPolygon($sp);
		$img->PopColor();
	    }
			
	    // Stroke the pattern
	    if( is_array($this->iPattern) ) {
		$f = new RectPatternFactory();
		if( is_array($this->iPatternColor) ) {
		    $pcolor = $this->iPatternColor[$i % $np];
		}
		else
		    $pcolor = $this->iPatternColor;
		$prect = $f->Create($this->iPattern[$i % $np],$pcolor,1);
		$prect->SetDensity($this->iPatternDensity[$i % $np]);

		if( $val < 0 ) {
		    $rx = $pts[0];
		    $ry = $pts[1];
		}
		else {
		    $rx = $pts[2];
		    $ry = $pts[3];
		}
		$width = abs($pts[4]-$pts[0])+1;
		$height = abs($pts[1]-$pts[3])+1;
		$prect->SetPos(new Rectangle($rx,$ry,$width,$height));
		$prect->Stroke($img);
	    }
	    else {
		if( $this->iPattern > -1 ) {
		    $f = new RectPatternFactory();
		    $prect = $f->Create($this->iPattern,$this->iPatternColor,1);
		    $prect->SetDensity($this->iPatternDensity);
		    if( $val < 0 ) {
			$rx = $pts[0];
			$ry = $pts[1];
		    }
		    else {
			$rx = $pts[2];
			$ry = $pts[3];
		    }
		    $width = abs($pts[4]-$pts[0])+1;
		    $height = abs($pts[1]-$pts[3])+1;
		    $prect->SetPos(new Rectangle($rx,$ry,$width,$height));
		    $prect->Stroke($img);
		}
	    }
	    // Stroke the outline of the bar
	    if( is_array($this->color) )
		$img->SetColor($this->color[$i % count($this->color)]);
	    else
		$img->SetColor($this->color);

	    $pts[] = $pts[0];
	    $pts[] = $pts[1];

	    if( $this->weight > 0 ) {
		$img->SetLineWeight($this->weight);
		$img->Polygon($pts);
	    }
			
	    // Determine how to best position the values of the individual bars
	    $x=$pts[2]+($pts[4]-$pts[2])/2;
	    $this->value->SetMargin(5);

	    if( $this->valuepos=='top' ) {
		$y=$pts[3];
		if( $img->a === 90 ) {
		    if( $val < 0 )
			$this->value->SetAlign('right','center');			
		    else
			$this->value->SetAlign('left','center');
			
		}
		else {
		    if( $val < 0 ) { 
			$this->value->SetMargin(-5);
			$y=$pts[1];
			$this->value->SetAlign('center','bottom');
		    }
		    else {
			$this->value->SetAlign('center','bottom');			
		    }

		}
		$this->value->Stroke($img,$val,$x,$y);
	    }
	    elseif( $this->valuepos=='max' ) {
		$y=$pts[3];
		if( $img->a === 90 ) {
		    if( $val < 0 )
			$this->value->SetAlign('left','center');
		    else
			$this->value->SetAlign('right','center');		    
		}
		else {
		    if( $val < 0 ) {
			$this->value->SetAlign('center','bottom');
		    }
		    else {
			$this->value->SetAlign('center','top');
		    }
		}
		$this->value->SetMargin(-5);
		$this->value->Stroke($img,$val,$x,$y);
	    }
	    elseif( $this->valuepos=='center' ) {
		$y = ($pts[3] + $pts[1])/2;
		$this->value->SetAlign('center','center');
		$this->value->SetMargin(0);
		$this->value->Stroke($img,$val,$x,$y);
	    }
	    elseif( $this->valuepos=='bottom' || $this->valuepos=='min' ) {
		$y=$pts[1];
		if( $img->a === 90 ) {
		    if( $val < 0 )
			$this->value->SetAlign('right','center');
		    else
			$this->value->SetAlign('left','center');		    		    
		}
		$this->value->SetMargin(3);
		$this->value->Stroke($img,$val,$x,$y);
	    }
	    else {
		JpGraphError::RaiseL(2006,$this->valuepos);
		//'Unknown position for values on bars :'.$this->valuepos);
	    }
	    // Create the client side image map
	    $rpts = $img->ArrRotate($pts);		
	    $csimcoord=round($rpts[0]).", ".round($rpts[1]);
	    for( $j=1; $j < 4; ++$j){
		$csimcoord .= ", ".round($rpts[2*$j]).", ".round($rpts[2*$j+1]);
	    }	    	    
	    if( !empty($this->csimtargets[$i]) ) {
		$this->csimareas .= '<area shape="poly" coords="'.$csimcoord.'" ';    	    
		$this->csimareas .= " href=\"".htmlentities($this->csimtargets[$i])."\"";

		if( !empty($this->csimwintargets[$i]) ) {
		    $this->csimareas .= " target=\"".$this->csimwintargets[$i]."\" ";
		}

		$sval='';
		if( !empty($this->csimalts[$i]) ) {
		    $sval=sprintf($this->csimalts[$i],$this->coords[0][$i]);
		    $this->csimareas .= " title=\"$sval\" alt=\"$sval\" ";
		}
		$this->csimareas .= " />\n";
	    }
	}
	return true;
    }
} // Class

//===================================================
// CLASS GroupBarPlot
// Description: Produce grouped bar plots
//===================================================
class GroupBarPlot extends BarPlot {
    private $plots, $nbrplots=0;
//---------------
// CONSTRUCTOR
    function GroupBarPlot($plots) {
	$this->width=0.7;
	$this->plots = $plots;
	$this->nbrplots = count($plots);
	if( $this->nbrplots < 1 ) {
	    JpGraphError::RaiseL(2007);//('Cannot create GroupBarPlot from empty plot array.');
	}
	for($i=0; $i < $this->nbrplots; ++$i ) {
	    if( empty($this->plots[$i]) || !isset($this->plots[$i]) ) {
		JpGraphError::RaiseL(2008,$i);//("Group bar plot element nbr $i is undefined or empty.");
	    }
	}
	$this->numpoints = $plots[0]->numpoints;
	$this->width=0.7;
    }

//---------------
// PUBLIC METHODS	
    function Legend($graph) {
	$n = count($this->plots);
	for($i=0; $i < $n; ++$i) {
	    $c = get_class($this->plots[$i]);
	    if( !($this->plots[$i] instanceof BarPlot) ) {
		JpGraphError::RaiseL(2009,$c);
//('One of the objects submitted to GroupBar is not a BarPlot. Make sure that you create the Group Bar plot from an array of BarPlot or AccBarPlot objects. (Class = '.$c.')');
	    }
	    $this->plots[$i]->DoLegend($graph);
	}
    }
	
    function Min() {
	list($xmin,$ymin) = $this->plots[0]->Min();
	$n = count($this->plots);
	for($i=0; $i < $n; ++$i) {
	    list($xm,$ym) = $this->plots[$i]->Min();
	    $xmin = max($xmin,$xm);
	    $ymin = min($ymin,$ym);
	}
	return array($xmin,$ymin);		
    }
	
    function Max() {
	list($xmax,$ymax) = $this->plots[0]->Max();
	$n = count($this->plots);
	for($i=0; $i < $n; ++$i) {
	    list($xm,$ym) = $this->plots[$i]->Max();
	    $xmax = max($xmax,$xm);
	    $ymax = max($ymax,$ym);
	}
	return array($xmax,$ymax);
    }
	
    function GetCSIMareas() {
	$n = count($this->plots);
	$csimareas='';
	for($i=0; $i < $n; ++$i) {
	    $csimareas .= $this->plots[$i]->csimareas;
	}
	return $csimareas;
    }
	
    // Stroke all the bars next to each other
    function Stroke($img,$xscale,$yscale) { 
	$tmp=$xscale->off;
	$n = count($this->plots);
	$subwidth = $this->width/$this->nbrplots ; 

	for( $i=0; $i < $n; ++$i ) {
	    $this->plots[$i]->ymin=$this->ybase;
	    $this->plots[$i]->SetWidth($subwidth);
	    
	    // If the client have used SetTextTickInterval() then
	    // major_step will be > 1 and the positioning will fail.
	    // If we assume it is always one the positioning will work
	    // fine with a text scale but this will not work with
	    // arbitrary linear scale
	    $xscale->off = $tmp+$i*round($xscale->scale_factor* $subwidth);
	    $this->plots[$i]->Stroke($img,$xscale,$yscale);
	}
	$xscale->off=$tmp;
    }
} // Class

//===================================================
// CLASS AccBarPlot
// Description: Produce accumulated bar plots
//===================================================
class AccBarPlot extends BarPlot {
    private $plots=null,$nbrplots=0;
//---------------
// CONSTRUCTOR
    function AccBarPlot($plots) {
	$this->plots = $plots;
	$this->nbrplots = count($plots);
	if( $this->nbrplots < 1 ) {
	    JpGraphError::RaiseL(2010);//('Cannot create AccBarPlot from empty plot array.');
	}
	for($i=0; $i < $this->nbrplots; ++$i ) {
	    if( empty($this->plots[$i]) || !isset($this->plots[$i]) ) {
		JpGraphError::RaiseL(2011,$i);//("Acc bar plot element nbr $i is undefined or empty.");
	    }
	}

// We can only allow individual plost which do not have specified X-positions
	for($i=0; $i < $this->nbrplots; ++$i ) {
	    if( !empty($this->plots[$i]->coords[1]) ) {
		JpGraphError::RaiseL(2015);
		//'Individual bar plots in an AccBarPlot or GroupBarPlot can not have specified X-positions.');
	    }
	}
	
	$this->numpoints = $plots[0]->numpoints;		
	$this->value = new DisplayValue();
    }

//---------------
// PUBLIC METHODS	
    function Legend($graph) {
	$n = count($this->plots);
	for( $i=$n-1; $i >= 0; --$i ) {
	    $c = get_class($this->plots[$i]);
	    if( !($this->plots[$i] instanceof BarPlot) ) {
		JpGraphError::RaiseL(2012,$c);
//('One of the objects submitted to AccBar is not a BarPlot. Make sure that you create the AccBar plot from an array of BarPlot objects.(Class='.$c.')');
	    }	    
	    $this->plots[$i]->DoLegend($graph);
	}
    }

    function Max() {
	list($xmax) = $this->plots[0]->Max();
	$nmax=0;
	for($i=0; $i < count($this->plots); ++$i) {
	    $n = count($this->plots[$i]->coords[0]);
	    $nmax = max($nmax,$n);
	    list($x) = $this->plots[$i]->Max();
	    $xmax = max($xmax,$x);
	}
	for( $i = 0; $i < $nmax; $i++ ) {
	    // Get y-value for bar $i by adding the
	    // individual bars from all the plots added.
	    // It would be wrong to just add the
	    // individual plots max y-value since that
	    // would in most cases give to large y-value.
	    $y=0;
	    if( !isset($this->plots[0]->coords[0][$i]) ) {
		JpGraphError::RaiseL(2014);
	    }
	    if( $this->plots[0]->coords[0][$i] > 0 )
		$y=$this->plots[0]->coords[0][$i];
	    for( $j = 1; $j < $this->nbrplots; $j++ ) {
		if( !isset($this->plots[$j]->coords[0][$i]) ) {
		    JpGraphError::RaiseL(2014);
		}
		if( $this->plots[$j]->coords[0][$i] > 0 )
		    $y += $this->plots[$j]->coords[0][$i];
	    }
	    $ymax[$i] = $y;
	}
	$ymax = max($ymax);

	// Bar always start at baseline
	if( $ymax <= $this->ybase ) 
	    $ymax = $this->ybase;
	return array($xmax,$ymax);
    }

    function Min() {
	$nmax=0;
	list($xmin,$ysetmin) = $this->plots[0]->Min();
	for($i=0; $i < count($this->plots); ++$i) {
	    $n = count($this->plots[$i]->coords[0]);
	    $nmax = max($nmax,$n);
	    list($x,$y) = $this->plots[$i]->Min();
	    $xmin = Min($xmin,$x);
	    $ysetmin = Min($y,$ysetmin);
	}
	for( $i = 0; $i < $nmax; $i++ ) {
	    // Get y-value for bar $i by adding the
	    // individual bars from all the plots added.
	    // It would be wrong to just add the
	    // individual plots max y-value since that
	    // would in most cases give to large y-value.
	    $y=0;
	    if( $this->plots[0]->coords[0][$i] < 0 )
		$y=$this->plots[0]->coords[0][$i];
	    for( $j = 1; $j < $this->nbrplots; $j++ ) {
		if( $this->plots[$j]->coords[0][$i] < 0 )
		    $y += $this->plots[ $j ]->coords[0][$i];
	    }
	    $ymin[$i] = $y;
	}
	$ymin = Min($ysetmin,Min($ymin));
	// Bar always start at baseline
	if( $ymin >= $this->ybase )
	    $ymin = $this->ybase;
	return array($xmin,$ymin);
    }

    // Stroke acc bar plot
    function Stroke($img,$xscale,$yscale) {
	$pattern=NULL;
	$img->SetLineWeight($this->weight);
	for($i=0; $i < $this->numpoints-1; $i++) {
	    $accy = 0;
	    $accy_neg = 0; 
	    for($j=0; $j < $this->nbrplots; ++$j ) {				
		$img->SetColor($this->plots[$j]->color);

		if ( $this->plots[$j]->coords[0][$i] >= 0) {
		    $yt=$yscale->Translate($this->plots[$j]->coords[0][$i]+$accy);
		    $accyt=$yscale->Translate($accy);
		    $accy+=$this->plots[$j]->coords[0][$i];
		}
		else {
		    //if ( $this->plots[$j]->coords[0][$i] < 0 || $accy_neg < 0 ) {
		    $yt=$yscale->Translate($this->plots[$j]->coords[0][$i]+$accy_neg);
		    $accyt=$yscale->Translate($accy_neg);
		    $accy_neg+=$this->plots[$j]->coords[0][$i];
		}				
				
		$xt=$xscale->Translate($i);

		if( $this->abswidth > -1 )
		    $abswidth=$this->abswidth;
		else
		    $abswidth=round($this->width*$xscale->scale_factor,0);
		
		$pts=array($xt,$accyt,$xt,$yt,$xt+$abswidth,$yt,$xt+$abswidth,$accyt);

		if( $this->bar_shadow ) {
		    $ssh = $this->bar_shadow_hsize;
		    $ssv = $this->bar_shadow_vsize;
		    
		    // We must also differ if we are a positive or negative bar. 
		    if( $j === 0 ) {
			// This gets extra complicated since we have to
			// see all plots to see if we are negative. It could
			// for example be that all plots are 0 until the very
			// last one. We therefore need to save the initial setup
			// for both the negative and positive case

			// In case the final bar is positive
			$sp[0]=$pts[6]+1; $sp[1]=$pts[7];
			$sp[2]=$pts[6]+$ssh; $sp[3]=$pts[7]-$ssv;

			// In case the final bar is negative
			$nsp[0]=$pts[0]; $nsp[1]=$pts[1];
			$nsp[2]=$pts[0]+$ssh; $nsp[3]=$pts[1]-$ssv;
			$nsp[4]=$pts[6]+$ssh; $nsp[5]=$pts[7]-$ssv;
			$nsp[10]=$pts[6]+1; $nsp[11]=$pts[7];
		    }

		    if( $j === $this->nbrplots-1 ) {
			// If this is the last plot of the bar and
			// the total value is larger than 0 then we
			// add the shadow.
			if( is_array($this->bar_shadow_color) ) {
			    $numcolors = count($this->bar_shadow_color);
			    if( $numcolors == 0 ) {
				JpGraphError::RaiseL(2013);//('You have specified an empty array for shadow colors in the bar plot.');
			    }
			    $img->PushColor($this->bar_shadow_color[$i % $numcolors]);
			}
			else {
			    $img->PushColor($this->bar_shadow_color);
			}

			if( $accy > 0 ) {
			    $sp[4]=$pts[4]+$ssh; $sp[5]=$pts[5]-$ssv;
			    $sp[6]=$pts[2]+$ssh; $sp[7]=$pts[3]-$ssv;
			    $sp[8]=$pts[2]; $sp[9]=$pts[3]-1;
			    $sp[10]=$pts[4]+1; $sp[11]=$pts[5];
			    $img->FilledPolygon($sp,4);
			}
			elseif( $accy_neg < 0 ) {
			    $nsp[6]=$pts[4]+$ssh; $nsp[7]=$pts[5]-$ssv;
			    $nsp[8]=$pts[4]+1; $nsp[9]=$pts[5];
			    $img->FilledPolygon($nsp,4);
			}
			$img->PopColor();
		    }
		}


		// If value is NULL or 0, then don't draw a bar at all
		if ($this->plots[$j]->coords[0][$i] == 0 ) continue;

		if( $this->plots[$j]->grad ) {
		    $grad = new Gradient($img);
		    $grad->FilledRectangle(
			$pts[2],$pts[3],
			$pts[6],$pts[7],
			$this->plots[$j]->grad_fromcolor,
			$this->plots[$j]->grad_tocolor,
			$this->plots[$j]->grad_style);				
		} else {
		    if (is_array($this->plots[$j]->fill_color) ) {
			$numcolors = count($this->plots[$j]->fill_color);
			$fillcolor = $this->plots[$j]->fill_color[$i % $numcolors];
			// If the bar is specified to be non filled then the fill color is false
			if( $fillcolor !== false ) 
			    $img->SetColor($this->plots[$j]->fill_color[$i % $numcolors]);
		    }
		    else {
			$fillcolor = $this->plots[$j]->fill_color;
			if( $fillcolor !== false ) 
			    $img->SetColor($this->plots[$j]->fill_color);
		    }
		    if( $fillcolor !== false )
			$img->FilledPolygon($pts);
		    $img->SetColor($this->plots[$j]->color);
		}				  

		// Stroke the pattern
		if( $this->plots[$j]->iPattern > -1 ) {
		    if( $pattern===NULL ) 
			$pattern = new RectPatternFactory();
		
		    $prect = $pattern->Create($this->plots[$j]->iPattern,$this->plots[$j]->iPatternColor,1);
		    $prect->SetDensity($this->plots[$j]->iPatternDensity);
		    if( $this->plots[$j]->coords[0][$i] < 0 ) {
			$rx = $pts[0];
			$ry = $pts[1];
		    }
		    else {
			$rx = $pts[2];
			$ry = $pts[3];
		    }
		    $width = abs($pts[4]-$pts[0])+1;
		    $height = abs($pts[1]-$pts[3])+1;
		    $prect->SetPos(new Rectangle($rx,$ry,$width,$height));
		    $prect->Stroke($img);
		}


		// CSIM array

		if( $i < count($this->plots[$j]->csimtargets) ) {
		    // Create the client side image map
		    $rpts = $img->ArrRotate($pts);		
		    $csimcoord=round($rpts[0]).", ".round($rpts[1]);
		    for( $k=1; $k < 4; ++$k){
			$csimcoord .= ", ".round($rpts[2*$k]).", ".round($rpts[2*$k+1]);
		    }	    	    
		    if( ! empty($this->plots[$j]->csimtargets[$i]) ) {
			$this->csimareas.= '<area shape="poly" coords="'.$csimcoord.'" '; 
			$this->csimareas.= " href=\"".$this->plots[$j]->csimtargets[$i]."\" ";

			if( ! empty($this->plots[$j]->csimwintargets[$i]) ) {
			    $this->csimareas.= " target=\"".$this->plots[$j]->csimwintargets[$i]."\" ";
			}

			$sval='';
			if( !empty($this->plots[$j]->csimalts[$i]) ) {
			    $sval=sprintf($this->plots[$j]->csimalts[$i],$this->plots[$j]->coords[0][$i]);
			    $this->csimareas .= " title=\"$sval\" ";
			}
			$this->csimareas .= " alt=\"$sval\" />\n";				
		    }
		}

		$pts[] = $pts[0];
		$pts[] = $pts[1];
		$img->SetLineWeight($this->plots[$j]->line_weight);
		$img->Polygon($pts);
		$img->SetLineWeight(1);
	    }
		
	    // Draw labels for each acc.bar
	
	    $x=$pts[2]+($pts[4]-$pts[2])/2;
	    if($this->bar_shadow) $x += $ssh;

	    // First stroke the accumulated value for the entire bar
	    // This value is always placed at the top/bottom of the bars
	    if( $accy_neg < 0 ) {
		$y=$yscale->Translate($accy_neg);			
		$this->value->Stroke($img,$accy_neg,$x,$y);
	    }
	    else {
		$y=$yscale->Translate($accy);			
		$this->value->Stroke($img,$accy,$x,$y);
	    }

	    $accy = 0;
	    $accy_neg = 0; 
	    for($j=0; $j < $this->nbrplots; ++$j ) {	

		// We don't print 0 values in an accumulated bar plot
		if( $this->plots[$j]->coords[0][$i] == 0 ) continue;
			
		if ($this->plots[$j]->coords[0][$i] > 0) {
		    $yt=$yscale->Translate($this->plots[$j]->coords[0][$i]+$accy);
		    $accyt=$yscale->Translate($accy);
		    if(  $this->plots[$j]->valuepos=='center' ) {
			$y = $accyt-($accyt-$yt)/2;
		    }
		    elseif( $this->plots[$j]->valuepos=='bottom' ) {
			$y = $accyt;
		    }
		    else { // top or max
			$y = $accyt-($accyt-$yt);
		    }
		    $accy+=$this->plots[$j]->coords[0][$i];
		    if(  $this->plots[$j]->valuepos=='center' ) {
			$this->plots[$j]->value->SetAlign("center","center");
			$this->plots[$j]->value->SetMargin(0);
		    }
		    elseif( $this->plots[$j]->valuepos=='bottom' ) {
			$this->plots[$j]->value->SetAlign('center','bottom');
			$this->plots[$j]->value->SetMargin(2);
		    }
		    else {
			$this->plots[$j]->value->SetAlign('center','top');
			$this->plots[$j]->value->SetMargin(1);
		    }
		} else {
		    $yt=$yscale->Translate($this->plots[$j]->coords[0][$i]+$accy_neg);
		    $accyt=$yscale->Translate($accy_neg);
		    $accy_neg+=$this->plots[$j]->coords[0][$i];
		    if(  $this->plots[$j]->valuepos=='center' ) {
			$y = $accyt-($accyt-$yt)/2;
		    }
		    elseif( $this->plots[$j]->valuepos=='bottom' ) {
			$y = $accyt;
		    }
		    else {
			$y = $accyt-($accyt-$yt);
		    }
		    if(  $this->plots[$j]->valuepos=='center' ) {
			$this->plots[$j]->value->SetAlign("center","center");
			$this->plots[$j]->value->SetMargin(0);
		    }
		    elseif( $this->plots[$j]->valuepos=='bottom' ) {
			$this->plots[$j]->value->SetAlign('center',$j==0 ? 'bottom':'top');
			$this->plots[$j]->value->SetMargin(-2);
		    }
		    else {
			$this->plots[$j]->value->SetAlign('center','bottom');
			$this->plots[$j]->value->SetMargin(-1);
		    }
		}	
		$this->plots[$j]->value->Stroke($img,$this->plots[$j]->coords[0][$i],$x,$y);
	    }

	}
	return true;
    }
} // Class

/* EOF */
?>
