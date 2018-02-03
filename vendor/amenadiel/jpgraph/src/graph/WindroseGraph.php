<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//============================================================
// CLASS WindroseGraph
//============================================================
class WindroseGraph extends Graph
{
    private $posx;
    private $posy;
    public $plots = [];

    public function __construct($width = 300, $height = 200, $cachedName = "", $timeout = 0, $inline = 1)
    {
        parent::__construct($width, $height, $cachedName, $timeout, $inline);
        $this->posx = $width / 2;
        $this->posy = $height / 2;
        $this->SetColor('white');
        $this->title->SetFont(FF_VERDANA, FS_NORMAL, 12);
        $this->title->SetMargin(8);
        $this->subtitle->SetFont(FF_VERDANA, FS_NORMAL, 10);
        $this->subtitle->SetMargin(0);
        $this->subsubtitle->SetFont(FF_VERDANA, FS_NORMAL, 8);
        $this->subsubtitle->SetMargin(0);
    }

    public function StrokeTexts()
    {
        if ($this->texts != null) {
            $n = count($this->texts);
            for ($i = 0; $i < $n; ++$i) {
                $this->texts[$i]->Stroke($this->img);
            }
        }
    }

    public function StrokeIcons()
    {
        if ($this->iIcons != null) {
            $n = count($this->iIcons);
            for ($i = 0; $i < $n; ++$i) {
                // Since Windrose graphs doesn't have any linear scale the position of
                // each icon has to be given as absolute coordinates
                $this->iIcons[$i]->_Stroke($this->img);
            }
        }
    }

    //---------------
    // PUBLIC METHODS
    public function Add($aObj)
    {
        if (is_array($aObj) && count($aObj) > 0) {
            $cl = $aObj[0];
        } else {
            $cl = $aObj;
        }
        if ($cl instanceof Text) {
            $this->AddText($aObj);
        } elseif ($cl instanceof IconPlot) {
            $this->AddIcon($aObj);
        } elseif (($cl instanceof WindrosePlot) || ($cl instanceof LayoutRect) || ($cl instanceof LayoutHor)) {
            $this->plots[] = $aObj;
        } else {
            Util\JpGraphError::RaiseL(22021);
        }
    }

    public function AddText($aTxt, $aToY2 = false)
    {
        parent::AddText($aTxt);
    }

    public function SetColor($c)
    {
        $this->SetMarginColor($c);
    }

    // Method description
    public function Stroke($aStrokeFileName = "")
    {

        // If the filename is the predefined value = '_csim_special_'
        // we assume that the call to stroke only needs to do enough
        // to correctly generate the CSIM maps.
        // We use this variable to skip things we don't strictly need
        // to do to generate the image map to improve performance
        // as best we can. Therefore you will see a lot of tests !$_csim in the
        // code below.
        $_csim = ($aStrokeFileName === _CSIM_SPECIALFILE);

        // We need to know if we have stroked the plot in the
        // GetCSIMareas. Otherwise the CSIM hasn't been generated
        // and in the case of GetCSIM called before stroke to generate
        // CSIM without storing an image to disk GetCSIM must call Stroke.
        $this->iHasStroked = true;

        if ($this->background_image != "" || $this->background_cflag != "") {
            $this->StrokeFrameBackground();
        } else {
            $this->StrokeFrame();
        }

        // n holds number of plots
        $n = count($this->plots);
        for ($i = 0; $i < $n; ++$i) {
            $this->plots[$i]->Stroke($this);
        }

        $this->footer->Stroke($this->img);
        $this->StrokeIcons();
        $this->StrokeTexts();
        $this->StrokeTitles();

        // If the filename is given as the special "__handle"
        // then the image handler is returned and the image is NOT
        // streamed back
        if ($aStrokeFileName == _IMG_HANDLER) {
            return $this->img->img;
        } else {
            // Finally stream the generated picture
            $this->cache->PutAndStream($this->img, $this->cache_name, $this->inline,
                $aStrokeFileName);
        }
    }
} // Class
