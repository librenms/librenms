<?php
/*=======================================================================
 // File:        JPGRAPH_UTILS.INC
 // Description: Collection of non-essential "nice to have" utilities
 // Created:     2005-11-20
 // Ver:         $Id: jpgraph_utils.inc.php 1777 2009-08-23 17:34:36Z ljp $
 //
 // Copyright (c) Aditus Consulting. All rights reserved.
 //========================================================================
 */

//===================================================
// CLASS FuncGenerator
// Description: Utility class to help generate data for function plots.
// The class supports both parametric and regular functions.
//===================================================
class FuncGenerator {
    private $iFunc='',$iXFunc='',$iMin,$iMax,$iStepSize;

    function __construct($aFunc,$aXFunc='') {
        $this->iFunc = $aFunc;
        $this->iXFunc = $aXFunc;
    }

    function E($aXMin,$aXMax,$aSteps=50) {
        $this->iMin = $aXMin;
        $this->iMax = $aXMax;
        $this->iStepSize = ($aXMax-$aXMin)/$aSteps;

        if( $this->iXFunc != '' )
        $t = 'for($i='.$aXMin.'; $i<='.$aXMax.'; $i += '.$this->iStepSize.') {$ya[]='.$this->iFunc.';$xa[]='.$this->iXFunc.';}';
        elseif( $this->iFunc != '' )
        $t = 'for($x='.$aXMin.'; $x<='.$aXMax.'; $x += '.$this->iStepSize.') {$ya[]='.$this->iFunc.';$xa[]=$x;} $x='.$aXMax.';$ya[]='.$this->iFunc.';$xa[]=$x;';
        else
        JpGraphError::RaiseL(24001);//('FuncGenerator : No function specified. ');

        @eval($t);

        // If there is an error in the function specifcation this is the only
        // way we can discover that.
        if( empty($xa) || empty($ya) )
        JpGraphError::RaiseL(24002);//('FuncGenerator : Syntax error in function specification ');

        return array($xa,$ya);
    }
}


//=============================================================================
// CLASS DateScaleUtils
// Description: Help to create a manual date scale
//=============================================================================
define('DSUTILS_MONTH',1); // Major and minor ticks on a monthly basis
define('DSUTILS_MONTH1',1); // Major and minor ticks on a monthly basis
define('DSUTILS_MONTH2',2); // Major ticks on a bi-monthly basis
define('DSUTILS_MONTH3',3); // Major icks on a tri-monthly basis
define('DSUTILS_MONTH6',4); // Major on a six-monthly basis
define('DSUTILS_WEEK1',5); // Major ticks on a weekly basis
define('DSUTILS_WEEK2',6); // Major ticks on a bi-weekly basis
define('DSUTILS_WEEK4',7); // Major ticks on a quod-weekly basis
define('DSUTILS_DAY1',8); // Major ticks on a daily basis
define('DSUTILS_DAY2',9); // Major ticks on a bi-daily basis
define('DSUTILS_DAY4',10); // Major ticks on a qoud-daily basis
define('DSUTILS_YEAR1',11); // Major ticks on a yearly basis
define('DSUTILS_YEAR2',12); // Major ticks on a bi-yearly basis
define('DSUTILS_YEAR5',13); // Major ticks on a five-yearly basis


class DateScaleUtils {
    public static $iMin=0, $iMax=0;

    private static $starthour,$startmonth, $startday, $startyear;
    private static $endmonth, $endyear, $endday;
    private static $tickPositions=array(),$minTickPositions=array();
    private static $iUseWeeks = true;

    static function UseWeekFormat($aFlg) {
        self::$iUseWeeks = $aFlg;
    }

