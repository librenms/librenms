<?php
namespace Amenadiel\JpGraph\Graph;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS GanttGraph
// Description: Main class to handle gantt graphs
//===================================================
class GanttGraph extends Graph
{
    public $scale; // Public accessible
    public $hgrid = null;
    private $iObj = array(); // Gantt objects
    private $iLabelHMarginFactor = 0.2; // 10% margin on each side of the labels
    private $iLabelVMarginFactor = 0.4; // 40% margin on top and bottom of label
    private $iLayout = GANTT_FROMTOP; // Could also be GANTT_EVEN
    private $iSimpleFont = FF_FONT1, $iSimpleFontSize = 11;
    private $iSimpleStyle = GANTT_RDIAG, $iSimpleColor = 'yellow', $iSimpleBkgColor = 'red';
    private $iSimpleProgressBkgColor = 'gray', $iSimpleProgressColor = 'darkgreen';
    private $iSimpleProgressStyle = GANTT_SOLID;
    private $iZoomFactor = 1.0;
    //---------------
    // CONSTRUCTOR
    // Create a new gantt graph
    public function __construct($aWidth = 0, $aHeight = 0, $aCachedName = "", $aTimeOut = 0, $aInline = true)
    {

        // Backward compatibility
        if ($aWidth == -1) {
            $aWidth = 0;
        }

        if ($aHeight == -1) {
            $aHeight = 0;
        }

        if ($aWidth < 0 || $aHeight < 0) {
            Util\JpGraphError::RaiseL(6002);
            //("You can't specify negative sizes for Gantt graph dimensions. Use 0 to indicate that you want the library to automatically determine a dimension.");
        }
        parent::__construct($aWidth, $aHeight, $aCachedName, $aTimeOut, $aInline);
        $this->scale = new GanttScale($this->img);

        // Default margins
        $this->img->SetMargin(15, 17, 25, 15);

        $this->hgrid = new HorizontalGridLine();

        $this->scale->ShowHeaders(GANTT_HWEEK | GANTT_HDAY);
        $this->SetBox();
    }

    //---------------
    // PUBLIC METHODS

    //

    public function SetSimpleFont($aFont, $aSize)
    {
        $this->iSimpleFont = $aFont;
        $this->iSimpleFontSize = $aSize;
    }

    public function SetSimpleStyle($aBand, $aColor, $aBkgColor)
    {
        $this->iSimpleStyle = $aBand;
        $this->iSimpleColor = $aColor;
        $this->iSimpleBkgColor = $aBkgColor;
    }

