<?php
namespace Amenadiel\JpGraph\Text;

//=================================================================
// CLASS LanguageConv
// Description:
// Converts various character encoding into proper
// UTF-8 depending on how the library have been configured and
// what font family is being used
//=================================================================
class LanguageConv
{
    private $g2312 = null;

    public function Convert($aTxt, $aFF)
    {
        if (LANGUAGE_GREEK) {
            if (GREEK_FROM_WINDOWS) {
                $unistring = LanguageConv::gr_win2uni($aTxt);
            } else {
                $unistring = LanguageConv::gr_iso2uni($aTxt);
            }
            return $unistring;
        } elseif (LANGUAGE_CYRILLIC) {
            if (CYRILLIC_FROM_WINDOWS && (!defined('LANGUAGE_CHARSET') || stristr(LANGUAGE_CHARSET, 'windows-1251'))) {
                $aTxt = convert_cyr_string($aTxt, "w", "k");
            }
            if (!defined('LANGUAGE_CHARSET') || stristr(LANGUAGE_CHARSET, 'koi8-r') || stristr(LANGUAGE_CHARSET, 'windows-1251')) {
                $isostring = convert_cyr_string($aTxt, "k", "i");
                $unistring = LanguageConv::iso2uni($isostring);
            } else {
                $unistring = $aTxt;
            }
            return $unistring;
        } elseif ($aFF === FF_SIMSUN) {
            // Do Chinese conversion
            if ($this->g2312 == null) {
                include_once 'jpgraph_gb2312.php';
                $this->g2312 = new GB2312toUTF8();
            }
            return $this->g2312->gb2utf8($aTxt);
        } elseif ($aFF === FF_BIG5) {
            if (!function_exists('iconv')) {
                JpGraphError::RaiseL(25006);
                //('Usage of FF_CHINESE (FF_BIG5) font family requires that your PHP setup has the iconv() function. By default this is not compiled into PHP (needs the "--width-iconv" when configured).');
            }
            return iconv('BIG5', 'UTF-8', $aTxt);
        } elseif (ASSUME_EUCJP_ENCODING &&
            ($aFF == FF_MINCHO || $aFF == FF_GOTHIC || $aFF == FF_PMINCHO || $aFF == FF_PGOTHIC)) {
            if (!function_exists('mb_convert_encoding')) {
                JpGraphError::RaiseL(25127);
            }
            return mb_convert_encoding($aTxt, 'UTF-8', 'EUC-JP');
        } elseif ($aFF == FF_DAVID || $aFF == FF_MIRIAM || $aFF == FF_AHRON) {
            return LanguageConv::heb_iso2uni($aTxt);
        } else {
            return $aTxt;
        }
    }

    // Translate iso encoding to unicode
    public static function iso2uni($isoline)
    {
        $uniline = '';
        for ($i = 0; $i < strlen($isoline); $i++) {
            $thischar = substr($isoline, $i, 1);
            $charcode = ord($thischar);
            $uniline .= ($charcode > 175) ? "&#" . (1040 + ($charcode - 176)) . ";" : $thischar;
        }
        return $uniline;
    }

    // Translate greek iso encoding to unicode
    public static function gr_iso2uni($isoline)
    {
        $uniline = '';
        for ($i = 0; $i < strlen($isoline); $i++) {
            $thischar = substr($isoline, $i, 1);
            $charcode = ord($thischar);
            $uniline .= ($charcode > 179 && $charcode != 183 && $charcode != 187 && $charcode != 189) ? "&#" . (900 + ($charcode - 180)) . ";" : $thischar;
        }
        return $uniline;
    }

    // Translate greek win encoding to unicode
    public static function gr_win2uni($winline)
    {
        $uniline = '';
        for ($i = 0; $i < strlen($winline); $i++) {
            $thischar = substr($winline, $i, 1);
            $charcode = ord($thischar);
            if ($charcode == 161 || $charcode == 162) {
                $uniline .= "&#" . (740 + $charcode) . ";";
            } else {
                $uniline .= (($charcode > 183 && $charcode != 187 && $charcode != 189) || $charcode == 180) ? "&#" . (900 + ($charcode - 180)) . ";" : $thischar;
            }
        }
        return $uniline;
    }

    public static function heb_iso2uni($isoline)
    {
        $isoline = hebrev($isoline);
        $o       = '';

        $n = strlen($isoline);
        for ($i = 0; $i < $n; $i++) {
            $c = ord(substr($isoline, $i, 1));
            $o .= ($c > 223) && ($c < 251) ? '&#' . (1264 + $c) . ';' : chr($c);
        }
        return utf8_encode($o);
    }
}