    static function doYearly($aType,$aMinor=false) {
        $i=0; $j=0;
        $m = self::$startmonth;
        $y = self::$startyear;

        if( self::$startday == 1 ) {
            self::$tickPositions[$i++] = mktime(0,0,0,$m,1,$y);
        }
        ++$m;


        switch( $aType ) {
            case DSUTILS_YEAR1:
                for($y=self::$startyear; $y <= self::$endyear; ++$y ) {
                    if( $aMinor ) {
                        while( $m <= 12 ) {
                            if( !($y == self::$endyear && $m > self::$endmonth) ) {
                                self::$minTickPositions[$j++] = mktime(0,0,0,$m,1,$y);
                            }
                            ++$m;
                        }
                        $m=1;
                    }
                    self::$tickPositions[$i++] = mktime(0,0,0,1,1,$y);
                }
                break;
            case DSUTILS_YEAR2:
                $y=self::$startyear;
                while( $y <= self::$endyear ) {
                    self::$tickPositions[$i++] = mktime(0,0,0,1,1,$y);
                    for($k=0; $k < 1; ++$k ) {
                        ++$y;
                        if( $aMinor ) {
                            self::$minTickPositions[$j++] = mktime(0,0,0,1,1,$y);
                        }
                    }
                    ++$y;
                }
                break;
            case DSUTILS_YEAR5:
                $y=self::$startyear;
                while( $y <= self::$endyear ) {
                    self::$tickPositions[$i++] = mktime(0,0,0,1,1,$y);
                    for($k=0; $k < 4; ++$k ) {
                        ++$y;
                        if( $aMinor ) {
                            self::$minTickPositions[$j++] = mktime(0,0,0,1,1,$y);
                        }
                    }
                    ++$y;
                }
                break;
        }
    }

    static function doDaily($aType,$aMinor=false) {
        $m = self::$startmonth;
        $y = self::$startyear;
        $d = self::$startday;
        $h = self::$starthour;
        $i=0;$j=0;

        if( $h == 0 ) {
            self::$tickPositions[$i++] = mktime(0,0,0,$m,$d,$y);
        }
        $t = mktime(0,0,0,$m,$d,$y);

        switch($aType) {
            case DSUTILS_DAY1:
                while( $t <= self::$iMax ) {
                    $t = strtotime('+1 day',$t);
                    self::$tickPositions[$i++] = $t;
                    if( $aMinor ) {
                        self::$minTickPositions[$j++] = strtotime('+12 hours',$t);
                    }
                }
                break;
            case DSUTILS_DAY2:
                while( $t <= self::$iMax ) {
                    $t = strtotime('+1 day',$t);
                    if( $aMinor ) {
                        self::$minTickPositions[$j++] = $t;
                    }
                    $t = strtotime('+1 day',$t);
                    self::$tickPositions[$i++] = $t;
                }
                break;
            case DSUTILS_DAY4:
                while( $t <= self::$iMax ) {
                    for($k=0; $k < 3; ++$k ) {
                        $t = strtotime('+1 day',$t);
                        if( $aMinor ) {
                            self::$minTickPositions[$j++] = $t;
                        }
                    }
                    $t = strtotime('+1 day',$t);
                    self::$tickPositions[$i++] = $t;
                }
                break;
        }
    }

    static function doWeekly($aType,$aMinor=false) {
        $hpd = 3600*24;
        $hpw = 3600*24*7;
        // Find out week number of min date
        $thursday = self::$iMin + $hpd * (3 - (date('w', self::$iMin) + 6) % 7);
        $week = 1 + (date('z', $thursday) - (11 - date('w', mktime(0, 0, 0, 1, 1, date('Y', $thursday)))) % 7) / 7;
        $daynumber = date('w',self::$iMin);
        if( $daynumber == 0 ) $daynumber = 7;
        $m = self::$startmonth;
        $y = self::$startyear;
        $d = self::$startday;
        $i=0;$j=0;
        // The assumption is that the weeks start on Monday. If the first day
        // is later in the week then the first week tick has to be on the following
        // week.
        if( $daynumber == 1 ) {
            self::$tickPositions[$i++] = mktime(0,0,0,$m,$d,$y);
            $t = mktime(0,0,0,$m,$d,$y) + $hpw;
        }
        else {
            $t = mktime(0,0,0,$m,$d,$y) + $hpd*(8-$daynumber);
        }

        switch($aType) {
            case DSUTILS_WEEK1:
                $cnt=0;
                break;
            case DSUTILS_WEEK2:
                $cnt=1;
                break;
            case DSUTILS_WEEK4:
                $cnt=3;
                break;
        }
        while( $t <= self::$iMax ) {
            self::$tickPositions[$i++] = $t;
            for($k=0; $k < $cnt; ++$k ) {
                $t += $hpw;
                if( $aMinor ) {
                    self::$minTickPositions[$j++] = $t;
                }
            }
            $t += $hpw;
        }
    }

