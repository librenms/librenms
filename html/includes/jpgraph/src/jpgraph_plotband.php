<?php
//=======================================================================
// File:	JPGRAPH_PLOTBAND.PHP
// Description:	PHP4 Graph Plotting library. Extension module.
// Created: 	2004-02-18
// Ver:		$Id: jpgraph_plotband.php 1091 2009-01-18 22:57:40Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

// Constants for types of static bands in plot area
define("BAND_RDIAG",1);	// Right diagonal lines
define("BAND_LDIAG",2); // Left diagonal lines
define("BAND_SOLID",3); // Solid one color
define("BAND_VLINE",4); // Vertical lines
define("BAND_HLINE",5);  // Horizontal lines
define("BAND_3DPLANE",6);  // "3D" Plane
define("BAND_HVCROSS",7);  // Vertical/Hor crosses
define("BAND_DIAGCROSS",8); // Diagonal crosses


// Utility class to hold coordinates for a rectangle
class Rectangle {
    public $x,$y,$w,$h;
    public $xe, $ye;
    function Rectangle($aX,$aY,$aWidth,$aHeight) {
	$this->x=$aX;
	$this->y=$aY;
	$this->w=$aWidth;
	$this->h=$aHeight;
	$this->xe=$aX+$aWidth-1;
	$this->ye=$aY+$aHeight-1;
    }
}

//=====================================================================
// Class RectPattern
// Base class for pattern hierarchi that is used to display patterned
// bands on the graph. Any subclass that doesn't override Stroke()
// must at least implement method DoPattern($aImg) which is responsible
// for drawing the pattern onto the graph.
//=====================================================================
class RectPattern {
    protected $color;
    protected $weight;
    protected $rect=null;
    protected $doframe=true;
    protected $linespacing;	// Line spacing in pixels
    protected $iBackgroundColor=-1;  // Default is no background fill
	
    function RectPattern($aColor,$aWeight=1) {
	$this->color = $aColor;
	$this->weight = $aWeight;		
    }

    function SetBackground($aBackgroundColor) {
	$this->iBackgroundColor=$aBackgroundColor;
    }

    function SetPos($aRect) {
	$this->rect = $aRect;
    }
	
    function ShowFrame($aShow=true) {
	$this->doframe=$aShow;
    }

    function SetDensity($aDens) {
	if( $aDens < 1 || $aDens > 100 )
	    JpGraphError::RaiseL(16001,$aDens);
//(" Desity for pattern must be between 1 and 100. (You tried $aDens)");
	// 1% corresponds to linespacing=50
	// 100 % corresponds to linespacing 1
	$this->linespacing = floor(((100-$aDens)/100.0)*50)+1;

    }

    function Stroke($aImg) {
	if( $this->rect == null )
	    JpGraphError::RaiseL(16002);
//(" No positions specified for pattern.");

	if( !(is_numeric($this->iBackgroundColor) && $this->iBackgroundColor==-1) ) {
	    $aImg->SetColor($this->iBackgroundColor);
	    $aImg->FilledRectangle($this->rect->x,$this->rect->y,$this->rect->xe,$this->rect->ye); 
	}

	$aImg->SetColor($this->color);
	$aImg->SetLineWeight($this->weight);

	// Virtual function implemented by subclass
	$this->DoPattern($aImg);

	// Frame around the pattern area
	if( $this->doframe ) 
	    $aImg->Rectangle($this->rect->x,$this->rect->y,$this->rect->xe,$this->rect->ye);
    }

}


//=====================================================================
// Class RectPatternSolid
// Implements a solid band
//=====================================================================
class RectPatternSolid extends RectPattern {

    function RectPatternSolid($aColor="black",$aWeight=1) {
	parent::RectPattern($aColor,$aWeight);
    }

    function DoPattern($aImg) {
	$aImg->SetColor($this->color);
	$aImg->FilledRectangle($this->rect->x,$this->rect->y,
			       $this->rect->xe,$this->rect->ye);
    }
}

//=====================================================================
// Class RectPatternHor
// Implements horizontal line pattern
//=====================================================================
class RectPatternHor extends RectPattern {
		
    function RectPatternHor($aColor="black",$aWeight=1,$aLineSpacing=7) {
	parent::RectPattern($aColor,$aWeight);
	$this->linespacing = $aLineSpacing;
    }
		
