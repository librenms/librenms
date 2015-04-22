<?php
//=======================================================================
// File:        JPGRAPH_LED.PHP
// Description: Module to generate Dotted LED-like digits
// Created:     2006-11-26
// Ver:         $Id: jpgraph_led.php 1674 2009-07-22 19:42:23Z ljp $
//
// Copyright 2006 (c) Aditus Consulting. All rights reserved.
//
// Changed: 2007-08-06 by Alexander Kurochkin (inspector@list.ru)
//========================================================================

// Constants for color schema
DEFINE('LEDC_RED', 0);
DEFINE('LEDC_GREEN', 1);
DEFINE('LEDC_BLUE', 2);
DEFINE('LEDC_YELLOW', 3);
DEFINE('LEDC_GRAY', 4);
DEFINE('LEDC_CHOCOLATE', 5);
DEFINE('LEDC_PERU', 6);
DEFINE('LEDC_GOLDENROD', 7);
DEFINE('LEDC_KHAKI', 8);
DEFINE('LEDC_OLIVE', 9);
DEFINE('LEDC_LIMEGREEN', 10);
DEFINE('LEDC_FORESTGREEN', 11);
DEFINE('LEDC_TEAL', 12);
DEFINE('LEDC_STEELBLUE', 13);
DEFINE('LEDC_NAVY', 14);
DEFINE('LEDC_INVERTGRAY', 15);

// Check that mb_strlen() is available
if( ! function_exists('mb_strlen') ) {
    JpGraphError::RaiseL(25500);
    //'Multibyte strings must be enabled in the PHP installation in order to run the LED module
    // so that the function mb_strlen() is available. See PHP documentation for more information.'
}

//========================================================================
// CLASS DigitalLED74
// Description:
// Construct a number as an image that looks like LED numbers in a
// 7x4 digital matrix
//========================================================================
class DigitalLED74
{
    private $iLED_X = 4, $iLED_Y=7,

        // fg-up, fg-down, bg
        $iColorSchema = array(
            LEDC_RED  => array('red','darkred:0.9','red:0.3'),// 0
            LEDC_GREEN  => array('green','darkgreen','green:0.3'),// 1
            LEDC_BLUE  => array('lightblue:0.9','darkblue:0.85','darkblue:0.7'),// 2
            LEDC_YELLOW  => array('yellow','yellow:0.4','yellow:0.3'),// 3
            LEDC_GRAY  => array('gray:1.4','darkgray:0.85','darkgray:0.7'),
            LEDC_CHOCOLATE => array('chocolate','chocolate:0.7','chocolate:0.5'),
            LEDC_PERU  => array('peru:0.95','peru:0.6','peru:0.5'),
            LEDC_GOLDENROD => array('goldenrod','goldenrod:0.6','goldenrod:0.5'),
            LEDC_KHAKI  => array('khaki:0.7','khaki:0.4','khaki:0.3'),
            LEDC_OLIVE  => array('#808000','#808000:0.7','#808000:0.6'),
            LEDC_LIMEGREEN => array('limegreen:0.9','limegreen:0.5','limegreen:0.4'),
            LEDC_FORESTGREEN => array('forestgreen','forestgreen:0.7','forestgreen:0.5'),
            LEDC_TEAL  => array('teal','teal:0.7','teal:0.5'),
            LEDC_STEELBLUE => array('steelblue','steelblue:0.65','steelblue:0.5'),
            LEDC_NAVY  => array('navy:1.3','navy:0.95','navy:0.8'),//14
            LEDC_INVERTGRAY => array('darkgray','lightgray:1.5','white')//15
            ),

        /* Each line of the character is encoded as a 4 bit value
         0      ____
         1      ___x
         2      __x_
         3      __xx
         4      _x__
         5      _x_x
         6      _xx_
         7      _xxx
         8      x___
         9      x__x
         10     x_x_
         11     x_xx
         12     xx__
         13     xx_x
         14     xxx_
         15     xxxx
        */