    static function doMonthly($aType,$aMinor=false) {
        $monthcount=0;
        $m = self::$startmonth;
        $y = self::$startyear;
        $i=0; $j=0;

        // Skip the first month label if it is before the startdate
        if( self::$startday == 1 ) {
            self::$tickPositions[$i++] = mktime(0,0,0,$m,1,$y);
            $monthcount=1;
        }
        if( $aType == 1 ) {
            if( self::$startday < 15 ) {
                self::$minTickPositions[$j++] = mktime(0,0,0,$m,15,$y);
            }
        }
        ++$m;

        // Loop through all the years included in the scale
        for($y=self::$startyear; $y <= self::$endyear; ++$y ) {
            // Loop through all the months. There are three cases to consider:
            // 1. We are in the first year and must start with the startmonth
            // 2. We are in the end year and we must stop at last month of the scale
            // 3. A year in between where we run through all the 12 months
            $stopmonth = $y == self::$endyear ? self::$endmonth : 12;
            while( $m <= $stopmonth ) {
                switch( $aType ) {
                    case DSUTILS_MONTH1:
                        // Set minor tick at the middle of the month
                        if( $aMinor ) {
                            if( $m <= $stopmonth ) {
                                if( !($y==self::$endyear && $m==$stopmonth && self::$endday < 15) )
                                self::$minTickPositions[$j++] = mktime(0,0,0,$m,15,$y);
                            }
                        }
                        // Major at month
                        // Get timestamp of first hour of first day in each month
                        self::$tickPositions[$i++] = mktime(0,0,0,$m,1,$y);

                        break;
                    case DSUTILS_MONTH2:
                        if( $aMinor ) {
                            // Set minor tick at start of each month
                            self::$minTickPositions[$j++] = mktime(0,0,0,$m,1,$y);
                        }

                        // Major at every second month
                        // Get timestamp of first hour of first day in each month
                        if( $monthcount % 2 == 0 ) {
                            self::$tickPositions[$i++] = mktime(0,0,0,$m,1,$y);
                        }
                        break;
                    case DSUTILS_MONTH3:
                        if( $aMinor ) {
                            // Set minor tick at start of each month
                            self::$minTickPositions[$j++] = mktime(0,0,0,$m,1,$y);
                        }
                        // Major at every third month
                        // Get timestamp of first hour of first day in each month
                        if( $monthcount % 3 == 0 ) {
                            self::$tickPositions[$i++] = mktime(0,0,0,$m,1,$y);
                        }
                        break;
                    case DSUTILS_MONTH6:
                        if( $aMinor ) {
                            // Set minor tick at start of each month
                            self::$minTickPositions[$j++] = mktime(0,0,0,$m,1,$y);
                        }
                        // Major at every third month
                        // Get timestamp of first hour of first day in each month
                        if( $monthcount % 6 == 0 ) {
                            self::$tickPositions[$i++] = mktime(0,0,0,$m,1,$y);
                        }
                        break;
                }
                ++$m;
                ++$monthcount;
            }
            $m=1;
        }

        // For the case where all dates are within the same month
        // we want to make sure we have at least two ticks on the scale
        // since the scale want work properly otherwise
        if(self::$startmonth == self::$endmonth && self::$startyear == self::$endyear && $aType==1 ) {
            self::$tickPositions[$i++] = mktime(0 ,0 ,0, self::$startmonth + 1, 1, self::$startyear);
        }

        return array(self::$tickPositions,self::$minTickPositions);
    }

    static function GetTicks($aData,$aType=1,$aMinor=false,$aEndPoints=false) {
        $n = count($aData);
        return self::GetTicksFromMinMax($aData[0],$aData[$n-1],$aType,$aMinor,$aEndPoints);
    }

