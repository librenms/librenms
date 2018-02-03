<?php
namespace Amenadiel\JpGraph\Text;

//---------------------------------------------------------------------
// CLASS GTextTable
// Description:
// Graphic text table
//---------------------------------------------------------------------
class GTextTable
{
    public $iCells         = array();
    public $iSize          = array(0, 0); // Need to be public since they are used by the cell
    private $iWidth        = 0;
    private $iHeight       = 0;
    private $iColWidth     = null;
    private $iRowHeight    = null;
    private $iImg          = null;
    private $iXPos         = 0;
    private $iYPos         = 0;
    private $iScaleXPos    = null;
    private $iScaleYPos    = null;
    private $iBGColor      = '';
    private $iBorderColor  = 'black';
    private $iBorderWeight = 1;
    private $iInit         = false;
    private $iYAnchor      = 'top';
    private $iXAnchor      = 'left';
    /*-----------------------------------------------------------------
     * First and second phase constructors
     *-----------------------------------------------------------------
     */
    public function __construct()
    {
        // Empty
    }

    public function Init($aRows = 0, $aCols = 0, $aFillText = '')
    {
        $this->iSize[0] = $aRows;
        $this->iSize[1] = $aCols;
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            for ($j = 0; $j < $this->iSize[1]; ++$j) {
                $this->iCells[$i][$j] = new GTextTableCell($aFillText, $i, $j);
                $this->iCells[$i][$j]->Init($this);
            }
        }
        $this->iInit = true;
    }

    /*-----------------------------------------------------------------
     * Outer border of table
     *-----------------------------------------------------------------
     */
    public function SetBorder($aWeight = 1, $aColor = 'black')
    {
        $this->iBorderColor  = $aColor;
        $this->iBorderWeight = $aWeight;
    }

    /*-----------------------------------------------------------------
     * Position in graph of table
     *-----------------------------------------------------------------
     */
    public function SetPos($aX, $aY)
    {
        $this->iXPos = $aX;
        $this->iYPos = $aY;
    }

    public function SetScalePos($aX, $aY)
    {
        $this->iScaleXPos = $aX;
        $this->iScaleYPos = $aY;
    }

    public function SetAnchorPos($aXAnchor, $aYAnchor = 'top')
    {
        $this->iXAnchor = $aXAnchor;
        $this->iYAnchor = $aYAnchor;
    }

    /*-----------------------------------------------------------------
     * Setup country flag in a cell
     *-----------------------------------------------------------------
     */
    public function SetCellCountryFlag($aRow, $aCol, $aFlag, $aScale = 1.0, $aMix = 100, $aStdSize = 3)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetCountryFlag($aFlag, $aScale, $aMix, $aStdSize);
    }

    /*-----------------------------------------------------------------
     * Setup image in a cell
     *-----------------------------------------------------------------
     */
    public function SetCellImage($aRow, $aCol, $aFile, $aScale = 1.0, $aMix = 100)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetImage($aFile, $aScale, $aMix);
    }

    public function SetRowImage($aRow, $aFile, $aScale = 1.0, $aMix = 100)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetImage($aFile, $aScale, $aMix);
        }
    }

    public function SetColImage($aCol, $aFile, $aScale = 1.0, $aMix = 100)
    {
        $this->_chkC($aCol);
        for ($j = 0; $j < $this->iSize[0]; ++$j) {
            $this->iCells[$j][$aCol]->SetImage($aFile, $aScale, $aMix);
        }
    }

    public function SetImage($aFileR1, $aScaleC1 = null, $aMixR2 = null, $aC2 = null, $aFile = null, $aScale = 1.0, $aMix = 100)
    {
        if ($aScaleC1 !== null && $aMixR2 !== null && $aC2 !== null && $aFile !== null) {
            $this->_chkR($aArgR1);
            $this->_chkC($aC1);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
        } else {
            if ($aScaleC1 !== null) {
                $aScale = $aScaleC1;
            }

            if ($aMixR2 !== null) {
                $aMix = $aMixR2;
            }

            $aFile    = $aFileR1;
            $aMixR2   = $this->iSize[0] - 1;
            $aFileR1  = 0;
            $aC2      = $this->iSize[1] - 1;
            $aScaleC1 = 0;
        }
        for ($i = $aArgR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->SetImage($aFile, $aScale, $aMix);
            }
        }
    }

    public function SetCellImageConstrain($aRow, $aCol, $aType, $aVal)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetImageConstrain($aType, $aVal);
    }

    /*-----------------------------------------------------------------
     * Generate a HTML version of the table
     *-----------------------------------------------------------------
     */
    public function toString()
    {
        $t = '<table border=1 cellspacing=0 cellpadding=0>';
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $t .= '<tr>';
            for ($j = 0; $j < $this->iSize[1]; ++$j) {
                $t .= '<td>';
                if ($this->iCells[$i][$j]->iMerged) {
                    $t .= 'M ';
                }

                $t .= 'val=' . $this->iCells[$i][$j]->iVal->t;
                $t .= ' (cs=' . $this->iCells[$i][$j]->iColSpan .
                ', rs=' . $this->iCells[$i][$j]->iRowSpan . ')';
                $t .= '</td>';
            }
            $t .= '</tr>';
        }
        $t .= '</table>';
        return $t;
    }

    /*-----------------------------------------------------------------
     * Specify data for table
     *-----------------------------------------------------------------
     */
    public function Set($aArg1, $aArg2 = null, $aArg3 = null)
    {
        if ($aArg2 === null && $aArg3 === null) {
            if (is_array($aArg1)) {
                if (is_array($aArg1[0])) {
                    $m = count($aArg1);
                    // Find the longest row
                    $n = 0;
                    for ($i = 0; $i < $m; ++$i) {
                        $n = max(count($aArg1[$i]), $n);
                    }

                    for ($i = 0; $i < $m; ++$i) {
                        for ($j = 0; $j < $n; ++$j) {
                            if (isset($aArg1[$i][$j])) {
                                $this->_setcell($i, $j, (string) $aArg1[$i][$j]);
                            } else {
                                $this->_setcell($i, $j);
                            }
                        }
                    }
                    $this->iSize[0] = $m;
                    $this->iSize[1] = $n;
                    $this->iInit    = true;
                } else {
                    JpGraphError::RaiseL(27001);
                    //('Illegal argument to GTextTable::Set(). Array must be 2 dimensional');
                }
            } else {
                JpGraphError::RaiseL(27002);
                //('Illegal argument to GTextTable::Set()');
            }
        } else {
            // Must be in the form (row,col,val)
            $this->_chkR($aArg1);
            $this->_chkC($aArg2);
            $this->_setcell($aArg1, $aArg2, (string) $aArg3);
        }
    }

    /*---------------------------------------------------------------------
     * Cell margin setting
     *---------------------------------------------------------------------
     */
    public function SetPadding($aArgR1, $aC1 = null, $aR2 = null, $aC2 = null, $aPad = null)
    {
        if ($aC1 !== null && $aR2 !== null && $aC2 !== null && $aPad !== null) {
            $this->_chkR($aArgR1);
            $this->_chkC($aC1);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
        } else {
            $aPad   = $aArgR1;
            $aR2    = $this->iSize[0] - 1;
            $aArgR1 = 0;
            $aC2    = $this->iSize[1] - 1;
            $aC1    = 0;
        }
        for ($i = $aArgR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->SetMargin($aPad, $aPad, $aPad, $aPad);
            }
        }
    }

    public function SetRowPadding($aRow, $aPad)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetMargin($aPad, $aPad, $aPad, $aPad);
        }
    }

    public function SetColPadding($aCol, $aPad)
    {
        $this->_chkC($aCol);
        for ($j = 0; $j < $this->iSize[0]; ++$j) {
            $this->iCells[$j][$aCol]->SetMargin($aPad, $aPad, $aPad, $aPad);
        }
    }

    public function SetCellPadding($aRow, $aCol, $aPad)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetMargin($aPad, $aPad, $aPad, $aPad);
    }

    /*---------------------------------------------------------------------
     * Cell text orientation setting
     *---------------------------------------------------------------------
     */
    public function SetTextOrientation($aArgR1, $aC1 = null, $aR2 = null, $aC2 = null, $aO = null)
    {
        if ($aC1 !== null && $aR2 !== null && $aC2 !== null && $aPad !== null) {
            $this->_chkR($aArgR1);
            $this->_chkC($aC1);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
        } else {
            $aO     = $aArgR1;
            $aR2    = $this->iSize[0] - 1;
            $aArgR1 = 0;
            $aC2    = $this->iSize[1] - 1;
            $aC1    = 0;
        }
        for ($i = $aArgR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->iVal->SetOrientation($aO);
            }
        }
    }

    public function SetRowTextOrientation($aRow, $aO)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->iVal->SetOrientation($aO);
        }
    }

    public function SetColTextOrientation($aCol, $aO)
    {
        $this->_chkC($aCol);
        for ($j = 0; $j < $this->iSize[0]; ++$j) {
            $this->iCells[$j][$aCol]->iVal->SetOrientation($aO);
        }
    }

    public function SetCellTextOrientation($aRow, $aCol, $aO)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->iVal->SetOrientation($aO);
    }

    /*---------------------------------------------------------------------
     * Font color setting
     *---------------------------------------------------------------------
     */

    public function SetColor($aArgR1, $aC1 = null, $aR2 = null, $aC2 = null, $aArg = null)
    {
        if ($aC1 !== null && $aR2 !== null && $aC2 !== null && $aArg !== null) {
            $this->_chkR($aArgR1);
            $this->_chkC($aC1);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
        } else {
            $aArg   = $aArgR1;
            $aR2    = $this->iSize[0] - 1;
            $aArgR1 = 0;
            $aC2    = $this->iSize[1] - 1;
            $aC1    = 0;
        }
        for ($i = $aArgR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->SetFontColor($aArg);
            }
        }
    }

    public function SetRowColor($aRow, $aColor)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetFontColor($aColor);
        }
    }

    public function SetColColor($aCol, $aColor)
    {
        $this->_chkC($aCol);
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $this->iCells[$i][$aCol]->SetFontColor($aColor);
        }
    }

    public function SetCellColor($aRow, $aCol, $aColor)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetFontColor($aColor);
    }

    /*---------------------------------------------------------------------
     * Fill color settings
     *---------------------------------------------------------------------
     */

    public function SetFillColor($aArgR1, $aC1 = null, $aR2 = null, $aC2 = null, $aArg = null)
    {
        if ($aC1 !== null && $aR2 !== null && $aC2 !== null && $aArg !== null) {
            $this->_chkR($aArgR1);
            $this->_chkC($aC1);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
            for ($i = $aArgR1; $i <= $aR2; ++$i) {
                for ($j = $aC1; $j <= $aC2; ++$j) {
                    $this->iCells[$i][$j]->SetFillColor($aArg);
                }
            }
        } else {
            $this->iBGColor = $aArgR1;
        }
    }

    public function SetRowFillColor($aRow, $aColor)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetFillColor($aColor);
        }
    }

    public function SetColFillColor($aCol, $aColor)
    {
        $this->_chkC($aCol);
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $this->iCells[$i][$aCol]->SetFillColor($aColor);
        }
    }

    public function SetCellFillColor($aRow, $aCol, $aColor)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetFillColor($aColor);
    }

    /*---------------------------------------------------------------------
     * Font family setting
     *---------------------------------------------------------------------
     */
    public function SetFont()
    {
        $numargs = func_num_args();
        if ($numargs == 2 || $numargs == 3) {
            $aFF = func_get_arg(0);
            $aFS = func_get_arg(1);
            if ($numargs == 3) {
                $aFSize = func_get_arg(2);
            } else {
                $aFSize = 10;
            }

            $aR2 = $this->iSize[0] - 1;
            $aR1 = 0;
            $aC2 = $this->iSize[1] - 1;
            $aC1 = 0;
        } elseif ($numargs == 6 || $numargs == 7) {
            $aR1 = func_get_arg(0);
            $aC1 = func_get_arg(1);
            $aR2 = func_get_arg(2);
            $aC2 = func_get_arg(3);
            $aFF = func_get_arg(4);
            $aFS = func_get_arg(5);
            if ($numargs == 7) {
                $aFSize = func_get_arg(6);
            } else {
                $aFSize = 10;
            }
        } else {
            JpGraphError::RaiseL(27003);
            //('Wrong number of arguments to GTextTable::SetColor()');
        }
        $this->_chkR($aR1);
        $this->_chkC($aC1);
        $this->_chkR($aR2);
        $this->_chkC($aC2);
        for ($i = $aR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->SetFont($aFF, $aFS, $aFSize);
            }
        }
    }

    public function SetRowFont($aRow, $aFF, $aFS, $aFSize = 10)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetFont($aFF, $aFS, $aFSize);
        }
    }

    public function SetColFont($aCol, $aFF, $aFS, $aFSize = 10)
    {
        $this->_chkC($aCol);
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $this->iCells[$i][$aCol]->SetFont($aFF, $aFS, $aFSize);
        }
    }

    public function SetCellFont($aRow, $aCol, $aFF, $aFS, $aFSize = 10)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetFont($aFF, $aFS, $aFSize);
    }

    /*---------------------------------------------------------------------
     * Cell align settings
     *---------------------------------------------------------------------
     */

    public function SetAlign($aR1HAlign = null, $aC1VAlign = null, $aR2 = null, $aC2 = null, $aHArg = null, $aVArg = 'center')
    {
        if ($aC1VAlign !== null && $aR2 !== null && $aC2 !== null && $aHArg !== null) {
            $this->_chkR($aR1HAlign);
            $this->_chkC($aC1VAlign);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
        } else {
            if ($aR1HAlign === null) {
                JpGraphError::RaiseL(27010);
            }
            if ($aC1VAlign === null) {
                $aC1VAlign = 'center';
            }
            $aHArg     = $aR1HAlign;
            $aVArg     = $aC1VAlign === null ? 'center' : $aC1VAlign;
            $aR2       = $this->iSize[0] - 1;
            $aR1HAlign = 0;
            $aC2       = $this->iSize[1] - 1;
            $aC1VAlign = 0;
        }
        for ($i = $aR1HAlign; $i <= $aR2; ++$i) {
            for ($j = $aC1VAlign; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->SetAlign($aHArg, $aVArg);
            }
        }
    }

    public function SetCellAlign($aRow, $aCol, $aHorAlign, $aVertAlign = 'bottom')
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetAlign($aHorAlign, $aVertAlign);
    }

    public function SetRowAlign($aRow, $aHorAlign, $aVertAlign = 'bottom')
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetAlign($aHorAlign, $aVertAlign);
        }
    }

    public function SetColAlign($aCol, $aHorAlign, $aVertAlign = 'bottom')
    {
        $this->_chkC($aCol);
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $this->iCells[$i][$aCol]->SetAlign($aHorAlign, $aVertAlign);
        }
    }

    /*---------------------------------------------------------------------
     * Cell number format
     *---------------------------------------------------------------------
     */

    public function SetNumberFormat($aArgR1, $aC1 = null, $aR2 = null, $aC2 = null, $aArg = null)
    {
        if ($aC1 !== null && $aR2 !== null && $aC2 !== null && $aArg !== null) {
            $this->_chkR($aArgR1);
            $this->_chkC($aC1);
            $this->_chkR($aR2);
            $this->_chkC($aC2);
        } else {
            $aArg   = $aArgR1;
            $aR2    = $this->iSize[0] - 1;
            $aArgR1 = 0;
            $aC2    = $this->iSize[1] - 1;
            $aC1    = 0;
        }
        if (!is_string($aArg)) {
            JpGraphError::RaiseL(27013); // argument must be a string
        }
        for ($i = $aArgR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                $this->iCells[$i][$j]->SetNumberFormat($aArg);
            }
        }
    }

    public function SetRowNumberFormat($aRow, $aF)
    {
        $this->_chkR($aRow);
        if (!is_string($aF)) {
            JpGraphError::RaiseL(27013); // argument must be a string
        }
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetNumberFormat($aF);
        }
    }

    public function SetColNumberFormat($aCol, $aF)
    {
        $this->_chkC($aCol);
        if (!is_string($aF)) {
            JpGraphError::RaiseL(27013); // argument must be a string
        }
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $this->iCells[$i][$aCol]->SetNumberFormat($aF);
        }
    }

    public function SetCellNumberFormat($aRow, $aCol, $aF)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        if (!is_string($aF)) {
            JpGraphError::RaiseL(27013); // argument must be a string
        }
        $this->iCells[$aRow][$aCol]->SetNumberFormat($aF);
    }

    /*---------------------------------------------------------------------
     * Set row and column min size
     *---------------------------------------------------------------------
     */

    public function SetMinColWidth($aColWidth, $aWidth = null)
    {
        // If there is only one argument this means that all
        // columns get set to the same width
        if ($aWidth === null) {
            for ($i = 0; $i < $this->iSize[1]; ++$i) {
                $this->iColWidth[$i] = $aColWidth;
            }
        } else {
            $this->_chkC($aColWidth);
            $this->iColWidth[$aColWidth] = $aWidth;
        }
    }

    public function SetMinRowHeight($aRowHeight, $aHeight = null)
    {
        // If there is only one argument this means that all
        // rows get set to the same height
        if ($aHeight === null) {
            for ($i = 0; $i < $this->iSize[0]; ++$i) {
                $this->iRowHeight[$i] = $aRowHeight;
            }
        } else {
            $this->_chkR($aRowHeight);
            $this->iRowHeight[$aRowHeight] = $aHeight;
        }
    }

    /*---------------------------------------------------------------------
     * Grid line settings
     *---------------------------------------------------------------------
     */

    public function SetGrid($aWeight = 1, $aColor = 'black', $aStyle = TGRID_SINGLE)
    {
        $rc = $this->iSize[0];
        $cc = $this->iSize[1];
        for ($i = 0; $i < $rc; ++$i) {
            for ($j = 0; $j < $cc; ++$j) {
                $this->iCells[$i][$j]->SetGridColor($aColor, $aColor);
                $this->iCells[$i][$j]->SetGridWeight($aWeight, $aWeight);
                $this->iCells[$i][$j]->SetGridStyle($aStyle);
            }
        }
    }

    public function SetColGrid($aCol, $aWeight = 1, $aColor = 'black', $aStyle = TGRID_SINGLE)
    {
        $this->_chkC($aCol);
        for ($i = 0; $i < $this->iSize[0]; ++$i) {
            $this->iCells[$i][$aCol]->SetGridWeight($aWeight);
            $this->iCells[$i][$aCol]->SetGridColor($aColor);
            $this->iCells[$i][$aCol]->SetGridStyle($aStyle);
        }
    }

    public function SetRowGrid($aRow, $aWeight = 1, $aColor = 'black', $aStyle = TGRID_SINGLE)
    {
        $this->_chkR($aRow);
        for ($j = 0; $j < $this->iSize[1]; ++$j) {
            $this->iCells[$aRow][$j]->SetGridWeight(null, $aWeight);
            $this->iCells[$aRow][$j]->SetGridColor(null, $aColor);
            $this->iCells[$aRow][$j]->SetGridStyle(null, $aStyle);
        }
    }

    /*---------------------------------------------------------------------
     * Merge cells
     *---------------------------------------------------------------------
     */

    public function MergeRow($aRow, $aHAlign = 'center', $aVAlign = 'center')
    {
        $this->_chkR($aRow);
        $this->MergeCells($aRow, 0, $aRow, $this->iSize[1] - 1, $aHAlign, $aVAlign);
    }

    public function MergeCol($aCol, $aHAlign = 'center', $aVAlign = 'center')
    {
        $this->_chkC($aCol);
        $this->MergeCells(0, $aCol, $this->iSize[0] - 1, $aCol, $aHAlign, $aVAlign);
    }

    public function MergeCells($aR1, $aC1, $aR2, $aC2, $aHAlign = 'center', $aVAlign = 'center')
    {
        if ($aR1 > $aR2 || $aC1 > $aC2) {
            JpGraphError::RaiseL(27004);
            //('GTextTable::MergeCells(). Specified cell range to be merged is not valid.');
        }
        $this->_chkR($aR1);
        $this->_chkC($aC1);
        $this->_chkR($aR2);
        $this->_chkC($aC2);
        $rspan = $aR2 - $aR1 + 1;
        $cspan = $aC2 - $aC1 + 1;
        // Setup the parent cell for this merged group
        if ($this->iCells[$aR1][$aC1]->IsMerged()) {
            JpGraphError::RaiseL(27005, $aR1, $aC1, $aR2, $aC2);
            //("Cannot merge already merged cells in the range ($aR1,$aC1), ($aR2,$aC2)");
        }
        $this->iCells[$aR1][$aC1]->SetRowColSpan($rspan, $cspan);
        $this->iCells[$aR1][$aC1]->SetAlign($aHAlign, $aVAlign);
        for ($i = $aR1; $i <= $aR2; ++$i) {
            for ($j = $aC1; $j <= $aC2; ++$j) {
                if (!($i == $aR1 && $j == $aC1)) {
                    if ($this->iCells[$i][$j]->IsMerged()) {
                        JpGraphError::RaiseL(27005, $aR1, $aC1, $aR2, $aC2);
                        //("Cannot merge already merged cells in the range ($aR1,$aC1), ($aR2,$aC2)");
                    }
                    $this->iCells[$i][$j]->SetMerged($aR1, $aC1, true);
                }
            }
        }
    }

    /*---------------------------------------------------------------------
     * CSIM methods
     *---------------------------------------------------------------------
     */

    public function SetCSIMTarget($aTarget, $aAlt = null, $aAutoTarget = false)
    {
        $m    = $this->iSize[0];
        $n    = $this->iSize[1];
        $csim = '';
        for ($i = 0; $i < $m; ++$i) {
            for ($j = 0; $j < $n; ++$j) {
                if ($aAutoTarget) {
                    $t = $aTarget . "?row=$i&col=$j";
                } else {
                    $t = $aTarget;
                }

                $this->iCells[$i][$j]->SetCSIMTarget($t, $aAlt);
            }
        }
    }

    public function SetCellCSIMTarget($aRow, $aCol, $aTarget, $aAlt = null)
    {
        $this->_chkR($aRow);
        $this->_chkC($aCol);
        $this->iCells[$aRow][$aCol]->SetCSIMTarget($aTarget, $aAlt);
    }

    /*---------------------------------------------------------------------
     * Private methods
     *---------------------------------------------------------------------
     */

    public function GetCSIMAreas()
    {
        $m    = $this->iSize[0];
        $n    = $this->iSize[1];
        $csim = '';
        for ($i = 0; $i < $m; ++$i) {
            for ($j = 0; $j < $n; ++$j) {
                $csim .= $this->iCells[$i][$j]->GetCSIMArea();
            }
        }
        return $csim;
    }

    public function _chkC($aCol)
    {
        if (!$this->iInit) {
            JpGraphError::Raise(27014); // Table not initialized
        }
        if ($aCol < 0 || $aCol >= $this->iSize[1]) {
            JpGraphError::RaiseL(27006, $aCol);
        }

        //("GTextTable:\nColumn argument ($aCol) is outside specified table size.");
    }

    public function _chkR($aRow)
    {
        if (!$this->iInit) {
            JpGraphError::Raise(27014); // Table not initialized
        }
        if ($aRow < 0 || $aRow >= $this->iSize[0]) {
            JpGraphError::RaiseL(27007, $aRow);
        }

        //("GTextTable:\nRow argument ($aRow) is outside specified table size.");
    }

    public function _getScalePos()
    {
        if ($this->iScaleXPos === null || $this->iScaleYPos === null) {
            return false;
        }
        return array($this->iScaleXPos, $this->iScaleYPos);
    }

    public function _autoSizeTable($aImg)
    {
        // Get maximum column width and row height
        $m = $this->iSize[0];
        $n = $this->iSize[1];
        $w = 1;
        $h = 1;

        // Get maximum row height per row
        for ($i = 0; $i < $m; ++$i) {
            $h = 0;
            for ($j = 0; $j < $n; ++$j) {
                $h = max($h, $this->iCells[$i][$j]->GetHeight($aImg));
            }
            if (isset($this->iRowHeight[$i])) {
                $this->iRowHeight[$i] = max($h, $this->iRowHeight[$i]);
            } else {
                $this->iRowHeight[$i] = $h;
            }
        }

        // Get maximum col width per columns
        for ($j = 0; $j < $n; ++$j) {
            $w = 0;
            for ($i = 0; $i < $m; ++$i) {
                $w = max($w, $this->iCells[$i][$j]->GetWidth($aImg));
            }
            if (isset($this->iColWidth[$j])) {
                $this->iColWidth[$j] = max($w, $this->iColWidth[$j]);
            } else {
                $this->iColWidth[$j] = $w;
            }
        }
    }

    public function _setcell($aRow, $aCol, $aVal = '')
    {
        if (isset($this->iCells[$aRow][$aCol])) {
            $this->iCells[$aRow][$aCol]->Set($aVal);
        } else {
            $this->iCells[$aRow][$aCol] = new GTextTableCell((string) $aVal, $aRow, $aCol);
            $this->iCells[$aRow][$aCol]->Init($this);
        }
    }

    public function StrokeWithScale($aImg, $aXScale, $aYScale)
    {
        if (is_numeric($this->iScaleXPos) && is_numeric($this->iScaleYPos)) {
            $x = round($aXScale->Translate($this->iScaleXPos));
            $y = round($aYScale->Translate($this->iScaleYPos));
            $this->Stroke($aImg, $x, $y);
        } else {
            $this->Stroke($aImg);
        }
    }

    public function Stroke($aImg, $aX = null, $aY = null)
    {
        if ($aX !== null && $aY !== null) {
            $this->iXPos = $aX;
            $this->iYPos = $aY;
        }

        $rc = $this->iSize[0]; // row count
        $cc = $this->iSize[1]; // column count

        if ($rc == 0 || $cc == 0) {
            JpGraphError::RaiseL(27009);
        }

        // Adjust margins of each cell based on the weight of the grid. Each table grid line
        // is actually occupying the left side and top part of each cell.
        for ($j = 0; $j < $cc; ++$j) {
            $this->iCells[0][$j]->iMarginTop += $this->iBorderWeight;
        }
        for ($i = 0; $i < $rc; ++$i) {
            $this->iCells[$i][0]->iMarginLeft += $this->iBorderWeight;
        }
        for ($i = 0; $i < $rc; ++$i) {
            for ($j = 0; $j < $cc; ++$j) {
                $this->iCells[$i][$j]->AdjustMarginsForGrid();
            }
        }

        // adjust row and column size depending on cell content
        $this->_autoSizeTable($aImg);

        if ($this->iSize[1] != count($this->iColWidth) || $this->iSize[0] != count($this->iRowHeight)) {
            JpGraphError::RaiseL(27008);
            //('Column and row size arrays must match the dimesnions of the table');
        }

        // Find out overall table size
        $width = 0;
        for ($i = 0; $i < $cc; ++$i) {
            $width += $this->iColWidth[$i];
        }
        $height = 0;
        for ($i = 0; $i < $rc; ++$i) {
            $height += $this->iRowHeight[$i];
        }

        // Adjust the X,Y position to alway be at the top left corner
        // The anchor position, i.e. how the client want to interpret the specified
        // x and y coordinate must be taken into account
        switch (strtolower($this->iXAnchor)) {
            case 'left':
                break;
            case 'center':
                $this->iXPos -= round($width / 2);
                break;
            case 'right':
                $this->iXPos -= $width;
                break;
        }
        switch (strtolower($this->iYAnchor)) {
            case 'top':
                break;
            case 'center':
            case 'middle':
                $this->iYPos -= round($height / 2);
                break;
            case 'bottom':
                $this->iYPos -= $height;
                break;
        }

        // Set the overall background color of the table if set
        if ($this->iBGColor !== '') {
            $aImg->SetColor($this->iBGColor);
            $aImg->FilledRectangle($this->iXPos, $this->iYPos, $this->iXPos + $width, $this->iYPos + $height);
        }

        // Stroke all cells
        $rpos = $this->iYPos;
        for ($i = 0; $i < $rc; ++$i) {
            $cpos = $this->iXPos;
            for ($j = 0; $j < $cc; ++$j) {
                // Calculate width and height of this cell if it is spanning
                // more than one column or row
                $cwidth = 0;
                for ($k = 0; $k < $this->iCells[$i][$j]->iColSpan; ++$k) {
                    $cwidth += $this->iColWidth[$j + $k];
                }
                $cheight = 0;
                for ($k = 0; $k < $this->iCells[$i][$j]->iRowSpan; ++$k) {
                    $cheight += $this->iRowHeight[$i + $k];
                }

                $this->iCells[$i][$j]->Stroke($aImg, $cpos, $rpos, $cwidth, $cheight);
                $cpos += $this->iColWidth[$j];
            }
            $rpos += $this->iRowHeight[$i];
        }

        // Stroke outer border
        $aImg->SetColor($this->iBorderColor);
        if ($this->iBorderWeight == 1) {
            $aImg->Rectangle($this->iXPos, $this->iYPos, $this->iXPos + $width, $this->iYPos + $height);
        } else {
            for ($i = 0; $i < $this->iBorderWeight; ++$i) {
                $aImg->Rectangle($this->iXPos + $i, $this->iYPos + $i,
                    $this->iXPos + $width - 1 + $this->iBorderWeight - $i,
                    $this->iYPos + $height - 1 + $this->iBorderWeight - $i);
            }
        }
    }
}
