<?php
//=======================================================================
// File:        JPGRAPH_PLOTMARK.PHP
// Description: Class file. Handles plotmarks
// Created:     2003-03-21
// Ver:         $Id: jpgraph_plotmark.inc.php 1106 2009-02-22 20:16:35Z ljp $
//
// Copyright (c) Aditus Consulting. All rights reserved.
//========================================================================


//===================================================
// CLASS PlotMark
// Description: Handles the plot marks in graphs
//===================================================

class PlotMark {
    public $title, $show=true;
    public $type,$weight=1;
    public $iFormatCallback="", $iFormatCallback2="";
    public $fill_color="blue";
    public $color="black", $width=4;
    private $yvalue,$xvalue='',$csimtarget,$csimwintarget='',$csimalt,$csimareas;
    private $markimg='',$iScale=1.0;
    private $oldfilename='',$iFileName='';
    private $imgdata_balls = null;
    private $imgdata_diamonds = null;
    private $imgdata_squares = null;
    private $imgdata_bevels = null;
    private $imgdata_stars = null;
    private $imgdata_pushpins = null;

    //--------------
    // CONSTRUCTOR
    function __construct() {
        $this->title = new Text();
        $this->title->Hide();
        $this->csimareas = '';
        $this->type=-1;
    }
    //---------------
    // PUBLIC METHODS
    function SetType($aType,$aFileName='',$aScale=1.0) {
        $this->type = $aType;
        if( $aType == MARK_IMG && $aFileName=='' ) {
            JpGraphError::RaiseL(23003);//('A filename must be specified if you set the mark type to MARK_IMG.');
        }
        $this->iFileName = $aFileName;
        $this->iScale = $aScale;
    }

    function SetCallback($aFunc) {
        $this->iFormatCallback = $aFunc;
    }

    function SetCallbackYX($aFunc) {
        $this->iFormatCallback2 = $aFunc;
    }

    function GetType() {
        return $this->type;
    }

    function SetColor($aColor) {
        $this->color=$aColor;
    }

    function SetFillColor($aFillColor) {
        $this->fill_color = $aFillColor;
    }

    function SetWeight($aWeight) {
        $this->weight = $aWeight;
    }

    // Synonym for SetWidth()
    function SetSize($aWidth) {
        $this->width=$aWidth;
    }

    function SetWidth($aWidth) {
        $this->width=$aWidth;
    }

    function SetDefaultWidth() {
        switch( $this->type ) {
            case MARK_CIRCLE:
            case MARK_FILLEDCIRCLE:
                $this->width=4;
                break;
            default:
                $this->width=7;
        }
    }

    function GetWidth() {
        return $this->width;
    }

    function Hide($aHide=true) {
        $this->show = !$aHide;
    }

    function Show($aShow=true) {
        $this->show = $aShow;
    }

    function SetCSIMAltVal($aY,$aX='') {
        $this->yvalue=$aY;
        $this->xvalue=$aX;
    }

    function SetCSIMTarget($aTarget,$aWinTarget='') {
        $this->csimtarget=$aTarget;
        $this->csimwintarget=$aWinTarget;
    }

    function SetCSIMAlt($aAlt) {
        $this->csimalt=$aAlt;
    }

    function GetCSIMAreas(){
        return $this->csimareas;
    }

    function AddCSIMPoly($aPts) {
        $coords = round($aPts[0]).", ".round($aPts[1]);
        $n = count($aPts)/2;
        for( $i=1; $i < $n; ++$i){
            $coords .= ", ".round($aPts[2*$i]).", ".round($aPts[2*$i+1]);
        }
        $this->csimareas="";
        if( !empty($this->csimtarget) ) {
            $this->csimareas .= "<area shape=\"poly\" coords=\"$coords\" href=\"".htmlentities($this->csimtarget)."\"";

            if( !empty($this->csimwintarget) ) {
                $this->csimareas .= " target=\"".$this->csimwintarget."\" ";
            }

            if( !empty($this->csimalt) ) {
                $tmp=sprintf($this->csimalt,$this->yvalue,$this->xvalue);
                $this->csimareas .= " title=\"$tmp\" alt=\"$tmp\"";
            }
            $this->csimareas .= " />\n";
        }
    }

    function AddCSIMCircle($x,$y,$r) {
        $x = round($x); $y=round($y); $r=round($r);
        $this->csimareas="";
        if( !empty($this->csimtarget) ) {
            $this->csimareas .= "<area shape=\"circle\" coords=\"$x,$y,$r\" href=\"".htmlentities($this->csimtarget)."\"";

            if( !empty($this->csimwintarget) ) {
                $this->csimareas .= " target=\"".$this->csimwintarget."\" ";
            }

            if( !empty($this->csimalt) ) {
                $tmp=sprintf($this->csimalt,$this->yvalue,$this->xvalue);
                $this->csimareas .= " title=\"$tmp\" alt=\"$tmp\" ";
            }
            $this->csimareas .= " />\n";
        }
    }
     
