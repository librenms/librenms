<?php
//=======================================================================
// File:	JPGRAPH_IMGTRANS.PHP
// Description:	Extension for JpGraph to do some simple img transformations
// Created: 	2003-09-06
// Ver:		$Id: jpgraph_imgtrans.php 781 2006-10-08 08:07:47Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================

//------------------------------------------------------------------------
// Class ImgTrans
// Perform some simple image transformations. 
//------------------------------------------------------------------------
class ImgTrans {
    private $gdImg=null;

    function ImgTrans($aGdImg) {
	// Constructor
	$this->gdImg = $aGdImg;
    }

    // --------------------------------------------------------------------
    // _TransVert3D() and _TransHor3D() are helper methods to 
    // Skew3D(). 
    // --------------------------------------------------------------------
    function _TransVert3D($aGdImg,$aHorizon=100,$aSkewDist=120,$aDir=SKEW3D_DOWN,$aMinSize=true,$aFillColor='#FFFFFF',$aQuality=false,$aBorder=false,$aHorizonPos=0.5) {


	// Parameter check
	if( $aHorizonPos < 0 || $aHorizonPos > 1.0 ) {
	    JpGraphError::RaiseL(9001);
//("Value for image transformation out of bounds.\nVanishing point on horizon must be specified as a value between 0 and 1.");
	}

	$w = imagesx($aGdImg);
	$h = imagesy($aGdImg);

	// Create new image
	$ww = $w;
	if( $aMinSize ) 
	    $hh = ceil($h * $aHorizon / ($aSkewDist+$h));
	else 
	    $hh = $h;
	
	$newgdh = imagecreatetruecolor($ww,$hh);
	$crgb = new RGB( $newgdh );
	$fillColor = $crgb->Allocate($aFillColor);
	imagefilledrectangle($newgdh,0,0,$ww-1,$hh-1,$fillColor);

	if( $aBorder ) {
	    $colidx = $crgb->Allocate($aBorder);
	    imagerectangle($newgdh,0,0,$ww-1,$hh-1,$colidx);
	}

	$mid = round($w * $aHorizonPos);
    
	$last=$h;
	for($y=0; $y < $h; ++$y) {	

	    $yp = $h-$y-1;
	    $yt = floor($yp * $aHorizon / ($aSkewDist + $yp));	    

	    if( !$aQuality ) {
		if( $last <= $yt ) continue ;
		$last = $yt;
	    }

	    for($x=0; $x < $w; ++$x) {	    
		$xt = ($x-$mid) * $aSkewDist / ($aSkewDist + $yp);
		if( $aDir == SKEW3D_UP ) 
		    $rgb = imagecolorat($aGdImg,$x,$h-$y-1);
		else
		    $rgb = imagecolorat($aGdImg,$x,$y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;    
		$colidx = imagecolorallocate($newgdh,$r,$g,$b);	
		$xt = round($xt+$mid);
		if( $aDir == SKEW3D_UP ) {
		    $syt = $yt;
		}
		else {
		    $syt = $hh-$yt-1;
		}

		if( !empty($set[$yt]) ) {
		    $nrgb = imagecolorat($newgdh,$xt,$syt);
		    $nr = ($nrgb >> 16) & 0xFF;
		    $ng = ($nrgb >> 8) & 0xFF;
		    $nb = $nrgb & 0xFF;    
		    $colidx = imagecolorallocate($newgdh,floor(($r+$nr)/2),
						 floor(($g+$ng)/2),floor(($b+$nb)/2));	
		}	

		imagesetpixel($newgdh,$xt,$syt,$colidx);	
	    }

	    $set[$yt] = true;	
	}

	return $newgdh;
    }

    // --------------------------------------------------------------------
    // _TransVert3D() and _TransHor3D() are helper methods to 
    // Skew3D(). 
    // --------------------------------------------------------------------
    function _TransHor3D($aGdImg,$aHorizon=100,$aSkewDist=120,$aDir=SKEW3D_LEFT,$aMinSize=true,$aFillColor='#FFFFFF',$aQuality=false,$aBorder=false,$aHorizonPos=0.5) {

	$w = imagesx($aGdImg);
	$h = imagesy($aGdImg);

	// Create new image
	$hh = $h;
	if( $aMinSize ) 
	    $ww = ceil($w * $aHorizon / ($aSkewDist+$w));
	else 
	    $ww = $w;
	
	$newgdh = imagecreatetruecolor($ww,$hh);
	$crgb = new RGB( $newgdh );
	$fillColor = $crgb->Allocate($aFillColor);
	imagefilledrectangle($newgdh,0,0,$ww-1,$hh-1,$fillColor);

	if( $aBorder ) {
	    $colidx = $crgb->Allocate($aBorder);
	    imagerectangle($newgdh,0,0,$ww-1,$hh-1,$colidx);
	}

	$mid = round($h * $aHorizonPos);

	$last = -1; 
	for($x=0; $x < $w-1; ++$x) {	    
	    $xt = floor($x * $aHorizon / ($aSkewDist + $x));
	    if( !$aQuality ) {
		if( $last >= $xt ) continue ;
		$last = $xt;
	    }

	    for($y=0; $y < $h; ++$y) {	
		$yp = $h-$y-1;
		$yt = ($yp-$mid) * $aSkewDist / ($aSkewDist + $x);

		if( $aDir == SKEW3D_RIGHT ) 
		    $rgb = imagecolorat($aGdImg,$w-$x-1,$y);
		else
		    $rgb = imagecolorat($aGdImg,$x,$y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;    
		$colidx = imagecolorallocate($newgdh,$r,$g,$b);	
		$yt = floor($hh-$yt-$mid-1);
		if( $aDir == SKEW3D_RIGHT ) {
		    $sxt = $ww-$xt-1;
		}
		else
		    $sxt = $xt ;

		if( !empty($set[$xt]) ) {
		    $nrgb = imagecolorat($newgdh,$sxt,$yt);
		    $nr = ($nrgb >> 16) & 0xFF;
		    $ng = ($nrgb >> 8) & 0xFF;
		    $nb = $nrgb & 0xFF;    
		    $colidx = imagecolorallocate($newgdh,floor(($r+$nr)/2),
						 floor(($g+$ng)/2),floor(($b+$nb)/2));	
		}
		imagesetpixel($newgdh,$sxt,$yt,$colidx);	
	    }

	    $set[$xt] = true;
	}

	return $newgdh;
    }

    // --------------------------------------------------------------------
    // Skew image for the apperance of a 3D effect
    // This transforms an image into a 3D-skewed version
    // of the image. The transformation is specified by giving the height
    // of the artificial horizon and specifying a "skew" factor which
    // is the distance on the horizon line between the point of 
    // convergence and perspective line.
    //
    // The function returns the GD handle of the transformed image
    // leaving the original image untouched.
    //
    // Parameters:
    // * $aGdImg, GD handle to the image to be transformed
    // * $aHorizon, Distance to the horizon 
    // * $aSkewDist, Distance from the horizon point of convergence
    //   on the horizon line to the perspective points. A larger 
    //   value will fore-shorten the image more
    // * $aDir, parameter specifies type of convergence. This of this 
    //   as the walls in a room you are looking at. This specifies if the
    //   image should be applied on the left,right,top or bottom walls.
    // * $aMinSize, true=make the new image just as big as needed,
    //   false = keep the image the same size as the original image
    // * $aFillColor, Background fill color in the image
    // * $aHiQuality, true=performa some interpolation that improves
    //   the image quality but at the expense of performace. Enabling
    //   high quality will have a dramatic effect on the time it takes
    //   to transform an image.
    // * $aBorder, if set to anything besides false this will draw a 
    //   a border of the speciied color around the image
    // --------------------------------------------------------------------
    function Skew3D($aHorizon=120,$aSkewDist=150,$aDir=SKEW3D_DOWN,$aHiQuality=false,$aMinSize=true,$aFillColor='#FFFFFF',$aBorder=false) {
	return $this->_Skew3D($this->gdImg,$aHorizon,$aSkewDist,$aDir,$aHiQuality,
			      $aMinSize,$aFillColor,$aBorder);
    }

    function _Skew3D($aGdImg,$aHorizon=120,$aSkewDist=150,$aDir=SKEW3D_DOWN,$aHiQuality=false,$aMinSize=true,$aFillColor='#FFFFFF',$aBorder=false) {
	if( $aDir == SKEW3D_DOWN || $aDir == SKEW3D_UP )
	    return $this->_TransVert3D($aGdImg,$aHorizon,$aSkewDist,$aDir,$aMinSize,$aFillColor,$aHiQuality,$aBorder);
	else
	    return $this->_TransHor3D($aGdImg,$aHorizon,$aSkewDist,$aDir,$aMinSize,$aFillColor,$aHiQuality,$aBorder);

    }
    
}


?>