<?php
namespace Amenadiel\JpGraph\Text;

use \Amenadiel\JpGraph\Util;

//=============================================================
// CLASS TTF
// Description: Handle TTF font names and mapping and loading of
//              font files
//=============================================================
class TTF
{
    private $font_files;
    private $style_names;

    public function __construct()
    {

        // String names for font styles to be used in error messages
        $this->style_names = array(
            FS_NORMAL     => 'normal',
            FS_BOLD       => 'bold',
            FS_ITALIC     => 'italic',
            FS_BOLDITALIC => 'bolditalic');

        // File names for available fonts
        $this->font_files = array(
            FF_COURIER => array(FS_NORMAL  => 'cour.ttf',
                FS_BOLD                    => 'courbd.ttf',
                FS_ITALIC                  => 'couri.ttf',
                FS_BOLDITALIC              => 'courbi.ttf'),
            FF_GEORGIA => array(FS_NORMAL  => 'georgia.ttf',
                FS_BOLD                    => 'georgiab.ttf',
                FS_ITALIC                  => 'georgiai.ttf',
                FS_BOLDITALIC              => ''),
            FF_TREBUCHE => array(FS_NORMAL => 'trebuc.ttf',
                FS_BOLD                    => 'trebucbd.ttf',
                FS_ITALIC                  => 'trebucit.ttf',
                FS_BOLDITALIC              => 'trebucbi.ttf'),
            FF_VERDANA => array(FS_NORMAL  => 'verdana.ttf',
                FS_BOLD                    => 'verdanab.ttf',
                FS_ITALIC                  => 'verdanai.ttf',
                FS_BOLDITALIC              => ''),
            FF_TIMES => array(FS_NORMAL    => 'times.ttf',
                FS_BOLD                    => 'timesbd.ttf',
                FS_ITALIC                  => 'timesi.ttf',
                FS_BOLDITALIC              => 'timesbi.ttf'),
            FF_COMIC => array(FS_NORMAL    => 'comic.ttf',
                FS_BOLD                    => 'comicbd.ttf',
                FS_ITALIC                  => '',
                FS_BOLDITALIC              => ''),
            /*FF_ARIAL => array(FS_NORMAL => 'arial.ttf',
            FS_BOLD => 'arialbd.ttf',
            FS_ITALIC => 'ariali.ttf',
            FS_BOLDITALIC => 'arialbi.ttf'),*/
            FF_ARIAL => array(FS_NORMAL     => 'arial.ttf',
                FS_BOLD                     => 'msttcorefonts/Arial_Black.ttf',
                FS_ITALIC                   => 'ariali.ttf',
                FS_BOLDITALIC               => 'arialbi.ttf'),
            FF_VERA => array(FS_NORMAL      => 'Vera.ttf',
                FS_BOLD                     => 'VeraBd.ttf',
                FS_ITALIC                   => 'VeraIt.ttf',
                FS_BOLDITALIC               => 'VeraBI.ttf'),
            FF_VERAMONO => array(FS_NORMAL  => 'VeraMono.ttf',
                FS_BOLD                     => 'VeraMoBd.ttf',
                FS_ITALIC                   => 'VeraMoIt.ttf',
                FS_BOLDITALIC               => 'VeraMoBI.ttf'),
            FF_VERASERIF => array(FS_NORMAL => 'VeraSe.ttf',
                FS_BOLD                     => 'VeraSeBd.ttf',
                FS_ITALIC                   => '',
                FS_BOLDITALIC               => ''),

            /* Chinese fonts */
            FF_SIMSUN => array(
                FS_NORMAL     => 'simsun.ttc',
                FS_BOLD       => 'simhei.ttf',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),
            FF_CHINESE => array(
                FS_NORMAL     => CHINESE_TTF_FONT,
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),
            FF_BIG5 => array(
                FS_NORMAL     => CHINESE_TTF_FONT,
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            /* Japanese fonts */
            FF_MINCHO => array(
                FS_NORMAL     => MINCHO_TTF_FONT,
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_PMINCHO => array(
                FS_NORMAL     => PMINCHO_TTF_FONT,
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_GOTHIC => array(
                FS_NORMAL     => GOTHIC_TTF_FONT,
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_PGOTHIC => array(
                FS_NORMAL     => PGOTHIC_TTF_FONT,
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            /* Hebrew fonts */
            FF_DAVID => array(
                FS_NORMAL     => 'DAVIDNEW.TTF',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_MIRIAM => array(
                FS_NORMAL     => 'MRIAMY.TTF',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_AHRON => array(
                FS_NORMAL     => 'ahronbd.ttf',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            /* Misc fonts */
            FF_DIGITAL => array(
                FS_NORMAL     => 'DIGIRU__.TTF',
                FS_BOLD       => 'Digirtu_.ttf',
                FS_ITALIC     => 'Digir___.ttf',
                FS_BOLDITALIC => 'DIGIRT__.TTF'),

            /* This is an experimental font for the speedometer development
            FF_SPEEDO =>    array(
            FS_NORMAL =>'Speedo.ttf',
            FS_BOLD =>'',
            FS_ITALIC =>'',
            FS_BOLDITALIC =>'' ),
             */

            FF_COMPUTER => array(
                FS_NORMAL     => 'COMPUTER.TTF',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_CALCULATOR => array(
                FS_NORMAL     => 'Triad_xs.ttf',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            /* Dejavu fonts */
            FF_DV_SANSSERIF => array(
                FS_NORMAL     => array('DejaVuSans.ttf'),
                FS_BOLD       => array('DejaVuSans-Bold.ttf', 'DejaVuSansBold.ttf'),
                FS_ITALIC     => array('DejaVuSans-Oblique.ttf', 'DejaVuSansOblique.ttf'),
                FS_BOLDITALIC => array('DejaVuSans-BoldOblique.ttf', 'DejaVuSansBoldOblique.ttf')),

            FF_DV_SANSSERIFMONO => array(
                FS_NORMAL     => array('DejaVuSansMono.ttf', 'DejaVuMonoSans.ttf'),
                FS_BOLD       => array('DejaVuSansMono-Bold.ttf', 'DejaVuMonoSansBold.ttf'),
                FS_ITALIC     => array('DejaVuSansMono-Oblique.ttf', 'DejaVuMonoSansOblique.ttf'),
                FS_BOLDITALIC => array('DejaVuSansMono-BoldOblique.ttf', 'DejaVuMonoSansBoldOblique.ttf')),

            FF_DV_SANSSERIFCOND => array(
                FS_NORMAL     => array('DejaVuSansCondensed.ttf', 'DejaVuCondensedSans.ttf'),
                FS_BOLD       => array('DejaVuSansCondensed-Bold.ttf', 'DejaVuCondensedSansBold.ttf'),
                FS_ITALIC     => array('DejaVuSansCondensed-Oblique.ttf', 'DejaVuCondensedSansOblique.ttf'),
                FS_BOLDITALIC => array('DejaVuSansCondensed-BoldOblique.ttf', 'DejaVuCondensedSansBoldOblique.ttf')),

            FF_DV_SERIF => array(
                FS_NORMAL     => array('DejaVuSerif.ttf'),
                FS_BOLD       => array('DejaVuSerif-Bold.ttf', 'DejaVuSerifBold.ttf'),
                FS_ITALIC     => array('DejaVuSerif-Italic.ttf', 'DejaVuSerifItalic.ttf'),
                FS_BOLDITALIC => array('DejaVuSerif-BoldItalic.ttf', 'DejaVuSerifBoldItalic.ttf')),

            FF_DV_SERIFCOND => array(
                FS_NORMAL     => array('DejaVuSerifCondensed.ttf', 'DejaVuCondensedSerif.ttf'),
                FS_BOLD       => array('DejaVuSerifCondensed-Bold.ttf', 'DejaVuCondensedSerifBold.ttf'),
                FS_ITALIC     => array('DejaVuSerifCondensed-Italic.ttf', 'DejaVuCondensedSerifItalic.ttf'),
                FS_BOLDITALIC => array('DejaVuSerifCondensed-BoldItalic.ttf', 'DejaVuCondensedSerifBoldItalic.ttf')),

            /* Placeholders for defined fonts */
            FF_USERFONT1 => array(
                FS_NORMAL     => '',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_USERFONT2 => array(
                FS_NORMAL     => '',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

            FF_USERFONT3 => array(
                FS_NORMAL     => '',
                FS_BOLD       => '',
                FS_ITALIC     => '',
                FS_BOLDITALIC => ''),

        );
    }

    //---------------
    // PUBLIC METHODS
    // Create the TTF file from the font specification
    public function File($family, $style = FS_NORMAL)
    {
        $fam = @$this->font_files[$family];
        if (!$fam) {
            Util\JpGraphError::RaiseL(25046, $family); //("Specified TTF font family (id=$family) is unknown or does not exist. Please note that TTF fonts are not distributed with JpGraph for copyright reasons. You can find the MS TTF WEB-fonts (arial, courier etc) for download at http://corefonts.sourceforge.net/");
        }
        $ff = @$fam[$style];

        // There are several optional file names. They are tried in order
        // and the first one found is used
        if (!is_array($ff)) {
            $ff = array($ff);
        }

        $jpgraph_font_dir = dirname(dirname(__FILE__)) . '/fonts/';

        foreach ($ff as $font_file) {
            // All font families are guaranteed to have the normal style

            if ($font_file === '') {
                Util\JpGraphError::RaiseL(25047, $this->style_names[$style], $this->font_files[$family][FS_NORMAL]);
            }
            //('Style "'.$this->style_names[$style].'" is not available for font family '.$this->font_files[$family][FS_NORMAL].'.');
            if (!$font_file) {
                Util\JpGraphError::RaiseL(25048, $fam); //("Unknown font style specification [$fam].");
            }

            // check jpgraph/src/fonts dir
            $jpgraph_font_file = $jpgraph_font_dir . $font_file;
            if (file_exists($jpgraph_font_file) === true && is_readable($jpgraph_font_file) === true) {
                $font_file = $jpgraph_font_file;
                break;
            }

            // check OS font dir
            if ($family >= FF_MINCHO && $family <= FF_PGOTHIC) {
                $font_file = MBTTF_DIR . $font_file;
            } else {
                $font_file = TTF_DIR . $font_file;
            }
            if (file_exists($font_file) === true && is_readable($font_file) === true) {
                break;
            }
        }

        if (!file_exists($font_file)) {
            //Util\JpGraphError::RaiseL(25049, $font_file); //("Font file \"$font_file\" is not readable or does not exist.");
            return $this->File(FF_DV_SANSSERIF, $style);
        }

        return $font_file;
    }

    public function SetUserFont($aNormal, $aBold = '', $aItalic = '', $aBoldIt = '')
    {
        $this->font_files[FF_USERFONT] =
        array(FS_NORMAL   => $aNormal,
            FS_BOLD       => $aBold,
            FS_ITALIC     => $aItalic,
            FS_BOLDITALIC => $aBoldIt);
    }

    public function SetUserFont1($aNormal, $aBold = '', $aItalic = '', $aBoldIt = '')
    {
        $this->font_files[FF_USERFONT1] =
        array(FS_NORMAL   => $aNormal,
            FS_BOLD       => $aBold,
            FS_ITALIC     => $aItalic,
            FS_BOLDITALIC => $aBoldIt);
    }

    public function SetUserFont2($aNormal, $aBold = '', $aItalic = '', $aBoldIt = '')
    {
        $this->font_files[FF_USERFONT2] =
        array(FS_NORMAL   => $aNormal,
            FS_BOLD       => $aBold,
            FS_ITALIC     => $aItalic,
            FS_BOLDITALIC => $aBoldIt);
    }

    public function SetUserFont3($aNormal, $aBold = '', $aItalic = '', $aBoldIt = '')
    {
        $this->font_files[FF_USERFONT3] =
        array(FS_NORMAL   => $aNormal,
            FS_BOLD       => $aBold,
            FS_ITALIC     => $aItalic,
            FS_BOLDITALIC => $aBoldIt);
    }
} // Class