    function DoPattern($aImg) {
	$x0 = $this->rect->x;		
	$x1 = $this->rect->xe;
	$y = $this->rect->y;
	while( $y < $this->rect->ye ) {
	    $aImg->Line($x0,$y,$x1,$y);
	    $y += $this->linespacing;
	}
    }
}

//=====================================================================
// Class RectPatternVert
// Implements vertical line pattern
//=====================================================================
class RectPatternVert extends RectPattern {
		
    function RectPatternVert($aColor="black",$aWeight=1,$aLineSpacing=7) {
	parent::RectPattern($aColor,$aWeight);
	$this->linespacing = $aLineSpacing;
    }

    //--------------------
    // Private methods
    //
    function DoPattern($aImg) {
	$x = $this->rect->x;		
	$y0 = $this->rect->y;
	$y1 = $this->rect->ye;
	while( $x < $this->rect->xe ) {
	    $aImg->Line($x,$y0,$x,$y1);
	    $x += $this->linespacing;
	}
    }
}


//=====================================================================
// Class RectPatternRDiag
// Implements right diagonal pattern
//=====================================================================
class RectPatternRDiag extends RectPattern {
		
    function RectPatternRDiag($aColor="black",$aWeight=1,$aLineSpacing=12) {
	parent::RectPattern($aColor,$aWeight);
	$this->linespacing = $aLineSpacing;
    }

    function DoPattern($aImg) {
	//  --------------------
	//  | /   /   /   /   /|
	//  |/   /   /   /   / |
	//  |   /   /   /   /  |
	//  --------------------
	$xe = $this->rect->xe;
	$ye = $this->rect->ye;
	$x0 = $this->rect->x + round($this->linespacing/2); 
	$y0 = $this->rect->y;
	$x1 = $this->rect->x; 
	$y1 = $this->rect->y + round($this->linespacing/2);

	while($x0<=$xe && $y1<=$ye) {
	    $aImg->Line($x0,$y0,$x1,$y1);
	    $x0 += $this->linespacing;
	    $y1 += $this->linespacing;
	}

	if( $xe-$x1 > $ye-$y0 ) { 
	    // Width larger than height
	    $x1 = $this->rect->x + ($y1-$ye);
	    $y1 = $ye; 
	    $y0 = $this->rect->y; 
	    while( $x0 <= $xe ) {
		$aImg->Line($x0,$y0,$x1,$y1);
		$x0 += $this->linespacing;
		$x1 += $this->linespacing;
	    }
	    
	    $y0=$this->rect->y + ($x0-$xe);
	    $x0=$xe;
	}
	else {
	    // Height larger than width
	    $diff = $x0-$xe;
	    $y0 = $diff+$this->rect->y;
	    $x0 = $xe;
	    $x1 = $this->rect->x;
	    while( $y1 <= $ye ) {
		$aImg->Line($x0,$y0,$x1,$y1);
		$y1 += $this->linespacing;
		$y0 += $this->linespacing;
	    }
	    
	    $diff = $y1-$ye;
	    $y1 = $ye;
	    $x1 = $diff + $this->rect->x;
	}

	while( $y0 <= $ye ) {
	    $aImg->Line($x0,$y0,$x1,$y1);
	    $y0 += $this->linespacing;		
	    $x1 += $this->linespacing;
	}
    }
}
 
//=====================================================================
// Class RectPatternLDiag
// Implements left diagonal pattern
//=====================================================================
class RectPatternLDiag extends RectPattern {
		
    function RectPatternLDiag($aColor="black",$aWeight=1,$aLineSpacing=12) {
	$this->linespacing = $aLineSpacing;
	parent::RectPattern($aColor,$aWeight);
    }