    static function GetAutoTicks($aMin,$aMax,$aMaxTicks=10,$aMinor=false) {
        $diff = $aMax - $aMin;
        $spd = 3600*24;
        $spw = $spd*7;
        $spm = $spd*30;
        $spy = $spd*352;

        if( self::$iUseWeeks )
        $w = 'W';
        else
        $w = 'd M';

        // Decision table for suitable scales
        // First value: Main decision point
        // Second value: Array of formatting depending on divisor for wanted max number of ticks. <divisor><formatting><format-string>,..
        $tt = array(
            array($spw, array(1,DSUTILS_DAY1,'d M',2,DSUTILS_DAY2,'d M',-1,DSUTILS_DAY4,'d M')),
            array($spm, array(1,DSUTILS_DAY1,'d M',2,DSUTILS_DAY2,'d M',4,DSUTILS_DAY4,'d M',7,DSUTILS_WEEK1,$w,-1,DSUTILS_WEEK2,$w)),
            array($spy, array(1,DSUTILS_DAY1,'d M',2,DSUTILS_DAY2,'d M',4,DSUTILS_DAY4,'d M',7,DSUTILS_WEEK1,$w,14,DSUTILS_WEEK2,$w,30,DSUTILS_MONTH1,'M',60,DSUTILS_MONTH2,'M',-1,DSUTILS_MONTH3,'M')),
            array(-1, array(30,DSUTILS_MONTH1,'M-Y',60,DSUTILS_MONTH2,'M-Y',90,DSUTILS_MONTH3,'M-Y',180,DSUTILS_MONTH6,'M-Y',352,DSUTILS_YEAR1,'Y',704,DSUTILS_YEAR2,'Y',-1,DSUTILS_YEAR5,'Y')));

        $ntt = count($tt);
        $nd = floor($diff/$spd);
        for($i=0; $i < $ntt; ++$i ) {
            if( $diff <= $tt[$i][0] || $i==$ntt-1) {
                $t = $tt[$i][1];
                $n = count($t)/3;
                for( $j=0; $j < $n; ++$j ) {
                    if( $nd/$t[3*$j] <= $aMaxTicks || $j==$n-1) {
                        $type = $t[3*$j+1];
                        $fs = $t[3*$j+2];
                        list($tickPositions,$minTickPositions) = self::GetTicksFromMinMax($aMin,$aMax,$type,$aMinor);
                        return array($fs,$tickPositions,$minTickPositions,$type);
                    }
                }
            }
        }
    }

    static function GetTicksFromMinMax($aMin,$aMax,$aType,$aMinor=false,$aEndPoints=false) {
        self::$starthour = date('G',$aMin);
        self::$startmonth = date('n',$aMin);
        self::$startday = date('j',$aMin);
        self::$startyear = date('Y',$aMin);
        self::$endmonth = date('n',$aMax);
        self::$endyear = date('Y',$aMax);
        self::$endday = date('j',$aMax);
        self::$iMin = $aMin;
        self::$iMax = $aMax;

        if( $aType <= DSUTILS_MONTH6 ) {
            self::doMonthly($aType,$aMinor);
        }
        elseif( $aType <= DSUTILS_WEEK4 ) {
            self::doWeekly($aType,$aMinor);
        }
        elseif( $aType <= DSUTILS_DAY4 ) {
            self::doDaily($aType,$aMinor);
        }
        elseif( $aType <= DSUTILS_YEAR5 ) {
            self::doYearly($aType,$aMinor);
        }
        else {
            JpGraphError::RaiseL(24003);
        }
        // put a label at the very left data pos
        if( $aEndPoints ) {
            $tickPositions[$i++] = $aData[0];
        }

        // put a label at the very right data pos
        if( $aEndPoints ) {
            $tickPositions[$i] = $aData[$n-1];
        }

        return array(self::$tickPositions,self::$minTickPositions);
    }
}

