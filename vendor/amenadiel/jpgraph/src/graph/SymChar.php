<?php
namespace Amenadiel\JpGraph\Graph;

//=============================================================================
// CLASS SymChar
// Description: Code values for some commonly used characters that
//              normally isn't available directly on the keyboard, for example
//              mathematical and greek symbols.
//=============================================================================
class SymChar
{
    public static function Get($aSymb, $aCapital = false)
    {
        $iSymbols = array(
            /* Greek */
            array('alpha', '03B1', '0391'),
            array('beta', '03B2', '0392'),
            array('gamma', '03B3', '0393'),
            array('delta', '03B4', '0394'),
            array('epsilon', '03B5', '0395'),
            array('zeta', '03B6', '0396'),
            array('ny', '03B7', '0397'),
            array('eta', '03B8', '0398'),
            array('theta', '03B8', '0398'),
            array('iota', '03B9', '0399'),
            array('kappa', '03BA', '039A'),
            array('lambda', '03BB', '039B'),
            array('mu', '03BC', '039C'),
            array('nu', '03BD', '039D'),
            array('xi', '03BE', '039E'),
            array('omicron', '03BF', '039F'),
            array('pi', '03C0', '03A0'),
            array('rho', '03C1', '03A1'),
            array('sigma', '03C3', '03A3'),
            array('tau', '03C4', '03A4'),
            array('upsilon', '03C5', '03A5'),
            array('phi', '03C6', '03A6'),
            array('chi', '03C7', '03A7'),
            array('psi', '03C8', '03A8'),
            array('omega', '03C9', '03A9'),
            /* Money */
            array('euro', '20AC'),
            array('yen', '00A5'),
            array('pound', '20A4'),
            /* Math */
            array('approx', '2248'),
            array('neq', '2260'),
            array('not', '2310'),
            array('def', '2261'),
            array('inf', '221E'),
            array('sqrt', '221A'),
            array('int', '222B'),
            /* Misc */
            array('copy', '00A9'),
            array('para', '00A7'),
            array('tm', '2122'), /* Trademark symbol */
            array('rtm', '00AE'), /* Registered trademark */
            array('degree', '00b0'),
            array('lte', '2264'), /* Less than or equal */
            array('gte', '2265'), /* Greater than or equal */

        );

        $n     = count($iSymbols);
        $i     = 0;
        $found = false;
        $aSymb = strtolower($aSymb);
        while ($i < $n && !$found) {
            $found = $aSymb === $iSymbols[$i++][0];
        }
        if ($found) {
            $ca = $iSymbols[--$i];
            if ($aCapital && count($ca) == 3) {
                $s = $ca[2];
            } else {
                $s = $ca[1];
            }

            return sprintf('&#%04d;', hexdec($s));
        } else {
            return '';
        }
    }
}