    function DoPattern($aImg) {
	//  --------------------
	//  |\   \   \   \   \ |
	//  | \   \   \   \   \|
	//  |  \   \   \   \   |
	//  |------------------|
	$xe = $this->rect->xe;
	$ye = $this->rect->ye;
	$x0 = $this->rect->x + round($this->linespacing/2); 
	$y0 = $this->rect->ye;
	$x1 = $this->rect->x; 
	$y1 = $this->rect->ye - round($this->linespacing/2);

	while($x0<=$xe && $y1>=$this->rect->y) {
	    $aImg->Line($x0,$y0,$x1,$y1);
	    $x0 += $this->linespacing;
	    $y1 -= $this->linespacing;
	}
	if( $xe-$x1 > $ye-$this->rect->y ) { 
	    // Width larger than height
	    $x1 = $this->rect->x + ($this->rect->y-$y1);
	    $y0=$ye; $y1=$this->rect->y; 
	    while( $x0 <= $xe ) {
		$aImg->Line($x0,$y0,$x1,$y1);
		$x0 += $this->linespacing;
		$x1 += $this->linespacing;
	    }
	    
	    $y0=$this->rect->ye - ($x0-$xe);
	    $x0=$xe;
	}
	else {
	    // Height larger than width
	    $diff = $x0-$xe;
	    $y0 = $ye-$diff;
	    $x0 = $xe;
	    while( $y1 >= $this->rect->y ) {
		$aImg->Line($x0,$y0,$x1,$y1);
		$y0 -= $this->linespacing;
		$y1 -= $this->linespacing;
	    }	    
	    $diff = $this->rect->y - $y1;
	    $x1 = $this->rect->x + $diff;
	    $y1 = $this->rect->y;
	}
	while( $y0 >= $this->rect->y ) {
	    $aImg->Line($x0,$y0,$x1,$y1);
	    $y0 -= $this->linespacing;
	    $x1 += $this->linespacing;
	}
    }
}

//=====================================================================
// Class RectPattern3DPlane
// Implements "3D" plane pattern
//=====================================================================
class RectPattern3DPlane extends RectPattern {
    private $alpha=50;  // Parameter that specifies the distance
    // to "simulated" horizon in pixel from the
    // top of the band. Specifies how fast the lines
    // converge.

    function RectPattern3DPlane($aColor="black",$aWeight=1) {
	parent::RectPattern($aColor,$aWeight);
	$this->SetDensity(10);  // Slightly larger default
    }

    function SetHorizon($aHorizon) {
	$this->alpha=$aHorizon;
    }
	
    function DoPattern($aImg) {
	// "Fake" a nice 3D grid-effect. 
	$x0 = $this->rect->x + $this->rect->w/2;
	$y0 = $this->rect->y;
	$x1 = $x0;
	$y1 = $this->rect->ye;
	$x0_right = $x0;
	$x1_right = $x1;

	// BTW "apa" means monkey in Swedish but is really a shortform for
	// "alpha+a" which was the labels I used on paper when I derived the
	// geometric to get the 3D perspective right. 
	// $apa is the height of the bounding rectangle plus the distance to the
	// artifical horizon (alpha)
	$apa = $this->rect->h + $this->alpha;

	// Three cases and three loops
	// 1) The endpoint of the line ends on the bottom line
	// 2) The endpoint ends on the side
	// 3) Horizontal lines

	// Endpoint falls on bottom line
	$middle=$this->rect->x + $this->rect->w/2;
	$dist=$this->linespacing;
	$factor=$this->alpha /($apa);
	while($x1>$this->rect->x) {
	    $aImg->Line($x0,$y0,$x1,$y1);
	    $aImg->Line($x0_right,$y0,$x1_right,$y1);
	    $x1 = $middle - $dist;
	    $x0 = $middle - $dist * $factor;
	    $x1_right = $middle + $dist;
	    $x0_right =  $middle + $dist * $factor;
	    $dist += $this->linespacing;
	}

	// Endpoint falls on sides
	$dist -= $this->linespacing;
	$d=$this->rect->w/2;
	$c = $apa - $d*$apa/$dist;
	while( $x0>$this->rect->x ) {
	    $aImg->Line($x0,$y0,$this->rect->x,$this->rect->ye-$c);
	    $aImg->Line($x0_right,$y0,$this->rect->xe,$this->rect->ye-$c);
	    $dist += $this->linespacing;			
	    $x0 = $middle - $dist * $factor;
	    $x1 = $middle - $dist;
	    $x0_right =  $middle + $dist * $factor;			
	    $c = $apa - $d*$apa/$dist;
	}		
		
	// Horizontal lines
	// They need some serious consideration since they are a function
	// of perspective depth (alpha) and density (linespacing)
	$x0=$this->rect->x;
	$x1=$this->rect->xe;
	$y=$this->rect->ye;
		
	// The first line is drawn directly. Makes the loop below slightly
	// more readable.
	$aImg->Line($x0,$y,$x1,$y);
	$hls = $this->linespacing;
		
	// A correction factor for vertical "brick" line spacing to account for
	// a) the difference in number of pixels hor vs vert
	// b) visual apperance to make the first layer of "bricks" look more
	// square.
	$vls = $this->linespacing*0.6;
		
	$ds = $hls*($apa-$vls)/$apa;
	// Get the slope for the "perspective line" going from bottom right
	// corner to top left corner of the "first" brick.
		
	// Uncomment the following lines if you want to get a visual understanding
	// of what this helpline does. BTW this mimics the way you would get the
	// perspective right when drawing on paper.
	/*
	  $x0 = $middle;
	  $y0 = $this->rect->ye;
	  $len=floor(($this->rect->ye-$this->rect->y)/$vls);
	  $x1 = $middle+round($len*$ds);
	  $y1 = $this->rect->ye-$len*$vls;
	  $aImg->PushColor("red");
	  $aImg->Line($x0,$y0,$x1,$y1);
	  $aImg->PopColor();
	*/
		
	$y -= $vls;		
	$k=($this->rect->ye-($this->rect->ye-$vls))/($middle-($middle-$ds));
	$dist = $hls;
	while( $y>$this->rect->y ) {
	    $aImg->Line($this->rect->x,$y,$this->rect->xe,$y);
	    $adj = $k*$dist/(1+$dist*$k/$apa);
	    if( $adj < 2 ) $adj=1;
	    $y = $this->rect->ye - round($adj);
	    $dist += $hls;
	}
    }
}