//=============================================================================
// Class ReadFileData
//=============================================================================
Class ReadFileData {
    //----------------------------------------------------------------------------
    // Desciption:
    // Read numeric data from a file.
    // Each value should be separated by either a new line or by a specified
    // separator character (default is ',').
    // Before returning the data each value is converted to a proper float
    // value. The routine is robust in the sense that non numeric data in the
    // file will be discarded.
    //
    // Returns:
    // The number of data values read on success, FALSE on failure
    //----------------------------------------------------------------------------
    static function FromCSV($aFile,&$aData,$aSepChar=',',$aMaxLineLength=1024) {
        $rh = @fopen($aFile,'r');
        if( $rh === false ) {
                return false;
        }
        $tmp = array();
        $lineofdata = fgetcsv($rh, 1000, ',');
        while ( $lineofdata !== FALSE) {
            $tmp = array_merge($tmp,$lineofdata);
            $lineofdata = fgetcsv($rh, $aMaxLineLength, $aSepChar);
        }
        fclose($rh);

        // Now make sure that all data is numeric. By default
        // all data is read as strings
        $n = count($tmp);
        $aData = array();
        $cnt=0;
        for($i=0; $i < $n; ++$i) {
            if( $tmp[$i] !== "" ) {
                $aData[$cnt++] = floatval($tmp[$i]);
            }
        }
        return $cnt;
    }

    //----------------------------------------------------------------------------
    // Desciption:
    // Read numeric data from a file.
    // Each value should be separated by either a new line or by a specified
    // separator character (default is ',').
    // Before returning the data each value is converted to a proper float
    // value. The routine is robust in the sense that non numeric data in the
    // file will be discarded.
    //
    // Options:
    // 'separator'     => ',',
    // 'enclosure'     => '"',
    // 'readlength'    => 1024,
    // 'ignore_first'  => false,
    // 'first_as_key'  => false
    // 'escape'        => '\',   # PHP >= 5.3 only
    //
    // Returns:
    // The number of lines read on success, FALSE on failure
    //----------------------------------------------------------------------------
    static function FromCSV2($aFile, &$aData, $aOptions = array()) {
        $aDefaults = array(
            'separator'     => ',',
            'enclosure'     => chr(34),
            'escape'        => chr(92),
            'readlength'    => 1024,
            'ignore_first'  => false,
            'first_as_key'  => false
            );

        $aOptions = array_merge(
            $aDefaults, is_array($aOptions) ? $aOptions : array());

        if( $aOptions['first_as_key'] ) {
            $aOptions['ignore_first'] =  true;
        }

        $rh = @fopen($aFile, 'r');

        if( $rh === false ) {
            return false;
        }

        $aData  = array();
        $aLine  = fgetcsv($rh,
                          $aOptions['readlength'],
                          $aOptions['separator'],
                          $aOptions['enclosure']
                          /*, $aOptions['escape']     # PHP >= 5.3 only */
                          );

        // Use numeric array keys for the columns by default
        // If specified use first lines values as assoc keys instead
        $keys = array_keys($aLine);
        if( $aOptions['first_as_key'] ) {
            $keys = array_values($aLine);
        }

        $num_lines = 0;
        $num_cols  = count($aLine);

        while ($aLine !== false) {
            if( is_array($aLine) && count($aLine) != $num_cols ) {
                JpGraphError::RaiseL(24004);
                // 'ReadCSV2: Column count mismatch in %s line %d'
            }

            // fgetcsv returns NULL for empty lines
            if( !is_null($aLine) ) {
                $num_lines++;

                if( !($aOptions['ignore_first'] && $num_lines == 1) && is_numeric($aLine[0]) ) {
                    for( $i = 0; $i < $num_cols; $i++ ) {
                        $aData[ $keys[$i] ][] = floatval($aLine[$i]);
                    }
                }
            }

            $aLine = fgetcsv($rh,
                             $aOptions['readlength'],
                             $aOptions['separator'],
                             $aOptions['enclosure']
                             /*, $aOptions['escape']     # PHP >= 5.3 only*/
                );
        }

        fclose($rh);

        if( $aOptions['ignore_first'] ) {
            $num_lines--;
        }

        return $num_lines;
    }

    // Read data from two columns in a plain text file
    static function From2Col($aFile, $aCol1, $aCol2, $aSepChar=' ') {
        $lines = @file($aFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if( $lines === false ) {
                return false;
        }
        $s = '/[\s]+/';
        if( $aSepChar == ',' ) {
                        $s = '/[\s]*,[\s]*/';
        }
        elseif( $aSepChar == ';' ) {
                        $s = '/[\s]*;[\s]*/';
        }
        foreach( $lines as $line => $datarow ) {
                $split = preg_split($s,$datarow);
                $aCol1[] = floatval(trim($split[0]));
                $aCol2[] = floatval(trim($split[1]));
        }

        return count($lines);
    }

    // Read data from one columns in a plain text file
    static function From1Col($aFile, $aCol1) {
        $lines = @file($aFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if( $lines === false ) {
                return false;
        }
        foreach( $lines as $line => $datarow ) {
                $aCol1[] = floatval(trim($datarow));
        }

        return count($lines);
    }

    static function FromMatrix($aFile,$aSepChar=' ') {
        $lines = @file($aFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        if( $lines === false ) {
                return false;
        }
        $mat = array();
        $reg = '/'.$aSepChar.'/';
        foreach( $lines as $line => $datarow ) {
                $row = preg_split($reg,trim($datarow));
                foreach ($row as $key => $cell ) {
                        $row[$key] = floatval(trim($cell));
                }
                $mat[] = $row;
        }
        return $mat;
    }


}

define('__LR_EPSILON', 1.0e-8);
//=============================================================================
// Class LinearRegression
//=============================================================================
class LinearRegression {
        private $ix=array(),$iy=array();
        private $ib=0, $ia=0;
        private $icalculated=false;
        public $iDet=0, $iCorr=0, $iStdErr=0;

        public function __construct($aDataX,$aDataY) {
                if( count($aDataX) !== count($aDataY) ) {
                        JpGraph::Raise('LinearRegression: X and Y data array must be of equal length.');
                }
                $this->ix = $aDataX;
                $this->iy = $aDataY;
        }

        public function Calc() {

                $this->icalculated = true;

                $n = count($this->ix);
                $sx2 = 0 ;
                $sy2 = 0 ;
                $sxy = 0 ;
                $sx = 0 ;
                $sy = 0 ;

                for( $i=0; $i < $n; ++$i ) {
                        $sx2 += $this->ix[$i] * $this->ix[$i];
                        $sy2 += $this->iy[$i] * $this->iy[$i];
                        $sxy += $this->ix[$i] * $this->iy[$i];
                        $sx += $this->ix[$i];
                        $sy += $this->iy[$i];
                }

                if( $n*$sx2 - $sx*$sx > __LR_EPSILON ) {
                        $this->ib = ($n*$sxy - $sx*$sy) / ( $n*$sx2 - $sx*$sx );
                        $this->ia = ( $sy - $this->ib*$sx ) / $n;

                        $sx = $this->ib * ( $sxy - $sx*$sy/$n );
                        $sy2 = $sy2 - $sy*$sy/$n;
                        $sy = $sy2 - $sx;

                        $this->iDet = $sx / $sy2;
                        $this->iCorr = sqrt($this->iDet);
                        if( $n > 2 ) {
                                $this->iStdErr = sqrt( $sy / ($n-2) );
                        }
                        else {
                                $this->iStdErr = NAN ;
                        }
                }
                else {
                        $this->ib = 0;
                        $this->ia = 0;
                }

        }

        public function GetAB() {
                if( $this->icalculated == false )
                        $this->Calc();
                return array($this->ia, $this->ib);
        }

        public function GetStat() {
                if( $this->icalculated == false )
                        $this->Calc();
                return array($this->iStdErr, $this->iCorr, $this->iDet);
        }

        public function GetY($aMinX, $aMaxX, $aStep=1) {
                if( $this->icalculated == false )
                        $this->Calc();

                $yy = array();
                $i = 0;
                for( $x=$aMinX; $x <= $aMaxX; $x += $aStep ) {
                        $xx[$i  ] = $x;
                        $yy[$i++] = $this->ia + $this->ib * $x;
                }

                return array($xx,$yy);
        }

}

?>