    function Stroke($img,$x,$y) {
        if( !$this->show ) return;

        if( $this->iFormatCallback != '' || $this->iFormatCallback2 != '' ) {

            if( $this->iFormatCallback != '' ) {
                $f = $this->iFormatCallback;
                list($width,$color,$fcolor) = call_user_func($f,$this->yvalue);
                $filename = $this->iFileName;
                $imgscale = $this->iScale;
            }
            else {
                $f = $this->iFormatCallback2;
                list($width,$color,$fcolor,$filename,$imgscale) = call_user_func($f,$this->yvalue,$this->xvalue);
                if( $filename=="" ) $filename = $this->iFileName;
                if( $imgscale=="" ) $imgscale = $this->iScale;
            }

            if( $width=="" ) $width = $this->width;
            if( $color=="" ) $color = $this->color;
            if( $fcolor=="" ) $fcolor = $this->fill_color;

        }
        else {
            $fcolor = $this->fill_color;
            $color = $this->color;
            $width = $this->width;
            $filename = $this->iFileName;
            $imgscale = $this->iScale;
        }

        if( $this->type == MARK_IMG ||
        ($this->type >= MARK_FLAG1 && $this->type <= MARK_FLAG4 ) ||
        $this->type >= MARK_IMG_PUSHPIN ) {

            // Note: For the builtin images we use the "filename" parameter
            // to denote the color
            $anchor_x = 0.5;
            $anchor_y = 0.5;
            switch( $this->type ) {
                case MARK_FLAG1:
                case MARK_FLAG2:
                case MARK_FLAG3:
                case MARK_FLAG4:
                    $this->markimg = FlagCache::GetFlagImgByName($this->type-MARK_FLAG1+1,$filename);
                    break;

                case MARK_IMG :
                    // Load an image and use that as a marker
                    // Small optimization, if we have already read an image don't
                    // waste time reading it again.
                    if( $this->markimg == '' || !($this->oldfilename === $filename) ) {
                        $this->markimg = Graph::LoadBkgImage('',$filename);
                        $this->oldfilename = $filename ;
                    }
                    break;

                case MARK_IMG_PUSHPIN:
                case MARK_IMG_SPUSHPIN:
                case MARK_IMG_LPUSHPIN:
                    if( $this->imgdata_pushpins == null ) {
                        require_once 'imgdata_pushpins.inc.php';
                        $this->imgdata_pushpins = new ImgData_PushPins();
                    }
                    $this->markimg = $this->imgdata_pushpins->GetImg($this->type,$filename);
                    list($anchor_x,$anchor_y) = $this->imgdata_pushpins->GetAnchor();
                    break;

                case MARK_IMG_SQUARE:
                    if( $this->imgdata_squares == null ) {
                        require_once 'imgdata_squares.inc.php';
                        $this->imgdata_squares = new ImgData_Squares();
                    }
                    $this->markimg = $this->imgdata_squares->GetImg($this->type,$filename);
                    list($anchor_x,$anchor_y) = $this->imgdata_squares->GetAnchor();
                    break;

                case MARK_IMG_STAR:
                    if( $this->imgdata_stars == null ) {
                        require_once 'imgdata_stars.inc.php';
                        $this->imgdata_stars = new ImgData_Stars();
                    }
                    $this->markimg = $this->imgdata_stars->GetImg($this->type,$filename);
                    list($anchor_x,$anchor_y) = $this->imgdata_stars->GetAnchor();
                    break;

                case MARK_IMG_BEVEL:
                    if( $this->imgdata_bevels == null ) {
                        require_once 'imgdata_bevels.inc.php';
                        $this->imgdata_bevels = new ImgData_Bevels();
                    }
                    $this->markimg = $this->imgdata_bevels->GetImg($this->type,$filename);
                    list($anchor_x,$anchor_y) = $this->imgdata_bevels->GetAnchor();
                    break;

                case MARK_IMG_DIAMOND:
                    if( $this->imgdata_diamonds == null ) {
                        require_once 'imgdata_diamonds.inc.php';
                        $this->imgdata_diamonds = new ImgData_Diamonds();
                    }
                    $this->markimg = $this->imgdata_diamonds->GetImg($this->type,$filename);
                    list($anchor_x,$anchor_y) = $this->imgdata_diamonds->GetAnchor();
                    break;

                case MARK_IMG_BALL:
                case MARK_IMG_SBALL:
                case MARK_IMG_MBALL:
                case MARK_IMG_LBALL:
                    if( $this->imgdata_balls == null ) {
                        require_once 'imgdata_balls.inc.php';
                        $this->imgdata_balls = new ImgData_Balls();
                    }
                    $this->markimg = $this->imgdata_balls->GetImg($this->type,$filename);
                    list($anchor_x,$anchor_y) = $this->imgdata_balls->GetAnchor();
                    break;
            }

            $w = $img->GetWidth($this->markimg);
            $h = $img->GetHeight($this->markimg);
             
            $dw = round($imgscale * $w );
            $dh = round($imgscale * $h );

            // Do potential rotation
            list($x,$y) = $img->Rotate($x,$y);

            $dx = round($x-$dw*$anchor_x);
            $dy = round($y-$dh*$anchor_y);
             
            $this->width = max($dx,$dy);
             
            $img->Copy($this->markimg,$dx,$dy,0,0,$dw,$dh,$w,$h);
            if( !empty($this->csimtarget) ) {
                $this->csimareas = "<area shape=\"rect\" coords=\"".
                $dx.','.$dy.','.round($dx+$dw).','.round($dy+$dh).'" '.
      "href=\"".htmlentities($this->csimtarget)."\"";

                if( !empty($this->csimwintarget) ) {
                    $this->csimareas .= " target=\"".$this->csimwintarget."\" ";
                }

                if( !empty($this->csimalt) ) {
                    $tmp=sprintf($this->csimalt,$this->yvalue,$this->xvalue);
                    $this->csimareas .= " title=\"$tmp\" alt=\"$tmp\" ";
                }
                $this->csimareas .= " />\n";
            }
             
            // Stroke title
            $this->title->Align("center","top");
            $this->title->Stroke($img,$x,$y+round($dh/2));
            return;
        }

        $weight = $this->weight;
        $dx=round($width/2,0);
        $dy=round($width/2,0);
        $pts=0;

        switch( $this->type ) {
            case MARK_SQUARE:
                $c[]=$x-$dx;$c[]=$y-$dy;
                $c[]=$x+$dx;$c[]=$y-$dy;
                $c[]=$x+$dx;$c[]=$y+$dy;
                $c[]=$x-$dx;$c[]=$y+$dy;
                $c[]=$x-$dx;$c[]=$y-$dy;
                $pts=5;
                break;
            case MARK_UTRIANGLE:
                ++$dx;++$dy;
                $c[]=$x-$dx;$c[]=$y+0.87*$dy; // tan(60)/2*$dx
                $c[]=$x;$c[]=$y-0.87*$dy;
                $c[]=$x+$dx;$c[]=$y+0.87*$dy;
                $c[]=$x-$dx;$c[]=$y+0.87*$dy; // tan(60)/2*$dx
                $pts=4;
                break;
            case MARK_DTRIANGLE:
                ++$dx;++$dy;
                $c[]=$x;$c[]=$y+0.87*$dy; // tan(60)/2*$dx
                $c[]=$x-$dx;$c[]=$y-0.87*$dy;
                $c[]=$x+$dx;$c[]=$y-0.87*$dy;
                $c[]=$x;$c[]=$y+0.87*$dy; // tan(60)/2*$dx
                $pts=4;
                break;
            case MARK_DIAMOND:
                $c[]=$x;$c[]=$y+$dy;
                $c[]=$x-$dx;$c[]=$y;
                $c[]=$x;$c[]=$y-$dy;
                $c[]=$x+$dx;$c[]=$y;
                $c[]=$x;$c[]=$y+$dy;
                $pts=5;
                break;
            case MARK_LEFTTRIANGLE:
                $c[]=$x;$c[]=$y;
                $c[]=$x;$c[]=$y+2*$dy;
                $c[]=$x+$dx*2;$c[]=$y;
                $c[]=$x;$c[]=$y;
                $pts=4;
                break;
            case MARK_RIGHTTRIANGLE:
                $c[]=$x-$dx*2;$c[]=$y;
                $c[]=$x;$c[]=$y+2*$dy;
                $c[]=$x;$c[]=$y;
                $c[]=$x-$dx*2;$c[]=$y;
                $pts=4;
                break;
            case MARK_FLASH:
                $dy *= 2;
                $c[]=$x+$dx/2; $c[]=$y-$dy;
                $c[]=$x-$dx+$dx/2; $c[]=$y+$dy*0.7-$dy;
                $c[]=$x+$dx/2; $c[]=$y+$dy*1.3-$dy;
                $c[]=$x-$dx+$dx/2; $c[]=$y+2*$dy-$dy;
                $img->SetLineWeight($weight);
                $img->SetColor($color);
                $img->Polygon($c);
                $img->SetLineWeight(1);
                $this->AddCSIMPoly($c);
                break;
        }

        if( $pts>0 ) {
            $this->AddCSIMPoly($c);
            $img->SetLineWeight($weight);
            $img->SetColor($fcolor);
            $img->FilledPolygon($c);
            $img->SetColor($color);
            $img->Polygon($c);
            $img->SetLineWeight(1);
        }
        elseif( $this->type==MARK_CIRCLE ) {
            $img->SetColor($color);
            $img->Circle($x,$y,$width);
            $this->AddCSIMCircle($x,$y,$width);
        }
        elseif( $this->type==MARK_FILLEDCIRCLE ) {
            $img->SetColor($fcolor);
            $img->FilledCircle($x,$y,$width);
            $img->SetColor($color);
            $img->Circle($x,$y,$width);
            $this->AddCSIMCircle($x,$y,$width);
        }
        elseif( $this->type==MARK_CROSS ) {
            // Oversize by a pixel to match the X
            $img->SetColor($color);
            $img->SetLineWeight($weight);
            $img->Line($x,$y+$dy+1,$x,$y-$dy-1);
            $img->Line($x-$dx-1,$y,$x+$dx+1,$y);
            $this->AddCSIMCircle($x,$y,$dx);
        }
        elseif( $this->type==MARK_X ) {
            $img->SetColor($color);
            $img->SetLineWeight($weight);
            $img->Line($x+$dx,$y+$dy,$x-$dx,$y-$dy);
            $img->Line($x-$dx,$y+$dy,$x+$dx,$y-$dy);
            $this->AddCSIMCircle($x,$y,$dx+$dy);
        }
        elseif( $this->type==MARK_STAR ) {
            $img->SetColor($color);
            $img->SetLineWeight($weight);
            $img->Line($x+$dx,$y+$dy,$x-$dx,$y-$dy);
            $img->Line($x-$dx,$y+$dy,$x+$dx,$y-$dy);
            // Oversize by a pixel to match the X
            $img->Line($x,$y+$dy+1,$x,$y-$dy-1);
            $img->Line($x-$dx-1,$y,$x+$dx+1,$y);
            $this->AddCSIMCircle($x,$y,$dx+$dy);
        }

        // Stroke title
        $this->title->Align("center","center");
        $this->title->Stroke($img,$x,$y);
    }
} // Class