        $iLEDSpec = array(
            0 => array(6,9,11,15,13,9,6),
            1 => array(2,6,10,2,2,2,2),
            2 => array(6,9,1,2,4,8,15),
            3 => array(6,9,1,6,1,9,6),
            4 => array(1,3,5,9,15,1,1),
            5 => array(15,8,8,14,1,9,6),
            6 => array(6,8,8,14,9,9,6),
            7 => array(15,1,1,2,4,4,4),
            8 => array(6,9,9,6,9,9,6),
            9 => array(6,9,9,7,1,1,6),
            '!' => array(4,4,4,4,4,0,4),
            '?' => array(6,9,1,2,2,0,2),
            '#' => array(0,9,15,9,15,9,0),
            '@' => array(6,9,11,11,10,9,6),
            '-' => array(0,0,0,15,0,0,0),
            '_' => array(0,0,0,0,0,0,15),
            '=' => array(0,0,15,0,15,0,0),
            '+' => array(0,0,4,14,4,0,0),
            '|' => array(4,4,4,4,4,4,4), //vertical line, used for simulate rus 'Ы'
            ',' => array(0,0,0,0,0,12,4),
            '.' => array(0,0,0,0,0,12,12),
            ':' => array(12,12,0,0,0,12,12),
            ';' => array(12,12,0,0,0,12,4),
            '[' => array(3,2,2,2,2,2,3),
            ']' => array(12,4,4,4,4,4,12),
            '(' => array(1,2,2,2,2,2,1),
            ')' => array(8,4,4,4,4,4,8),
            '{' => array(3,2,2,6,2,2,3),
            '}' => array(12,4,4,6,4,4,12),
            '<' => array(1,2,4,8,4,2,1),
            '>' => array(8,4,2,1,2,4,8),
            '*' => array(9,6,15,6,9,0,0),
            '"' => array(10,10,0,0,0,0,0),
            '\'' => array(4,4,0,0,0,0,0),
            '`' => array(4,2,0,0,0,0,0),
            '~' => array(13,11,0,0,0,0,0),
            '^' => array(4,10,0,0,0,0,0),
            '\\' => array(8,8,4,6,2,1,1),
            '/' => array(1,1,2,6,4,8,8),
            '%' => array(1,9,2,6,4,9,8),
            '&' => array(0,4,10,4,11,10,5),
            '$' => array(2,7,8,6,1,14,4),
            ' ' => array(0,0,0,0,0,0,0),
            '•' => array(0,0,6,6,0,0,0), //149
            '°' => array(14,10,14,0,0,0,0), //176
            '†' => array(4,4,14,4,4,4,4), //134
            '‡' => array(4,4,14,4,14,4,4), //135
            '±' => array(0,4,14,4,0,14,0), //177
            '‰' => array(0,4,2,15,2,4,0), //137 show right arrow
            '™' => array(0,2,4,15,4,2,0), //156 show left arrow
            'Ў' => array(0,0,8,8,0,0,0), //159 show small hi-stick - that need for simulate rus 'Ф'
            "\t" => array(8,8,8,0,0,0,0), //show hi-stick - that need for simulate rus 'У'
            "\r" => array(8,8,8,8,8,8,8), //vertical line - that need for simulate 'M', 'W' and rus 'М','Ш' ,'Щ'
            "\n" => array(15,15,15,15,15,15,15), //fill up - that need for simulate rus 'Ж'
            "Ґ" => array(10,5,10,5,10,5,10), //chess
            "µ" => array(15,0,15,0,15,0,15), //4 horizontal lines
            // latin
            'A' => array(6,9,9,15,9,9,9),
            'B' => array(14,9,9,14,9,9,14),
            'C' => array(6,9,8,8,8,9,6),
            'D' => array(14,9,9,9,9,9,14),
            'E' => array(15,8,8,14,8,8,15),
            'F' => array(15,8,8,14,8,8,8),
            'G' => array(6,9,8,8,11,9,6),
            'H' => array(9,9,9,15,9,9,9),
            'I' => array(14,4,4,4,4,4,14),
            'J' => array(15,1,1,1,1,9,6),
            'K' => array(8,9,10,12,12,10,9),
            'L' => array(8,8,8,8,8,8,15),
            'M' => array(8,13,10,8,8,8,8),// need to add \r
            'N' => array(9,9,13,11,9,9,9),
            'O' => array(6,9,9,9,9,9,6),
            'P' => array(14,9,9,14,8,8,8),
            'Q' => array(6,9,9,9,13,11,6),
            'R' => array(14,9,9,14,12,10,9),
            'S' => array(6,9,8,6,1,9,6),
            'T' => array(14,4,4,4,4,4,4),
            'U' => array(9,9,9,9,9,9,6),
            'V' => array(0,0,0,10,10,10,4),
            'W' => array(8,8,8,8,10,13,8),// need to add \r
            'X' => array(9,9,6,6,6,9,9),
            'Y' => array(10,10,10,10,4,4,4),
            'Z' => array(15,1,2,6,4,8,15),
            // russian utf-8
            'А' => array(6,9,9,15,9,9,9),
            'Б' => array(14,8,8,14,9,9,14),
            'В' => array(14,9,9,14,9,9,14),
            'Г' => array(15,8,8,8,8,8,8),
            'Д' => array(14,9,9,9,9,9,14),
            'Е' => array(15,8,8,14,8,8,15),
            'Ё' => array(6,15,8,14,8,8,15),
            //Ж is combine: >\n<
            'З' => array(6,9,1,2,1,9,6),
            'И' => array(9,9,9,11,13,9,9),
            'Й' => array(13,9,9,11,13,9,9),
            'К' => array(9,10,12,10,9,9,9),
            'Л' => array(7,9,9,9,9,9,9),
            'М' => array(8,13,10,8,8,8,8),// need to add \r
            'Н' => array(9,9,9,15,9,9,9),
            'О' => array(6,9,9,9,9,9,6),
            'П' => array(15,9,9,9,9,9,9),
            'Р' => array(14,9,9,14,8,8,8),
            'С' => array(6,9,8,8,8,9,6),
            'Т' => array(14,4,4,4,4,4,4),
            'У' => array(9,9,9,7,1,9,6),
            'Ф' => array(2,7,10,10,7,2,2),// need to add Ў
            'Х' => array(9,9,6,6,6,9,9),
            'Ц' => array(10,10,10,10,10,15,1),
            'Ч' => array(9,9,9,7,1,1,1),
            'Ш' => array(10,10,10,10,10,10,15),// \r
            'Щ' => array(10,10,10,10,10,15,0),// need to add \r
            'Ъ' => array(12,4,4,6,5,5,6),
            'Ы' => array(8,8,8,14,9,9,14),// need to add |
            'Ь' => array(8,8,8,14,9,9,14),
            'Э' => array(6,9,1,7,1,9,6),
            'Ю' => array(2,2,2,3,2,2,2),// need to add O
            'Я' => array(7,9,9,7,3,5,9)
            ),