//=====================================================================
// Class RectPatternCross
// Vert/Hor crosses
//=====================================================================
class RectPatternCross extends RectPattern {
    private $vert=null;
    private $hor=null;
    function RectPatternCross($aColor="black",$aWeight=1) {
	parent::RectPattern($aColor,$aWeight);
	$this->vert = new RectPatternVert($aColor,$aWeight);
	$this->hor  = new RectPatternHor($aColor,$aWeight);
    }

    function SetOrder($aDepth) {
	$this->vert->SetOrder($aDepth);
	$this->hor->SetOrder($aDepth);
    }

    function SetPos($aRect) {
	parent::SetPos($aRect);
	$this->vert->SetPos($aRect);
	$this->hor->SetPos($aRect);
    }

    function SetDensity($aDens) {
	$this->vert->SetDensity($aDens);
	$this->hor->SetDensity($aDens);
    }

    function DoPattern($aImg) {
	$this->vert->DoPattern($aImg);
	$this->hor->DoPattern($aImg);
    }
}

//=====================================================================
// Class RectPatternDiagCross
// Vert/Hor crosses
//=====================================================================

class RectPatternDiagCross extends RectPattern {
    private $left=null;
    private $right=null;
    function RectPatternDiagCross($aColor="black",$aWeight=1) {
	parent::RectPattern($aColor,$aWeight);
	$this->right = new RectPatternRDiag($aColor,$aWeight);
	$this->left  = new RectPatternLDiag($aColor,$aWeight);
    }

    function SetOrder($aDepth) {
	$this->left->SetOrder($aDepth);
	$this->right->SetOrder($aDepth);
    }

    function SetPos($aRect) {
	parent::SetPos($aRect);
	$this->left->SetPos($aRect);
	$this->right->SetPos($aRect);
    }

    function SetDensity($aDens) {
	$this->left->SetDensity($aDens);
	$this->right->SetDensity($aDens);
    }

    function DoPattern($aImg) {
	$this->left->DoPattern($aImg);
	$this->right->DoPattern($aImg);
    }

}

//=====================================================================
// Class RectPatternFactory
// Factory class for rectangular pattern 
//=====================================================================
class RectPatternFactory {
    function RectPatternFactory() {
	// Empty
    }
    function Create($aPattern,$aColor,$aWeight=1) {
	switch($aPattern) {
	    case BAND_RDIAG:
		$obj =  new RectPatternRDiag($aColor,$aWeight);
		break;
	    case BAND_LDIAG:
		$obj =  new RectPatternLDiag($aColor,$aWeight);
		break;
	    case BAND_SOLID:
		$obj =  new RectPatternSolid($aColor,$aWeight);
		break;
	    case BAND_VLINE:
		$obj =  new RectPatternVert($aColor,$aWeight);
		break;
	    case BAND_HLINE:
		$obj =  new RectPatternHor($aColor,$aWeight);
		break;
	    case BAND_3DPLANE:
		$obj =  new RectPattern3DPlane($aColor,$aWeight);
		break;
	    case BAND_HVCROSS:
		$obj =  new RectPatternCross($aColor,$aWeight);
		break;
	    case BAND_DIAGCROSS:
		$obj =  new RectPatternDiagCross($aColor,$aWeight);
		break;
	    default:
		JpGraphError::RaiseL(16003,$aPattern);
//(" Unknown pattern specification ($aPattern)");
	}
	return $obj;
    }
}