//========================================================================
// CLASS ImgData
// Description: Base class for all image data classes that contains the
// real image data.
//========================================================================
class ImgData {
    protected $name = '';  // Each subclass gives a name
    protected $an = array();  // Data array names
    protected $colors = array(); // Available colors
    protected $index  = array(); // Index for colors
    protected $maxidx = 0 ;  // Max color index
    protected $anchor_x=0.5, $anchor_y=0.5 ;    // Where is the center of the image
    
    function __construct() {
        // Empty
    }
    
    // Create a GD image from the data and return a GD handle
    function GetImg($aMark,$aIdx) {
        $n = $this->an[$aMark];
        if( is_string($aIdx) ) {
            if( !in_array($aIdx,$this->colors) ) {
                JpGraphError::RaiseL(23001,$this->name,$aIdx);//('This marker "'.($this->name).'" does not exist in color: '.$aIdx);
            }
            $idx = $this->index[$aIdx];
        }
        elseif( !is_integer($aIdx) ||
        (is_integer($aIdx) && $aIdx > $this->maxidx ) ) {
            JpGraphError::RaiseL(23002,$this->name);//('Mark color index too large for marker "'.($this->name).'"');
        }
        else
        $idx = $aIdx ;
        return Image::CreateFromString(base64_decode($this->{$n}[$idx][1]));
    }
    
    function GetAnchor() {
        return array($this->anchor_x,$this->anchor_y);
    }
}


// Keep a global flag cache to reduce memory usage
$_gFlagCache=array(
1 => null,
2 => null,
3 => null,
4 => null,
);
// Only supposed to b called as statics
class FlagCache {
    
    static function GetFlagImgByName($aSize,$aName) {
        global $_gFlagCache;
        require_once('jpgraph_flags.php');
        if( $_gFlagCache[$aSize] === null ) {
            $_gFlagCache[$aSize] = new FlagImages($aSize);
        }
        $f = $_gFlagCache[$aSize];
        $idx = $f->GetIdxByName($aName,$aFullName);
        return $f->GetImgByIdx($idx);
    }
}

?>