        $iSuperSampling = 3, $iMarg = 1, $iRad = 4;

    function __construct($aRadius = 2, $aMargin= 0.6) {
        $this->iRad = $aRadius;
        $this->iMarg = $aMargin;
    }

    function SetSupersampling($aSuperSampling = 2) {
        $this->iSuperSampling = $aSuperSampling;
    }

    function _GetLED($aLedIdx, $aColor = 0) {
        $width=  $this->iLED_X*$this->iRad*2 +  ($this->iLED_X+1)*$this->iMarg + $this->iRad ;
        $height= $this->iLED_Y*$this->iRad*2 +  ($this->iLED_Y)*$this->iMarg + $this->iRad * 2;

        // Adjust radious for supersampling
        $rad = $this->iRad * $this->iSuperSampling;

        // Margin in between "Led" dots
        $marg = $this->iMarg * $this->iSuperSampling;

        $swidth = $width*$this->iSuperSampling;
        $sheight = $height*$this->iSuperSampling;

        $simg = new RotImage($swidth, $sheight, 0, DEFAULT_GFORMAT, false);
        $simg->SetColor($this->iColorSchema[$aColor][2]);
        $simg->FilledRectangle(0, 0, $swidth-1, $sheight-1);

        if( array_key_exists($aLedIdx, $this->iLEDSpec) ) {
            $d = $this->iLEDSpec[$aLedIdx];
        }
        else {
            $d = array(0,0,0,0,0,0,0);
        }

        for($r = 0; $r < 7; ++$r) {
            $dr = $d[$r];
            for($c = 0; $c < 4; ++$c) {
                if( ($dr & pow(2,3-$c)) !== 0 ) {
                    $color = $this->iColorSchema[$aColor][0];
                }
                else {
                    $color = $this->iColorSchema[$aColor][1];
                }

                $x = 2*$rad*$c+$rad + ($c+1)*$marg + $rad ;
                $y = 2*$rad*$r+$rad + ($r+1)*$marg + $rad ;

                $simg->SetColor($color);
                $simg->FilledCircle($x,$y,$rad);
            }
        }

        $img =  new Image($width, $height, DEFAULT_GFORMAT, false);
        $img->Copy($simg->img, 0, 0, 0, 0, $width, $height, $swidth, $sheight);
        $simg->Destroy();
        unset($simg);
        return $img;
    }


    function Stroke($aValStr, $aColor = 0, $aFileName = '') {
    	$this->StrokeNumber($aValStr, $aColor, $aFileName);
    }


    function StrokeNumber($aValStr, $aColor = 0, $aFileName = '') {
        if( $aColor < 0 || $aColor >= sizeof($this->iColorSchema) ) {
            $aColor = 0;
        }

        if(($n = mb_strlen($aValStr,'utf8')) == 0) {
            $aValStr = ' ';
            $n = 1;
        }

        for($i = 0; $i < $n; ++$i) {
            $d = mb_substr($aValStr, $i, 1, 'utf8');
            if(  ctype_digit($d) ) {
                $d = (int)$d;
            }
            else {
               $d = strtoupper($d);
            }
            $digit_img[$i] = $this->_GetLED($d, $aColor);
        }

        $w = imagesx($digit_img[0]->img);
        $h = imagesy($digit_img[0]->img);

        $number_img = new Image($w*$n, $h, DEFAULT_GFORMAT, false);

        for($i = 0; $i < $n; ++$i) {
            $number_img->Copy($digit_img[$i]->img, $i*$w, 0, 0, 0, $w, $h, $w, $h);
        }

        if( $aFileName != '' ) {
            $number_img->Stream($aFileName);
        } else {
            $number_img->Headers();
            $number_img->Stream();
        }
    }
}
?>