    // A utility function to help create basic Gantt charts
    public function CreateSimple($data, $constrains = array(), $progress = array())
    {
        $num = count($data);
        for ($i = 0; $i < $num; ++$i) {
            switch ($data[$i][1]) {
                case ACTYPE_GROUP:
                    // Create a slightly smaller height bar since the
                    // "wings" at the end will make it look taller
                    $a = new GanttBar($data[$i][0], $data[$i][2], $data[$i][3], $data[$i][4], '', 8);
                    $a->title->SetFont($this->iSimpleFont, FS_BOLD, $this->iSimpleFontSize);
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

                    $a->SetPattern(BAND_SOLID, 'black');
                    $csimpos = 6;
                    break;

                case ACTYPE_NORMAL:
                    $a = new GanttBar($data[$i][0], $data[$i][2], $data[$i][3], $data[$i][4], '', 10);
                    $a->title->SetFont($this->iSimpleFont, FS_NORMAL, $this->iSimpleFontSize);
                    $a->SetPattern($this->iSimpleStyle, $this->iSimpleColor);
                    $a->SetFillColor($this->iSimpleBkgColor);
                    // Check if this activity should have a constrain line
                    $n = count($constrains);
                    for ($j = 0; $j < $n; ++$j) {
                        if (empty($constrains[$j]) || (count($constrains[$j]) != 3)) {
                            Util\JpGraphError::RaiseL(6003, $j);
                            //("Invalid format for Constrain parameter at index=$j in CreateSimple(). Parameter must start with index 0 and contain arrays of (Row,Constrain-To,Constrain-Type)");
                        }
                        if ($constrains[$j][0] == $data[$i][0]) {
                            $a->SetConstrain($constrains[$j][1], $constrains[$j][2], 'black', ARROW_S2, ARROWT_SOLID);
                        }
                    }

                    // Check if this activity have a progress bar
                    $n = count($progress);
                    for ($j = 0; $j < $n; ++$j) {

                        if (empty($progress[$j]) || (count($progress[$j]) != 2)) {
                            Util\JpGraphError::RaiseL(6004, $j);
                            //("Invalid format for Progress parameter at index=$j in CreateSimple(). Parameter must start with index 0 and contain arrays of (Row,Progress)");
                        }
                        if ($progress[$j][0] == $data[$i][0]) {
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
                    $a = new MileStone($data[$i][0], $data[$i][2], $data[$i][3]);
                    $a->title->SetFont($this->iSimpleFont, FS_NORMAL, $this->iSimpleFontSize);
                    $a->caption->SetFont($this->iSimpleFont, FS_NORMAL, $this->iSimpleFontSize);
                    $csimpos = 5;
                    break;
                default:
                    die('Unknown activity type');
                    break;
            }

            // Setup caption
            $a->caption->Set($data[$i][$csimpos - 1]);

            // Check if this activity should have a CSIM target�?
            if (!empty($data[$i][$csimpos])) {
                $a->SetCSIMTarget($data[$i][$csimpos]);
                $a->SetCSIMAlt($data[$i][$csimpos + 1]);
            }
            if (!empty($data[$i][$csimpos + 2])) {
                $a->title->SetCSIMTarget($data[$i][$csimpos + 2]);
                $a->title->SetCSIMAlt($data[$i][$csimpos + 3]);
            }

            $this->Add($a);
        }
    }

    // Set user specified scale zoom factor when auto sizing is used
    public function SetZoomFactor($aZoom)
    {
        $this->iZoomFactor = $aZoom;
    }

    // Set what headers should be shown
    public function ShowHeaders($aFlg)
    {
        $this->scale->ShowHeaders($aFlg);
    }

    // Specify the fraction of the font height that should be added
    // as vertical margin
    public function SetLabelVMarginFactor($aVal)
    {
        $this->iLabelVMarginFactor = $aVal;
    }

    // Synonym to the method above
    public function SetVMarginFactor($aVal)
    {
        $this->iLabelVMarginFactor = $aVal;
    }

    // Add a new Gantt object
    public function Add($aObject)
    {
        if (is_array($aObject) && count($aObject) > 0) {
            $cl = $aObject[0];
            if (class_exists('IconPlot', false) && ($cl instanceof IconPlot)) {
                $this->AddIcon($aObject);
            } elseif (class_exists('Text', false) && ($cl instanceof Text)) {
                $this->AddText($aObject);
            } else {
                $n = count($aObject);
                for ($i = 0; $i < $n; ++$i) {
                    $this->iObj[] = $aObject[$i];
                }

            }
        } else {
            if (class_exists('IconPlot', false) && ($aObject instanceof IconPlot)) {
                $this->AddIcon($aObject);
            } elseif (class_exists('Text', false) && ($aObject instanceof Text)) {
                $this->AddText($aObject);
            } else {
                $this->iObj[] = $aObject;
            }
        }
    }

    public function StrokeTexts()
    {
        // Stroke any user added text objects
        if ($this->texts != null) {
            $n = count($this->texts);
            for ($i = 0; $i < $n; ++$i) {
                if ($this->texts[$i]->iScalePosX !== null && $this->texts[$i]->iScalePosY !== null) {
                    $x = $this->scale->TranslateDate($this->texts[$i]->iScalePosX);
                    $y = $this->scale->TranslateVertPos($this->texts[$i]->iScalePosY);
                    $y -= $this->scale->GetVertSpacing() / 2;
                } else {
                    $x = $y = null;
                }
                $this->texts[$i]->Stroke($this->img, $x, $y);
            }
        }
    }

    // Override inherit method from Graph and give a warning message
    public function SetScale($aAxisType, $aYMin = 1, $aYMax = 1, $aXMin = 1, $aXMax = 1)
    {
        Util\JpGraphError::RaiseL(6005);
        //("SetScale() is not meaningfull with Gantt charts.");
    }

    // Specify the date range for Gantt graphs (if this is not set it will be
    // automtically determined from the input data)
    public function SetDateRange($aStart, $aEnd)
    {
        // Adjust the start and end so that the indicate the
        // begining and end of respective start and end days
        if (strpos($aStart, ':') === false) {
            $aStart = date('Y-m-d 00:00', strtotime($aStart));
        }

        if (strpos($aEnd, ':') === false) {
            $aEnd = date('Y-m-d 23:59', strtotime($aEnd));
        }

        $this->scale->SetRange($aStart, $aEnd);
    }

    // Get the maximum width of the activity titles columns for the bars
    // The name is lightly misleading since we from now on can have
    // multiple columns in the label section. When this was first written
    // it only supported a single label, hence the name.
    public function GetMaxLabelWidth()
    {
        $m = 10;
        if ($this->iObj != null) {
            $marg = $this->scale->actinfo->iLeftColMargin + $this->scale->actinfo->iRightColMargin;
            $n = count($this->iObj);
            for ($i = 0; $i < $n; ++$i) {
                if (!empty($this->iObj[$i]->title)) {
                    if ($this->iObj[$i]->title->HasTabs()) {
                        list($tot, $w) = $this->iObj[$i]->title->GetWidth($this->img, true);
                        $m = max($m, $tot);
                    } else {
                        $m = max($m, $this->iObj[$i]->title->GetWidth($this->img));
                    }

                }
            }
        }
        return $m;
    }

    // Get the maximum height of the titles for the bars
    public function GetMaxLabelHeight()
    {
        $m = 10;
        if ($this->iObj != null) {
            $n = count($this->iObj);
            // We can not include the title of GnttVLine since that title is stroked at the bottom
            // of the Gantt bar and not in the activity title columns
            for ($i = 0; $i < $n; ++$i) {
                if (!empty($this->iObj[$i]->title) && !($this->iObj[$i] instanceof GanttVLine)) {
                    $m = max($m, $this->iObj[$i]->title->GetHeight($this->img));
                }
            }
        }
        return $m;
    }

    public function GetMaxBarAbsHeight()
    {
        $m = 0;
        if ($this->iObj != null) {
            $m = $this->iObj[0]->GetAbsHeight($this->img);
            $n = count($this->iObj);
            for ($i = 1; $i < $n; ++$i) {
                $m = max($m, $this->iObj[$i]->GetAbsHeight($this->img));
            }
        }
        return $m;
    }

    // Get the maximum used line number (vertical position) for bars
    public function GetBarMaxLineNumber()
    {
        $m = 1;
        if ($this->iObj != null) {
            $m = $this->iObj[0]->GetLineNbr();
            $n = count($this->iObj);
            for ($i = 1; $i < $n; ++$i) {
                $m = max($m, $this->iObj[$i]->GetLineNbr());
            }
        }
        return $m;
    }

    // Get the minumum and maximum used dates for all bars
    public function GetBarMinMax()
    {
        $start = 0;
        $n = count($this->iObj);
        while ($start < $n && $this->iObj[$start]->GetMaxDate() === false) {
            ++$start;
        }

        if ($start >= $n) {
            Util\JpGraphError::RaiseL(6006);
            //('Cannot autoscale Gantt chart. No dated activities exist. [GetBarMinMax() start >= n]');
        }

        $max = $this->scale->NormalizeDate($this->iObj[$start]->GetMaxDate());
        $min = $this->scale->NormalizeDate($this->iObj[$start]->GetMinDate());

        for ($i = $start + 1; $i < $n; ++$i) {
            $rmax = $this->scale->NormalizeDate($this->iObj[$i]->GetMaxDate());
            if ($rmax != false) {
                $max = Max($max, $rmax);
            }

            $rmin = $this->scale->NormalizeDate($this->iObj[$i]->GetMinDate());
            if ($rmin != false) {
                $min = Min($min, $rmin);
            }

        }
        $minDate = date("Y-m-d", $min);
        $min = strtotime($minDate);
        $maxDate = date("Y-m-d 23:59", $max);
        $max = strtotime($maxDate);
        return array($min, $max);
    }

    // Create a new auto sized canvas if the user hasn't specified a size
    // The size is determined by what scale the user has choosen and hence
    // the minimum width needed to display the headers. Some margins are
    // also added to make it better looking.
    public function AutoSize()
    {

        if ($this->img->img == null) {
            // The predefined left, right, top, bottom margins.
            // Note that the top margin might incease depending on
            // the title.
            $hadj = $vadj = 0;
            if ($this->doshadow) {
                $hadj = $this->shadow_width;
                $vadj = $this->shadow_width + 5;
            }

            $lm = $this->img->left_margin;
            $rm = $this->img->right_margin + $hadj;
            $rm += 2;
            $tm = $this->img->top_margin;
            $bm = $this->img->bottom_margin + $vadj;
            $bm += 2;

            // If there are any added GanttVLine we must make sure that the
            // bottom margin is wide enough to hold a title.
            $n = count($this->iObj);
            for ($i = 0; $i < $n; ++$i) {
                if ($this->iObj[$i] instanceof GanttVLine) {
                    $bm = max($bm, $this->iObj[$i]->title->GetHeight($this->img) + 10);
                }
            }

            // First find out the height
            $n = $this->GetBarMaxLineNumber() + 1;
            $m = max($this->GetMaxLabelHeight(), $this->GetMaxBarAbsHeight());
            $height = $n * ((1 + $this->iLabelVMarginFactor) * $m);

            // Add the height of the scale titles
            $h = $this->scale->GetHeaderHeight();
            $height += $h;

            // Calculate the top margin needed for title and subtitle
            if ($this->title->t != "") {
                $tm += $this->title->GetFontHeight($this->img);
            }
            if ($this->subtitle->t != "") {
                $tm += $this->subtitle->GetFontHeight($this->img);
            }

            // ...and then take the bottom and top plot margins into account
            $height += $tm + $bm + $this->scale->iTopPlotMargin + $this->scale->iBottomPlotMargin;
            // Now find the minimum width for the chart required

            // If day scale or smaller is shown then we use the day font width
            // as the base size unit.
            // If only weeks or above is displayed we use a modified unit to
            // get a smaller image.
            if ($this->scale->IsDisplayHour() || $this->scale->IsDisplayMinute()) {
                // Add 2 pixel margin on each side
                $fw = $this->scale->day->GetFontWidth($this->img) + 4;
            } elseif ($this->scale->IsDisplayWeek()) {
                $fw = 8;
            } elseif ($this->scale->IsDisplayMonth()) {
                $fw = 4;
            } else {
                $fw = 2;
            }

            $nd = $this->scale->GetNumberOfDays();

            if ($this->scale->IsDisplayDay()) {
                // If the days are displayed we also need to figure out
                // how much space each day's title will require.
                switch ($this->scale->day->iStyle) {
                    case DAYSTYLE_LONG:
                        $txt = "Monday";
                        break;
                    case DAYSTYLE_LONGDAYDATE1:
                        $txt = "Monday 23 Jun";
                        break;
                    case DAYSTYLE_LONGDAYDATE2:
                        $txt = "Monday 23 Jun 2003";
                        break;
                    case DAYSTYLE_SHORT:
                        $txt = "Mon";
                        break;
                    case DAYSTYLE_SHORTDAYDATE1:
                        $txt = "Mon 23/6";
                        break;
                    case DAYSTYLE_SHORTDAYDATE2:
                        $txt = "Mon 23 Jun";
                        break;
                    case DAYSTYLE_SHORTDAYDATE3:
                        $txt = "Mon 23";
                        break;
                    case DAYSTYLE_SHORTDATE1:
                        $txt = "23/6";
                        break;
                    case DAYSTYLE_SHORTDATE2:
                        $txt = "23 Jun";
                        break;
                    case DAYSTYLE_SHORTDATE3:
                        $txt = "Mon 23";
                        break;
                    case DAYSTYLE_SHORTDATE4:
                        $txt = "88";
                        break;
                    case DAYSTYLE_CUSTOM:
                        $txt = date($this->scale->day->iLabelFormStr, strtotime('2003-12-20 18:00'));
                        break;
                    case DAYSTYLE_ONELETTER:
                    default:
                        $txt = "M";
                        break;
                }
                $fw = $this->scale->day->GetStrWidth($this->img, $txt) + 6;
            }

            // If we have hours enabled we must make sure that each day has enough
            // space to fit the number of hours to be displayed.
            if ($this->scale->IsDisplayHour()) {
                // Depending on what format the user has choose we need different amount
                // of space. We therefore create a typical string for the choosen format
                // and determine the length of that string.
                switch ($this->scale->hour->iStyle) {
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
                        $txt = date($this->scale->hour->iLabelFormStr, strtotime('2003-12-20 18:00'));
                        break;
                    case HOURSTYLE_HM24:
                    default:
                        $txt = '24:00';
                        break;
                }

                $hfw = $this->scale->hour->GetStrWidth($this->img, $txt) + 6;
                $mw = $hfw;
                if ($this->scale->IsDisplayMinute()) {
                    // Depending on what format the user has choose we need different amount
                    // of space. We therefore create a typical string for the choosen format
                    // and determine the length of that string.
                    switch ($this->scale->minute->iStyle) {
                        case HOURSTYLE_CUSTOM:
                            $txt2 = date($this->scale->minute->iLabelFormStr, strtotime('2005-05-15 18:55'));
                            break;
                        case MINUTESTYLE_MM:
                        default:
                            $txt2 = '15';
                            break;
                    }

                    $mfw = $this->scale->minute->GetStrWidth($this->img, $txt2) + 6;
                    $n2 = ceil(60 / $this->scale->minute->GetIntervall());
                    $mw = $n2 * $mfw;
                }
                $hfw = $hfw < $mw ? $mw : $hfw;
                $n = ceil(24 * 60 / $this->scale->TimeToMinutes($this->scale->hour->GetIntervall()));
                $hw = $n * $hfw;
                $fw = $fw < $hw ? $hw : $fw;
            }

            // We need to repeat this code block here as well.
            // THIS iS NOT A MISTAKE !
            // We really need it since we need to adjust for minutes both in the case
            // where hour scale is shown and when it is not shown.

            if ($this->scale->IsDisplayMinute()) {
                // Depending on what format the user has choose we need different amount
                // of space. We therefore create a typical string for the choosen format
                // and determine the length of that string.
                switch ($this->scale->minute->iStyle) {
                    case HOURSTYLE_CUSTOM:
                        $txt = date($this->scale->minute->iLabelFormStr, strtotime('2005-05-15 18:55'));
                        break;
                    case MINUTESTYLE_MM:
                    default:
                        $txt = '15';
                        break;
                }

                $mfw = $this->scale->minute->GetStrWidth($this->img, $txt) + 6;
                $n = ceil(60 / $this->scale->TimeToMinutes($this->scale->minute->GetIntervall()));
                $mw = $n * $mfw;
                $fw = $fw < $mw ? $mw : $fw;
            }

            // If we display week we must make sure that 7*$fw is enough
            // to fit up to 10 characters of the week font (if the week is enabled)
            if ($this->scale->IsDisplayWeek()) {
                // Depending on what format the user has choose we need different amount
                // of space
                $fsw = strlen($this->scale->week->iLabelFormStr);
                if ($this->scale->week->iStyle == WEEKSTYLE_FIRSTDAY2WNBR) {
                    $fsw += 8;
                } elseif ($this->scale->week->iStyle == WEEKSTYLE_FIRSTDAYWNBR) {
                    $fsw += 7;
                } else {
                    $fsw += 4;
                }

                $ww = $fsw * $this->scale->week->GetFontWidth($this->img);
                if (7 * $fw < $ww) {
                    $fw = ceil($ww / 7);
                }
            }

            if (!$this->scale->IsDisplayDay() && !$this->scale->IsDisplayHour() &&
                !(($this->scale->week->iStyle == WEEKSTYLE_FIRSTDAYWNBR ||
                    $this->scale->week->iStyle == WEEKSTYLE_FIRSTDAY2WNBR) && $this->scale->IsDisplayWeek())) {
                // If we don't display the individual days we can shrink the
                // scale a little bit. This is a little bit pragmatic at the
                // moment and should be re-written to take into account
                // a) What scales exactly are shown and
                // b) what format do they use so we know how wide we need to
                // make each scale text space at minimum.
                $fw /= 2;
                if (!$this->scale->IsDisplayWeek()) {
                    $fw /= 1.8;
                }
            }

            $cw = $this->GetMaxActInfoColWidth();
            $this->scale->actinfo->SetMinColWidth($cw);
            if ($this->img->width <= 0) {
                // Now determine the width for the activity titles column

                // Firdst find out the maximum width of each object column
                $titlewidth = max(max($this->GetMaxLabelWidth(),
                    $this->scale->tableTitle->GetWidth($this->img)),
                    $this->scale->actinfo->GetWidth($this->img));

                // Add the width of the vertivcal divider line
                $titlewidth += $this->scale->divider->iWeight * 2;

                // Adjust the width by the user specified zoom factor
                $fw *= $this->iZoomFactor;

                // Now get the total width taking
                // titlewidth, left and rigt margin, dayfont size
                // into account
                $width = $titlewidth + $nd * $fw + $lm + $rm;
            } else {
                $width = $this->img->width;
            }

            $width = round($width);
            $height = round($height);
            // Make a sanity check on image size
            if ($width > MAX_GANTTIMG_SIZE_W || $height > MAX_GANTTIMG_SIZE_H) {
                Util\JpGraphError::RaiseL(6007, $width, $height);
                //("Sanity check for automatic Gantt chart size failed. Either the width (=$width) or height (=$height) is larger than MAX_GANTTIMG_SIZE. This could potentially be caused by a wrong date in one of the activities.");
            }
            $this->img->CreateImgCanvas($width, $height);
            $this->img->SetMargin($lm, $rm, $tm, $bm);
        }
    }

    // Return an array width the maximum width for each activity
    // column. This is used when we autosize the columns where we need
    // to find out the maximum width of each column. In order to do that we
    // must walk through all the objects, sigh...
    public function GetMaxActInfoColWidth()
    {
        $n = count($this->iObj);
        if ($n == 0) {
            return;
        }

        $w = array();
        $m = $this->scale->actinfo->iLeftColMargin + $this->scale->actinfo->iRightColMargin;

        for ($i = 0; $i < $n; ++$i) {
            $tmp = $this->iObj[$i]->title->GetColWidth($this->img, $m);
            $nn = count($tmp);
            for ($j = 0; $j < $nn; ++$j) {
                if (empty($w[$j])) {
                    $w[$j] = $tmp[$j];
                } else {
                    $w[$j] = max($w[$j], $tmp[$j]);
                }

            }
        }
        return $w;
    }

    // Stroke the gantt chart
    public function Stroke($aStrokeFileName = "")
    {

        // If the filename is the predefined value = '_csim_special_'
        // we assume that the call to stroke only needs to do enough
        // to correctly generate the CSIM maps.
        // We use this variable to skip things we don't strictly need
        // to do to generate the image map to improve performance
        // a best we can. Therefor you will see a lot of tests !$_csim in the
        // code below.
        $_csim = ($aStrokeFileName === _CSIM_SPECIALFILE);

        // Should we autoscale dates?

        if (!$this->scale->IsRangeSet()) {
            list($min, $max) = $this->GetBarMinMax();
            $this->scale->SetRange($min, $max);
        }

        $this->scale->AdjustStartEndDay();

        // Check if we should autoscale the image
        $this->AutoSize();

        // Should we start from the top or just spread the bars out even over the
        // available height
        $this->scale->SetVertLayout($this->iLayout);
        if ($this->iLayout == GANTT_FROMTOP) {
            $maxheight = max($this->GetMaxLabelHeight(), $this->GetMaxBarAbsHeight());
            $this->scale->SetVertSpacing($maxheight * (1 + $this->iLabelVMarginFactor));
        }
        // If it hasn't been set find out the maximum line number
        if ($this->scale->iVertLines == -1) {
            $this->scale->iVertLines = $this->GetBarMaxLineNumber() + 1;
        }

        $maxwidth = max($this->scale->actinfo->GetWidth($this->img),
            max($this->GetMaxLabelWidth(),
                $this->scale->tableTitle->GetWidth($this->img)));

        $this->scale->SetLabelWidth($maxwidth + $this->scale->divider->iWeight); //*(1+$this->iLabelHMarginFactor));

        if (!$_csim) {
            $this->StrokePlotArea();
            if ($this->iIconDepth == DEPTH_BACK) {
                $this->StrokeIcons();
            }
        }

        $this->scale->Stroke();

        if (!$_csim) {
            // Due to a minor off by 1 bug we need to temporarily adjust the margin
            $this->img->right_margin--;
            $this->StrokePlotBox();
            $this->img->right_margin++;
        }

        // Stroke Grid line
        $this->hgrid->Stroke($this->img, $this->scale);

        $n = count($this->iObj);
        for ($i = 0; $i < $n; ++$i) {
            //$this->iObj[$i]->SetLabelLeftMargin(round($maxwidth*$this->iLabelHMarginFactor/2));
            $this->iObj[$i]->Stroke($this->img, $this->scale);
        }

        $this->StrokeTitles();

        if (!$_csim) {
            $this->StrokeConstrains();
            $this->footer->Stroke($this->img);

            if ($this->iIconDepth == DEPTH_FRONT) {
                $this->StrokeIcons();
            }

            // Stroke all added user texts
            $this->StrokeTexts();

            // Should we do any final image transformation
            if ($this->iImgTrans) {
                if (!class_exists('ImgTrans', false)) {
                    require_once 'jpgraph_imgtrans.php';
                }

                $tform = new ImgTrans($this->img->img);
                $this->img->img = $tform->Skew3D($this->iImgTransHorizon, $this->iImgTransSkewDist,
                    $this->iImgTransDirection, $this->iImgTransHighQ,
                    $this->iImgTransMinSize, $this->iImgTransFillColor,
                    $this->iImgTransBorder);
            }

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
    }

    public function StrokeConstrains()
    {
        $n = count($this->iObj);

        // Stroke all constrains
        for ($i = 0; $i < $n; ++$i) {

            // Some gantt objects may not have constraints associated with them
            // for example we can add IconPlots which doesn't have this property.
            if (empty($this->iObj[$i]->constraints)) {
                continue;
            }

            $numConstrains = count($this->iObj[$i]->constraints);

            for ($k = 0; $k < $numConstrains; $k++) {
                $vpos = $this->iObj[$i]->constraints[$k]->iConstrainRow;
                if ($vpos >= 0) {
                    $c1 = $this->iObj[$i]->iConstrainPos;

                    // Find out which object is on the target row
                    $targetobj = -1;
                    for ($j = 0; $j < $n && $targetobj == -1; ++$j) {
                        if ($this->iObj[$j]->iVPos == $vpos) {
                            $targetobj = $j;
                        }
                    }
                    if ($targetobj == -1) {
                        Util\JpGraphError::RaiseL(6008, $this->iObj[$i]->iVPos, $vpos);
                        //('You have specifed a constrain from row='.$this->iObj[$i]->iVPos.' to row='.$vpos.' which does not have any activity.');
                    }
                    $c2 = $this->iObj[$targetobj]->iConstrainPos;
                    if (count($c1) == 4 && count($c2) == 4) {
                        switch ($this->iObj[$i]->constraints[$k]->iConstrainType) {
                            case CONSTRAIN_ENDSTART:
                                if ($c1[1] < $c2[1]) {
                                    $link = new GanttLink($c1[2], $c1[3], $c2[0], $c2[1]);
                                } else {
                                    $link = new GanttLink($c1[2], $c1[1], $c2[0], $c2[3]);
                                }
                                $link->SetPath(3);
                                break;
                            case CONSTRAIN_STARTEND:
                                if ($c1[1] < $c2[1]) {
                                    $link = new GanttLink($c1[0], $c1[3], $c2[2], $c2[1]);
                                } else {
                                    $link = new GanttLink($c1[0], $c1[1], $c2[2], $c2[3]);
                                }
                                $link->SetPath(0);
                                break;
                            case CONSTRAIN_ENDEND:
                                if ($c1[1] < $c2[1]) {
                                    $link = new GanttLink($c1[2], $c1[3], $c2[2], $c2[1]);
                                } else {
                                    $link = new GanttLink($c1[2], $c1[1], $c2[2], $c2[3]);
                                }
                                $link->SetPath(1);
                                break;
                            case CONSTRAIN_STARTSTART:
                                if ($c1[1] < $c2[1]) {
                                    $link = new GanttLink($c1[0], $c1[3], $c2[0], $c2[1]);
                                } else {
                                    $link = new GanttLink($c1[0], $c1[1], $c2[0], $c2[3]);
                                }
                                $link->SetPath(3);
                                break;
                            default:
                                Util\JpGraphError::RaiseL(6009, $this->iObj[$i]->iVPos, $vpos);
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

    public function GetCSIMAreas()
    {
        if (!$this->iHasStroked) {
            $this->Stroke(_CSIM_SPECIALFILE);
        }

        $csim = $this->title->GetCSIMAreas();
        $csim .= $this->subtitle->GetCSIMAreas();
        $csim .= $this->subsubtitle->GetCSIMAreas();

        $n = count($this->iObj);
        for ($i = $n - 1; $i >= 0; --$i) {
            $csim .= $this->iObj[$i]->GetCSIMArea();
        }

        return $csim;
    }
}
