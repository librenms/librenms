<?php
/*=======================================================================
 // File:        JPGRAPH_MGRAPH.PHP
 // Description: Class to handle multiple graphs in the same image
 // Created:     2006-01-15
 // Ver:         $Id: jpgraph_mgraph.php 1770 2009-08-17 06:10:22Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

//=============================================================================
// CLASS MGraph
// Description: Create a container image that can hold several graph
//=============================================================================
class MGraph {

    public $title = null, $subtitle = null, $subsubtitle = null;

    protected $img=NULL;
    protected $iCnt=0,$iGraphs = array(); // image_handle, x, y, fx, fy, sizex, sizey
    protected $iFillColor='white', $iCurrentColor=0;
    protected $lm=4,$rm=4,$tm=4,$bm=4;
    protected $iDoFrame = FALSE, $iFrameColor = 'black', $iFrameWeight = 1;
    protected $iLineWeight = 1;
    protected $expired=false;
    protected $cache=null,$cache_name = '',$inline=true;
    protected $image_format='png',$image_quality=75;
    protected $iWidth=NULL,$iHeight=NULL;
    protected $background_image='',$background_image_center=true,
    $backround_image_format='',$background_image_mix=100,
    $background_image_y=NULL, $background_image_x=NULL;
    private $doshadow=false, $shadow_width=4, $shadow_color='gray@0.5';
    public $footer;


    // Create a new instane of the combined graph
    function __construct($aWidth=NULL,$aHeight=NULL,$aCachedName='',$aTimeOut=0,$aInline=true) {
        $this->iWidth = $aWidth;
        $this->iHeight = $aHeight;

        // If the cached version exist just read it directly from the
        // cache, stream it back to browser and exit
        if( $aCachedName!='' && READ_CACHE && $aInline ) {
            $this->cache = new ImgStreamCache();
            $this->cache->SetTimeOut($aTimeOut);
            $image = new Image();
            if( $this->cache->GetAndStream($image,$aCachedName) ) {
                exit();
            }
        }
        $this->inline = $aInline;
        $this->cache_name = $aCachedName;

        $this->title = new Text();
        $this->title->ParagraphAlign('center');
        $this->title->SetFont(FF_FONT2,FS_BOLD);
        $this->title->SetMargin(3);
        $this->title->SetAlign('center');

        $this->subtitle = new Text();
        $this->subtitle->ParagraphAlign('center');
        $this->subtitle->SetFont(FF_FONT1,FS_BOLD);
        $this->subtitle->SetMargin(3);
        $this->subtitle->SetAlign('center');

        $this->subsubtitle = new Text();
        $this->subsubtitle->ParagraphAlign('center');
        $this->subsubtitle->SetFont(FF_FONT1,FS_NORMAL);
        $this->subsubtitle->SetMargin(3);
        $this->subsubtitle->SetAlign('center');

        $this->footer = new Footer();

    }

    // Specify background fill color for the combined graph
    function SetFillColor($aColor) {
        $this->iFillColor = $aColor;
    }

    // Add a frame around the combined graph
    function SetFrame($aFlg,$aColor='black',$aWeight=1) {
        $this->iDoFrame = $aFlg;
        $this->iFrameColor = $aColor;
        $this->iFrameWeight = $aWeight;
    }

    // Specify a background image blend
    function SetBackgroundImageMix($aMix) {
        $this->background_image_mix = $aMix ;
    }

    // Specify a background image
    function SetBackgroundImage($aFileName,$aCenter_aX=NULL,$aY=NULL) {
        // Second argument can be either a boolean value or
        // a numeric
        $aCenter=TRUE;
        $aX=NULL;

        if( is_numeric($aCenter_aX) ) {
            $aX=$aCenter_aX;
        }

        // Get extension to determine image type
        $e = explode('.',$aFileName);
        if( !$e ) {
            JpGraphError::RaiseL(12002,$aFileName);
            //('Incorrect file name for MGraph::SetBackgroundImage() : '.$aFileName.' Must have a valid image extension (jpg,gif,png) when using autodetection of image type');
        }

        $valid_formats = array('png', 'jpg', 'gif');
        $aImgFormat = strtolower($e[count($e)-1]);
        if ($aImgFormat == 'jpeg')  {
            $aImgFormat = 'jpg';
        }
        elseif (!in_array($aImgFormat, $valid_formats) )  {
            JpGraphError::RaiseL(12003,$aImgFormat,$aFileName);
            //('Unknown file extension ($aImgFormat) in MGraph::SetBackgroundImage() for filename: '.$aFileName);
        }

        $this->background_image = $aFileName;
        $this->background_image_center=$aCenter;
        $this->background_image_format=$aImgFormat;
        $this->background_image_x = $aX;
        $this->background_image_y = $aY;
    }

    function _strokeBackgroundImage() {
        if( $this->background_image == '' ) return;

        $bkgimg = Graph::LoadBkgImage('',$this->background_image);

        // Background width & Heoght
        $bw = imagesx($bkgimg);
        $bh = imagesy($bkgimg);

        // Canvas width and height
        $cw = imagesx($this->img);
        $ch = imagesy($this->img);

        if( $this->doshadow ) {
            $cw -= $this->shadow_width;
            $ch -= $this->shadow_width;
        }

        if( $this->background_image_x === NULL || $this->background_image_y === NULL ) {
            if( $this->background_image_center ) {
                // Center original image in the plot area
                $x = round($cw/2-$bw/2); $y = round($ch/2-$bh/2);
            }
            else {
                // Just copy the image from left corner, no resizing
                $x=0; $y=0;
            }
        }
        else {
            $x = $this->background_image_x;
            $y = $this->background_image_y;
        }
        imagecopymerge($this->img,$bkgimg,$x,$y,0,0,$bw,$bh,$this->background_image_mix);
    }

    function AddMix($aGraph,$x=0,$y=0,$mix=100,$fx=0,$fy=0,$w=0,$h=0) {
        $this->_gdImgHandle($aGraph->Stroke( _IMG_HANDLER),$x,$y,$fx=0,$fy=0,$w,$h,$mix);
    }

    function Add($aGraph,$x=0,$y=0,$fx=0,$fy=0,$w=0,$h=0) {
        $this->_gdImgHandle($aGraph->Stroke( _IMG_HANDLER),$x,$y,$fx=0,$fy=0,$w,$h);
    }

    function _gdImgHandle($agdCanvas,$x,$y,$fx=0,$fy=0,$w=0,$h=0,$mix=100) {
        if( $w == 0 ) {
            $w = @imagesx($agdCanvas);
        }
        if( $w === NULL ) {
            JpGraphError::RaiseL(12007);
            //('Argument to MGraph::Add() is not a valid GD image handle.');
            return;
        }
        if( $h == 0 ) {
            $h = @imagesy($agdCanvas);
        }
        $this->iGraphs[$this->iCnt++] = array($agdCanvas,$x,$y,$fx,$fy,$w,$h,$mix);
    }

    function SetMargin($lm,$rm,$tm,$bm) {
        $this->lm = $lm;
        $this->rm = $rm;
        $this->tm = $tm;
        $this->bm = $bm;
    }

    function SetExpired($aFlg=true) {
        $this->expired = $aFlg;
    }

    function SetImgFormat($aFormat,$aQuality=75) {
        $this->image_format = $aFormat;
        $this->image_quality = $aQuality;
    }

    // Set the shadow around the whole image
    function SetShadow($aShowShadow=true,$aShadowWidth=4,$aShadowColor='gray@0.3') {
        $this->doshadow = $aShowShadow;
        $this->shadow_color = $aShadowColor;
        $this->shadow_width = $aShadowWidth;
        $this->footer->iBottomMargin += $aShadowWidth;
        $this->footer->iRightMargin += $aShadowWidth;
    }

    function StrokeTitle($image,$w,$h) {
        // Stroke title
        if( $this->title->t !== '' ) {

            $margin = 3;

            $y = $this->title->margin;
            if( $this->title->halign == 'center' ) {
                $this->title->Center(0,$w,$y);
            }
            elseif( $this->title->halign == 'left' ) {
                $this->title->SetPos($this->title->margin+2,$y);
            }
            elseif( $this->title->halign == 'right' ) {
                $indent = 0;
                if( $this->doshadow ) {
                    $indent = $this->shadow_width+2;
                }
                $this->title->SetPos($w-$this->title->margin-$indent,$y,'right');
            }
            $this->title->Stroke($image);

            // ... and subtitle
            $y += $this->title->GetTextHeight($image) + $margin + $this->subtitle->margin;
            if( $this->subtitle->halign == 'center' ) {
                $this->subtitle->Center(0,$w,$y);
            }
            elseif( $this->subtitle->halign == 'left' ) {
                $this->subtitle->SetPos($this->subtitle->margin+2,$y);
            }
            elseif( $this->subtitle->halign == 'right' ) {
                $indent = 0;
                if( $this->doshadow ) {
                    $indent = $this->shadow_width+2;
                }
                $this->subtitle->SetPos($this->img->width-$this->subtitle->margin-$indent,$y,'right');
            }
            $this->subtitle->Stroke($image);

            // ... and subsubtitle
            $y += $this->subtitle->GetTextHeight($image) + $margin + $this->subsubtitle->margin;
            if( $this->subsubtitle->halign == 'center' ) {
                $this->subsubtitle->Center(0,$w,$y);
            }
            elseif( $this->subsubtitle->halign == 'left' ) {
                $this->subsubtitle->SetPos($this->subsubtitle->margin+2,$y);
            }
            elseif( $this->subsubtitle->halign == 'right' ) {
                $indent = 0;
                if( $this->doshadow ) {
                    $indent = $this->shadow_width+2;
                }
                $this->subsubtitle->SetPos($w-$this->subsubtitle->margin-$indent,$y,'right');
            }
            $this->subsubtitle->Stroke($image);

        }
    }

    function Stroke($aFileName='') {
        // Find out the necessary size for the container image
        $w=0; $h=0;
        for($i=0; $i < $this->iCnt; ++$i ) {
            $maxw = $this->iGraphs[$i][1]+$this->iGraphs[$i][5];
            $maxh = $this->iGraphs[$i][2]+$this->iGraphs[$i][6];
            $w = max( $w, $maxw );
            $h = max( $h, $maxh );
        }
        $w += $this->lm+$this->rm;
        $h += $this->tm+$this->bm;

        // User specified width,height overrides
        if( $this->iWidth !== NULL && $this->iWidth !== 0 ) $w = $this->iWidth;
        if( $this->iHeight!== NULL && $this->iHeight !== 0) $h = $this->iHeight;

        if( $this->doshadow ) {
            $w += $this->shadow_width;
            $h += $this->shadow_width;
        }

        $image = new Image($w,$h);
        $image->SetImgFormat( $this->image_format,$this->image_quality);

        if( $this->doshadow ) {
            $image->SetColor($this->iFrameColor);
            $image->ShadowRectangle(0,0,$w-1,$h-1,$this->iFillColor,$this->shadow_width,$this->shadow_color);
            $w -= $this->shadow_width;
            $h -= $this->shadow_width;
        }
        else {
            $image->SetColor($this->iFillColor);
            $image->FilledRectangle(0,0,$w-1,$h-1);
        }
        $image->SetExpired($this->expired);

        $this->img = $image->img;
        $this->_strokeBackgroundImage();

        if( $this->iDoFrame && ! $this->doshadow ) {
           $image->SetColor($this->iFrameColor);
           $image->SetLineWeight($this->iFrameWeight);
           $image->Rectangle(0,0,$w-1,$h-1);
        }

        // Copy all sub graphs to the container
        for($i=0; $i < $this->iCnt; ++$i ) {
            $image->CopyMerge($this->iGraphs[$i][0],
                            $this->iGraphs[$i][1]+$this->lm,$this->iGraphs[$i][2]+$this->tm,
                            $this->iGraphs[$i][3],$this->iGraphs[$i][4],
                            $this->iGraphs[$i][5],$this->iGraphs[$i][6],
                            -1,-1, /* Full from width and height */
                            $this->iGraphs[$i][7]);


        }

        $this->StrokeTitle($image,$w,$h);
        $this->footer->Stroke($image);

        // Output image
        if( $aFileName == _IMG_HANDLER ) {
            return $image->img;
        }
        else {
            //Finally stream the generated picture
            $this->cache = new ImgStreamCache();
            $this->cache->PutAndStream($image,$this->cache_name,$this->inline,$aFileName);
        }
    }
}

// EOF

?>