//=====================================================================
// Class PlotBand
// Factory class which is used by the client.
// It is responsible for factoring the corresponding pattern
// concrete class.
//=====================================================================
class PlotBand {
    public $depth; // Determine if band should be over or under the plots
    private $prect=null;
    private $dir, $min, $max;

    function PlotBand($aDir,$aPattern,$aMin,$aMax,$aColor="black",$aWeight=1,$aDepth=DEPTH_BACK) {
	$f =  new RectPatternFactory();
	$this->prect = $f->Create($aPattern,$aColor,$aWeight);
	if( is_numeric($aMin) && is_numeric($aMax) && ($aMin > $aMax) ) 
	    JpGraphError::RaiseL(16004);
//('Min value for plotband is larger than specified max value. Please correct.');
	$this->dir = $aDir;
	$this->min = $aMin;
	$this->max = $aMax;
	$this->depth=$aDepth;
    }
	
    // Set position. aRect contains absolute image coordinates
    function SetPos($aRect) {
	assert( $this->prect != null ) ;
	$this->prect->SetPos($aRect);
    }
	
    function ShowFrame($aFlag=true) {
	$this->prect->ShowFrame($aFlag);
    }

    // Set z-order. In front of pplot or in the back
    function SetOrder($aDepth) {
	$this->depth=$aDepth;
    }
	
    function SetDensity($aDens) {
	$this->prect->SetDensity($aDens);
    }
	
    function GetDir() {
	return $this->dir;
    }
	
    function GetMin() {
	return $this->min;
    }
	
    function GetMax() {
	return $this->max;
    }

    function PreStrokeAdjust($aGraph) {
	// Nothing to do
    }
	
    // Display band
    function Stroke($aImg,$aXScale,$aYScale) {
	assert( $this->prect != null ) ;
	if( $this->dir == HORIZONTAL ) {
	    if( $this->min === 'min' ) $this->min = $aYScale->GetMinVal();
	    if( $this->max === 'max' ) $this->max = $aYScale->GetMaxVal();

            // Only draw the bar if it actually appears in the range
            if ($this->min < $aYScale->GetMaxVal() && $this->max > $aYScale->GetMinVal()) {
	    
	    // Trucate to limit of axis
	    $this->min = max($this->min, $aYScale->GetMinVal());
	    $this->max = min($this->max, $aYScale->GetMaxVal());

	    $x=$aXScale->scale_abs[0];
	    $y=$aYScale->Translate($this->max);
	    $width=$aXScale->scale_abs[1]-$aXScale->scale_abs[0]+1;
	    $height=abs($y-$aYScale->Translate($this->min))+1;
	    $this->prect->SetPos(new Rectangle($x,$y,$width,$height));
	    $this->prect->Stroke($aImg);
            }
	}
	else {	// VERTICAL
	    if( $this->min === 'min' ) $this->min = $aXScale->GetMinVal();
	    if( $this->max === 'max' ) $this->max = $aXScale->GetMaxVal();
            
            // Only draw the bar if it actually appears in the range
	    if ($this->min < $aXScale->GetMaxVal() && $this->max > $aXScale->GetMinVal()) {
	    
	    // Trucate to limit of axis
	    $this->min = max($this->min, $aXScale->GetMinVal());
	    $this->max = min($this->max, $aXScale->GetMaxVal());

	    $y=$aYScale->scale_abs[1];
	    $x=$aXScale->Translate($this->min);
	    $height=abs($aYScale->scale_abs[1]-$aYScale->scale_abs[0]);
	    $width=abs($x-$aXScale->Translate($this->max));
	    $this->prect->SetPos(new Rectangle($x,$y,$width,$height));
	    $this->prect->Stroke($aImg);
            }
	}
    }
}


?>
