<?php
namespace Amenadiel\JpGraph\Graph;

//===================================================
// CLASS HeaderProperty
// Description: Data encapsulating class to hold property
// for each type of the scale headers
//===================================================
class HeaderProperty
{
    public $grid;
    public $iShowLabels = true, $iShowGrid = true;
    public $iTitleVertMargin = 3, $iFFamily = FF_FONT0, $iFStyle = FS_NORMAL, $iFSize = 8;
    public $iStyle = 0;
    public $iFrameColor = "black", $iFrameWeight = 1;
    public $iBackgroundColor = "white";
    public $iWeekendBackgroundColor = "lightgray", $iSundayTextColor = "red"; // these are only used with day scale
    public $iTextColor = "black";
    public $iLabelFormStr = "%d";
    public $iIntervall = 1;

    //---------------
    // CONSTRUCTOR
    public function __construct()
    {
        $this->grid = new LineProperty();
    }

    //---------------
    // PUBLIC METHODS
    public function Show($aShow = true)
    {
        $this->iShowLabels = $aShow;
    }

    public function SetIntervall($aInt)
    {
        $this->iIntervall = $aInt;
    }

    public function SetInterval($aInt)
    {
        $this->iIntervall = $aInt;
    }

    public function GetIntervall()
    {
        return $this->iIntervall;
    }

    public function SetFont($aFFamily, $aFStyle = FS_NORMAL, $aFSize = 10)
    {
        $this->iFFamily = $aFFamily;
        $this->iFStyle = $aFStyle;
        $this->iFSize = $aFSize;
    }

    public function SetFontColor($aColor)
    {
        $this->iTextColor = $aColor;
    }

    public function GetFontHeight($aImg)
    {
        $aImg->SetFont($this->iFFamily, $this->iFStyle, $this->iFSize);
        return $aImg->GetFontHeight();
    }

    public function GetFontWidth($aImg)
    {
        $aImg->SetFont($this->iFFamily, $this->iFStyle, $this->iFSize);
        return $aImg->GetFontWidth();
    }

    public function GetStrWidth($aImg, $aStr)
    {
        $aImg->SetFont($this->iFFamily, $this->iFStyle, $this->iFSize);
        return $aImg->GetTextWidth($aStr);
    }

    public function SetStyle($aStyle)
    {
        $this->iStyle = $aStyle;
    }

    public function SetBackgroundColor($aColor)
    {
        $this->iBackgroundColor = $aColor;
    }

    public function SetFrameWeight($aWeight)
    {
        $this->iFrameWeight = $aWeight;
    }

    public function SetFrameColor($aColor)
    {
        $this->iFrameColor = $aColor;
    }

    // Only used by day scale
    public function SetWeekendColor($aColor)
    {
        $this->iWeekendBackgroundColor = $aColor;
    }

    // Only used by day scale
    public function SetSundayFontColor($aColor)
    {
        $this->iSundayTextColor = $aColor;
    }

    public function SetTitleVertMargin($aMargin)
    {
        $this->iTitleVertMargin = $aMargin;
    }

    public function SetLabelFormatString($aStr)
    {
        $this->iLabelFormStr = $aStr;
    }

    public function SetFormatString($aStr)
    {
        $this->SetLabelFormatString($aStr);
    }

}
