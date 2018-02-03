<?php
namespace Amenadiel\JpGraph\Image;

use Amenadiel\JpGraph\Util;

//===================================================
// CLASS PredefIcons
// Description: Predefined icons for use with Gantt charts
//===================================================
define('GICON_WARNINGRED', 0);
define('GICON_TEXT', 1);
define('GICON_ENDCONS', 2);
define('GICON_MAIL', 3);
define('GICON_STARTCONS', 4);
define('GICON_CALC', 5);
define('GICON_MAGNIFIER', 6);
define('GICON_LOCK', 7);
define('GICON_STOP', 8);
define('GICON_WARNINGYELLOW', 9);
define('GICON_FOLDEROPEN', 10);
define('GICON_FOLDER', 11);
define('GICON_TEXTIMPORTANT', 12);

class PredefIcons
{
    private $iBuiltinIcon = null;
    private $iLen         = -1;

    public function GetLen()
    {
        return $this->iLen;
    }

    public function GetImg($aIdx)
    {
        if ($aIdx < 0 || $aIdx >= $this->iLen) {
            Util\JpGraphError::RaiseL(6010, $aIdx);
            //('Illegal icon index for Gantt builtin icon ['.$aIdx.']');
        }
        return Image::CreateFromString(base64_decode($this->iBuiltinIcon[$aIdx][1]));
    }

    public function __construct()
    {
        //==========================================================
        // warning.png
        //==========================================================
        $this->iBuiltinIcon[0][0] = 1043;
        $this->iBuiltinIcon[0][1] =
        'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsSAAALEgHS3X78AAAA' .
        'B3RJTUUH0wgKFSgilWPhUQAAA6BJREFUeNrtl91rHFUYh5/3zMx+Z5JNUoOamCZNaqTZ6IWIkqRiQWmi1IDetHfeiCiltgXBP8AL' .
        '0SIUxf/AvfRSBS9EKILFFqyIH9CEmFZtPqrBJLs7c+b1YneT3WTTbNsUFPLCcAbmzPt73o9zzgzs2Z793231UOdv3w9k9Z2uzOdA' .
        '5+2+79yNeL7Hl7hw7oeixRMZ6PJM26W18DNAm/Vh7lR8fqh97NmMF11es1iFpMATqdirwMNA/J4DpIzkr5YsAF1PO6gIMYHRdPwl' .
        'oO2elmB+qH3sm7XozbkgYvy8SzYnZPtcblyM6I+5z3jQ+0vJfgpEu56BfI9vUkbyi2HZd1QJoeWRiAjBd4SDCW8SSAOy6wBHMzF7' .
        'YdV2A+ROuvRPLfHoiSU0EMY/cDAIhxJeGngKaN1VgHyPL7NBxI1K9P4QxBzw3K1zJ/zkG8B9uwaQ7/HNsRZv9kohBGD0o7JqMYS/' .
        '/ynPidQw/LrBiPBcS/yFCT95DvB2BWAy4575PaQbQKW+tPd3GCItu2odKI++YxiKu0d26oWmAD7paZU/rLz37VqIijD2YbnzNBBE' .
        'IBHf8K8qjL7vYhCGErEU8CTg3xXAeMp96GrJEqkyXkm9Bhui1xfsunjdGhcYLq+IzjsGmBt5YH/cmJkFq6gIqlon3u4LxdKGuCIo' .
        'Qu41g0E41po+2R33Xt5uz9kRIB2UTle7PnfKrROP1HD4sRjZlq0lzhwoZ6rDNeTi3nEg1si/7FT7kYQbXS6E5E65tA5uRF9tutq0' .
        'K/VwAF+/FbIYWt6+tjQM/AqUms7A4Wy6d7YSfSNxgMmzi0ycWWworio4QJvj4LpuL5BqugTnXzzqJsJwurrlNhJXFaavW67NRw3F' .
        'q+aJcCQVe9fzvJGmAY7/dPH0gi0f64OveGxa+usCuQMeZ0+kt8BVrX+qPO9Bzx0MgqBvs+a2PfDdYIf+WAjXU1ub4tqNaPPzRs8A' .
        'blrli+WVn79cXn0cWKl+tGx7HLc7pu3CSmnfitL+l1UihAhwjFkPQev4K/fSABjBM8JCaFuurJU+rgW41SroA8aNMVNAFtgHJCsn' .
        'XGy/58QVxAC9MccJtZ5kIzNlW440WrJ2ea4YPA9cAooA7i0A/gS+iqLoOpB1HOegqrYB3UBmJrAtQAJwpwPr1Ry92wVlgZsiYlW1' .
        'uX1gU36dymgqYxJIJJNJT1W9QqHgNwFQBGYqo94OwHZQUuPD7ACglSvc+5n5T9m/wfJJX4U9qzEAAAAASUVORK5CYII=';

        //==========================================================
        // edit.png
        //==========================================================
        $this->iBuiltinIcon[1][0] = 959;
        $this->iBuiltinIcon[1][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAFgAWABY9j+ZuwAAAAlwSFlz' .
        'AAALEAAACxABrSO9dQAAAAd0SU1FB9AKDAwbIEXOA6AAAAM8SURBVHicpdRPaBxlHMbx76ZvsmOTmm1dsEqQSIIsEmGVBAQjivEQ' .
        'PAUJngpWsAWlBw8egpQepKwplN4ULEG9CjkEyUFKlSJrWTG0IU51pCsdYW2ncUPjdtp9Z+f3vuNhu8nKbmhaf5cZeGc+PO8zf1Lc' .
        'm0KhkACICCKCMeaBjiLC0tLSnjNvPmuOHRpH0TZTU1M8zBi9wakzn7OFTs5sw8YYACYmJrre7HkeuVyu69qPF77hlT1XmZ0eQ03O' .
        'wOLJTvhBx1rLz18VmJ0eY+jVd2FxDkKXnvYLHgb97OgLzE4ON9Hzc1B1QaQzsed5O0Lta3Ec89OnR5h5McfQ+Mw2qgQUnfBOPbZ3' .
        'bK3l+xOvMT0+3ERLp5FNF6UEjcL32+DdVmGt5WLhDYYPZrbRqreFumXwql0S3w9tnDvLWD5PZigPpdOwuYpSCo3C8wU3UHxQdHbf' .
        'cZIkNM6dxcnlUM4k1eUFMlUPpUADbpkttFarHe6oYqeOr6yt4RzMQHYUcUsQVtGicHDwKprViuLDkkOtVnsHCHZVRVy/zcj1i5Af' .
        'h8AjdIts+hUcGcYPK3iBtKM3gD/uAzf/AdY2mmmVgy6X8YNNKmGIvyloPcB8SUin07RQ4EZHFdsdG0wkJEnEaHAJxvKEpSLeaokV' .
        'r4zWmhUZYLlY4b1D03y5eIEWCtS7vsciAgiIxkQRabWOrlQor66y4pUphoJb1jiO4uO5o0S3q6RSqVbiOmC7VCEgAhLSaDQ48dH7' .
        'vD46REY0iysegSjKQciRt99ib7qXwX0O+pG4teM6YKHLB9JMq4mTmF9/+AKA4wvLZByH7OgYL7+UY2qvw/7Bfg5kHiXjJFyv3CGO' .
        'Y1rof+BW4t/XLiPG0DCGr79d4XzRxRnIMn98huXSTYyJ6et1UNYQhRvcinpJq86H3wGPPPM0iBDd+QffD1g4eZjLvuG7S1Wef26E' .
        'J7L7eSx7gAHVg7V3MSbi6m/r93baBd6qQjerAJg/9Ql/XrvG0ON1+vv7GH3qSfY5fahUnSTpwZgIEQesaVXRPbHRG/xyJSAxMYlp' .
        'EOm71HUINiY7mGb95l/8jZCyQmJjMDGJjUmsdCROtZ0n/P/Z8v4Fs2MTUUf7vYoAAAAASUVORK5CYII=';

        //==========================================================
        // endconstrain.png
        //==========================================================
        $this->iBuiltinIcon[2][0] = 666;
        $this->iBuiltinIcon[2][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz' .
        'AAALDwAACw8BkvkDpQAAAAd0SU1FB9ALEREILkh0+eQAAAIXSURBVHictZU9aFNRFMd/N81HX77aptJUWmp1LHRpIcWhg5sIDlUQ' .
        'LAXB4t7RRUpwEhy7iQ46CCIoSHcl0CFaoVARU2MFMYktadLXJNok7x2HtCExvuYFmnO4w/3gx+Gc/z1HKRTdMEdXqHbB/sgc/sic' .
        'nDoYAI8XwDa8o1RMLT+2hAsigtTvbIGVqhX46szUifBGswUeCPgAGB7QeLk0X4Ork+HOxo1VgSqGASjMqkn8W4r4vVtEgI/RRQEL' .
        'vaoGD85cl5V3nySR/S1mxWxab7f35PnntNyMJeRr9kCMqiHTy09EoeToLwggx6ymiMOD/VwcD7Oa/MHkcIiQx026WGYto5P/U+ZZ' .
        '7gD0QwDuT5z9N3LrVPi0Xs543eQPKkRzaS54eviJIp4tMFQFMllAWN2qcRZHBnixNM8NYD162xq8u7ePSQ+GX2Pjwxc2dB2cLtB8' .
        '7GgamCb0anBYBeChMtl8855CarclxU1gvViiUK4w2OMkNDnGeJ8bt9fH90yOnOkCwLFTwhzykhvtYzOWoBBbY//R3dbaNTYhf2RO' .
        'QpeuUMzv188MlwuHy0H13HnE48UzMcL0WAtUHX8OxZHoG1URiFw7rnLLCswuSPD1ulze/iWjT2PSf+dBXRFtVVGIvzqph0pQL7VE' .
        'avXYaXXxPwsnt0imdttCocMmZBdK7YU9D8wuNOW0nXc6QWzPsSa5naZ1beb9BbGB6dxGtMnXAAAAAElFTkSuQmCC';

        //==========================================================
        // mail.png
        //==========================================================
        $this->iBuiltinIcon[3][0] = 1122;
        $this->iBuiltinIcon[3][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz' .
        'AAALEAAACxABrSO9dQAAAAd0SU1FB9AJHAMfFvL9OU8AAAPfSURBVHictZRdaBRXFMd/987H7tbNx8aYtGCrEexDsOBDaKHFxirb' .
        'h0qhsiY0ykppKq1osI99C4H2WSiFFMHWUhXBrjRi0uCmtSEUGgP1QWqhWjGkoW7M1kTX3WRn5p4+TJJNGolQ6IXDnDtz+N0z/3PP' .
        'UWBIpdpYa23b9g09PZ2kUrOrvmUyGVKp1Ao/mUyi56YnVgWfO/P1CihAd/dJMpmaNROIRq8BkM1m0bH6TasC3j6QXgFdXI+DR6PR' .
        'JX/Pno8B+KLnMKqlpUU8z8MYs2RBEDzWf9J+0RcRbMdxGBsbw/fmCXwPMUEYID4iAVp8wIRmDIHMo4yHSIBSASKC+CWE0C/PF9jU' .
        '3B6Cp+4M07C5FUtKGNvGwQJctPgIsgD2wRhEIqAMGB+UQYkHJgYYZD7P1HwVlmWhHcfhyk83KeRGUW4t6CgoG5SNUS4KBWgQDUov' .
        '7AGlwYASBVqH0Bk49dXpCviVV3dw/tI1Bvr7kMIIlh0NYUpjlF0BAYvcxSXmEVLKceHSCJm+PnbueBHbtkNwTXUNBzo6aGpq4sSZ' .
        'GwT5H7BsF6Wdf1GWHQAoM0upeI9PT1yioS7B7tdaSdSuw7KsUGMAy7HYsmUztTW1nMwM0txssX1rlHjjS5jy/Uq2YkK/eJuLl6/z' .
        'x+1xkslW6mrixGIODx8EFSlEBC0+tmXT0NhA2763iEUjnLv4C8XpUbSbAB1mKkGJ3J83Od77HW5EszvZSqK2iljMIeJaRGNuJePF' .
        '6mspY7BJ1DXwQnCd2fxGRq5OUCz8xt72dyhMZcn++Cu3xu9SKhdp2b4ZHWnAtTSxmIWlhcIjlksR3lNBYzlxZsb7+f7ne+xtSzOd' .
        'u83szH1OnThOPp/n+a0beeP1l4mvq+PU2Qyd+5PY1RuwlAqLYFaBfbTbyPSdfgaH77A//QF4f1O/vpr6RJyq+C5Kc/M8FbFxXItY' .
        'xOHDrvfo/fxLDnbsJBp5BowBReVWYAzabeTh5ABDw7cWoNNL3YYYNtSv57lnn6Z+Qx01VeuIuBa2DV1HD3H63BAPZu4u1WGpeLHq' .
        'Rh7+NcjA0O+0p4+CNwXigwnbWlQQdpuEpli+n+PIkcOc//YKuckJJFh2K2anrjFw+QZt6S6kPImIF/b+cqAJD1LihWAxC61twBTo' .
        'fPcQF/oGsVW5ovHQlavs2/8+uYnRVSOUgHAmmAClBIOBwKC0gPjhIRgEIX2wg7NnwpZW3d3d4vs+vu8TBMGK51rvPM9b8hdteZxd' .
        'LBbVR8feJDs0Rlv6GFKeXJ21rNRXESxMPR+CBUl0nN7PjtO+dye7Up/8v1I88bf/ixT/AO1/hZsqW+C6AAAAAElFTkSuQmCC';

        //==========================================================
        // startconstrain.png
        //==========================================================
        $this->iBuiltinIcon[4][0] = 725;
        $this->iBuiltinIcon[4][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz' .
        'AAALDgAACw4BQL7hQQAAAAd0SU1FB9ALEREICJp5fBkAAAJSSURBVHic3dS9a1NRGMfx77kxtS+xqS9FG6p1ER3qVJpBQUUc3CRU' .
        'BwURVLB1EAuKIP0THJQiiNRJBK3iJl18AyeltRZa0bbaJMbUNmlNSm5e7s25j0NqpSSmyag/OMM9POdzDuflwn8djz8gClVRrVEV' .
        'ur4Bl1FTNSzLrSS6vbml0jUUwSXj8Qfk3PkLtLW2AeBIybmrgz3+gFzpucjlE4f4btuFTuWuCF5XDr3a3UPf6cM8GQvxzbsRAJdh' .
        'ScfxSywml5j7mVypN0eGEJ0tebIre+zxB6Tv7jPReS2hREpOvpmUXU+H5eC913JnNCSRVE60pUVbWoZjprR39Yq70bdqj4pW7PEH' .
        '5FpvL9e79jOTTHM7ssDL6CJZ08LbvAGnrpZg2mI2Z/MlZfN8IkxuSwu4V9+WIrj7zFlOHfXzKrLIi2SGh5ECKjnNVNxkQEc55vOw' .
        'rb6O8JLFdHyJ+ayFElUeHvjwkfteL/V7fKTSkFvIQE4DoLI2Mz/muTkTApcBKIwaN8pwIUrKw+ajWwDknAO0d/r4zFaMuRS63sWm' .
        'RoOdm+vRIriUYjKexrQV+t1o0YEVwfZSVJmD/dIABJuO0LG3lRFx0GOfiAELE9OgCrfU0XnIp5FwGLEy5WEAOxlR5uN+ARhP7GN3' .
        '5w7Gv4bQI2+xpt4jjv2nWBmIlcExE2vDAHYioszBZXw6CPE4ADoWVHmd/tuwlZR9eXYyoszBfpiNQqaAOU5+TXRN+DeeenADPT9b' .
        'EVgKVsutKPl0TGWGhwofoquaoKK4apsq/tH/e/kFwBMXLgAEKK4AAAAASUVORK5CYII=';

        //==========================================================
        // calc.png
        //==========================================================
        $this->iBuiltinIcon[5][0] = 589;
        $this->iBuiltinIcon[5][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAA4AIwBbgMF12wAAAAlwSFlz' .
        'AAALEQAACxEBf2RfkQAAAAd0SU1FB9AHBxQeFsqn0wQAAAHKSURBVHicnZWff+RAGIef3U/gcOEgUAgUCgcLhYXCwsHBQeGgUDgs' .
        'FgMHB4VA/4Bg4XChWFgIFIqBwkJhsRAYeOGF+TQHmWSTTbKd9pU37/x45jvfTDITXEynAbdWKVQB0NazcVm0alcL4rJaRVzm+w/e' .
        '3iwAkzbYRcnnYgI04GCvsxxSPabYaEdt2Ra6D0atcvvvDmyrMWBX1zPq2ircP/Tk98DiJtjV/fim6ziOCL6dDHZNhxQ3arIMsox4' .
        'vejleL2Ay9+jaw6A+4OSICG2cacGKhsGxg+CxeqAQS0Y7BYJvowq7iGMOhXHEfzpvpQkA9bLKgOgWKt+4Lo1mM9hs9m17QNsJ70P' .
        'Fjc/O52joogoX8MZKiBiAFxd9Z1vcj9wfSpUlDRNMcYQxzFpmnJ0FPH8nDe1MQaWSz9woQpWSZKEojDkeaWoKAyr1tlu+s48wfVx' .
        'u7n5i7jthmGIiEGcT+36PP+gFeJrxWLhb0UA/lb4ggGs1T0rZs0zwM/ZjNfilcIY5tutPxgOW3F6dUX464LrKILLiw+A7WErrl+2' .
        'rABG1EL/BilZP8DjU2uR4U+2E49P1Z8QJmNXUzl24A9GBT0IruCfi86d9x+D12RGzt+pNAAAAABJRU5ErkJggg==';

        //==========================================================
        // mag.png
        //==========================================================
        $this->iBuiltinIcon[6][0] = 1415;
        $this->iBuiltinIcon[6][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlz' .
        'AAALDAAACwwBP0AiyAAAAAd0SU1FB9ALDxEWDY6Ul+UAAAUESURBVHicdZVrbFRFGIafsyyF0nalV1R6WiggaAptlzsr1OgEogmC' .
        '0IgoBAsBgkIrBAPEhBj/AP6xRTCUFEwRI4jcgsitXMrFCJptJWvBNpXYbbXtbtttt6e7e86ec/yxadlCfZPJZDIz73zzzjfvR2VL' .
        'F7U+hf0HD2JduIzTFy6SlJRkPtkcDgdCCE65OxFC8NPV6wghyM7OptankJ2dzbSC5QghEEIgCSHog9PpNAF27dlN6miZuPgElB4/' .
        'nmY3O7ZtByA1NVUCkGWZweD1eklJScESTbqxuIjrd+/x6uIl5M19hSy7nfGOeUxf+g7VjU1sKi7C4/GYsiyz7tAJAD4/cRaA1tZW' .
        'AHIPnECUVGD1+/3U19ebG4uLeHf1akamjsIwoVnVCOvQEdLoVILYYmMo3PIxSBJflpSaDX5FAmju1QAYv/8k/s8+wLVxOU0jR2LZ' .
        '8sMFAApWrCApbRRDrRZirBYSLBKaoRPQw3SFernf2sav7T0Ubt4KwL4FMwF4Vu8FoHBCKgCzDhwHwLIhZ7y5a89u4m2JhA0wTdDC' .
        'OrphEjJMNElCHxKDEjaobmvlfo/Krj27CQQCJsCGJW8C0KXqAMxMiosQA8hZWcTFx9OsaniDKh1qmG7VoFsL0x0K06kbeAMhWpRe' .
        '/KpG+gwHAKUnz7Dz3BUMw6DK18nuw99wt0Nh6VdHI8RJicmETQgFg7SFwjSrGv+oKp6ghldV6dZ0ugJBlF6FmCESQ2w2AIqXLsan' .
        'BrFYLJTnTCBrdBqveeopWZiPFaBHUegJhegMqGgxEkHDwB/UaQ9rdIV06v0+TD2EEQjQFtAY0dsNgNvt5sialQAIIXh7wQKuVf6J' .
        'gTsSccPDWlQstClBGjr9eHpVWvUQncEwdYEedF8noQ4vmYmpZMTH0nTvDn25vLbrNmu7bvfnsYEbAMnhcPDgwQPzUo2LJusw/mhp' .
        'QwlHNO0KBAnoIfxtrcQMT2De1Mm891wyUzNlUlJSpIyMDBobGzlzr5rFM/Koq6vrP8ASGxsLwPmKcvIShjPGZiPOakE3VFB8hHwd' .
        'vJAxhrk5L7Ly+RQuH/sWgPdXrwFg/6HDFBUsIj09nehfbAWwPWOT9n5RYhqGwarNWxkRM5TRCfF4U1PQsDDJFk9uYhwXvzvKjm3b' .
        'KSsro3DJInNW5RXp7u2bAKSlpeH1esnPz6eqqgqLpmmcr3Fht9ulfaV7mZk1Bs+lM6T1djM9fhg5egDPpTNMy5TZsW07kydPYdWM' .
        'aXx96ixOp9O8cfUa80srmDpjOgAulytiQqZpMnvObLbt/JTtHxXj9/tRVdU0DGOAufRpevPDTeac0hJyc3NxOOawfv161lVWS6eX' .
        'z+9/UOCxu1VWVvaTRGv16NFfjB2bNeAQp9NpTpmSM4DcbrdL0WsGDKLRR+52uwe1yP8jb2lpYfikyY9t80n03UCWZeaXVjw1f+zs' .
        'Oen+/d+pqanhzp2fKSsrw+l0mi6XiyPl5ZGITdN8fAVJwjRNJEmi1qfw1kw7siyTnJxMe3s71dXV3GpoZO64DG41NPJylvxU5D/e' .
        'qJKsfWQD9IkaZ2RmUvr9aV4aGYcQgjfO3aWoYBF5eXm4ewIsu/CbdPz1aWb0/p1bNoOrQxlUiuiaFo3c3FyEEOx9+C9CCD6paaTW' .
        'p/TXyYkTJ0Xe59jf7QOyAKDWp/QXxcFQ61P4pT3ShBBcvnUHIQTjxmX19/8BCeVg+/GPpskAAAAASUVORK5CYII=';

        //==========================================================
        // lock.png
        //==========================================================
        $this->iBuiltinIcon[7][0] = 963;
        $this->iBuiltinIcon[7][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz' .
        'AAALCwAACwsBbQSEtwAAAAd0SU1FB9AKAw0XDmwMOwIAAANASURBVHic7ZXfS1t3GMY/3+PprI7aisvo2YU6h6ATA8JW4rrlsF4U' .
        'qiAsF9mhl0N2cYTRy9G/wptAYWPD9iJtRy5asDe7cYFmyjaXOLaMImOrmkRrjL9yTmIS3120JybWQgfb3R74wuc8Lzw858vLOUpE' .
        'OK6pqSm2trbY39+nu7tbPHYch7m5OcLhMIA67kWj0aMQEWk6tm17rNm2LSIie3t7ksvlJJ1OSyqVkls3Z8SyLMnlcqTTaVKpFLdu' .
        'zmBZVj1HeY2VUti2TSQSQSml2bZdi0QirK2tMT09zerqKtlslqGhISYnJ4nHv2N+foFsNquOe9FotLlxOBwmk8lgWRbhcFgymYxY' .
        'liUi0mqaJoAuIi2macrdO7fFsizx3to0Te7euV1vrXtXEgqFmJmZYWVlhXK5LB4/U9kwDL784kYV0A3DYHd3m4sXRymXywKoRi8U' .
        'Ch01DgQCJBIJLMsiEAhIIpHw2uLz+eqtYrEYIqKZpimxWEyCwaCMjY01zYPBIJpXqVQqsby8TLVabWKA/v5+RkZGMAyDrq4ulFKH' .
        'HsfjcWZnZ+ns7KTRqwcnk0mKxSKFQqGJlVKtruuSTCYB6O3trW9UI/v9/iZPB/j8s2HOnX0FgHfeXpeffnzK+fWf+fijvhLs0PtG' .
        'D/n1OJ9+MsrlSwb3733DwMCAt1EyPj6uACYmJp56168NU6nUqFSE9nZdPE7+WqC/r4NKTagcCJVqDaUUB5VDAA4Pa9x7sMLlSwan' .
        'WjRmv13D7/erpaWlo604qOp88OF7LC48rPNosMq5Th+Dgxd4/XyA1rbzADi7j8jnf2P++wdcvSr8MJ/i8eomAKlUqn41OsDAQDeD' .
        'g++yuPCwzm/2vU8+n2a7sMFfj79mp7BBuVzioFSiXHJx3SKuW2Rzy0Up9dxnQVvODALQerqNRn4ZKe0Mvtc6TpzpmqbxalcY9Ato' .
        '2v06t515C73YQftZB9GLnDrt4LoujuPgOA4Ui+C6yOpXJwZrJ7r/gv4P/u+D9W7fLxTz+1ScQxrZ3atRLaVxdjbY2d184R6/sLHe' .
        'opHP7/Do90Ua+WWUyezzZHObP/7cfX54/dowE1d66s8TV3oE+Mfn+L/zb4XmHPjRG9YjAAAAAElFTkSuQmCC';

        //==========================================================
        // stop.png
        //==========================================================
        $this->iBuiltinIcon[8][0] = 889;
        $this->iBuiltinIcon[8][1] =
        'iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz' .
        'AAALDwAACw8BkvkDpQAAAAd0SU1FB9AJDwEvNyD6M/0AAAL2SURBVHic1ZTLaxVnGIefb2bO5OScHJN4oWrFNqcUJYoUEgU3/Qf6' .
        'F7gwCkIrvdBLUtqqiLhSg9bgBduFSHZdiG5ctkJ3xRDbUFwUmghNzBDanPGMkzOX79LFJGPMOSd204U/+Bbzvd/78F4H/ieJdoad' .
        'pZKxRFszAI/DcP0HazXY22v+HB01kee1PA/v3zfnjx4xgGnHcNZe7OvuNj+cOEF1ZATv5nUA4jhBSgmADCVWo8Ge2Of9wb18P/G7' .
        'oUXmYi30zqlTVEdGWLh1g2D6MYlKkXGE0Vl8aa2GEB149+4xXSzyoOIw/mimiZV/DPb25pFOj13A9gOMEChhUEqhVYqWKUk9QAUp' .
        'sT/P4s8PmKlUmNhQaIJbkDVqBbpw6wZ2zUc4Nm+ePku5p4eOrgpueQOFUoVCVxcD4+N07dpF9+5tVJeWGPBjhvr7WF1zC8ASgtcP' .
        'H8a7eZ1odh4sh50nzwCw9ZNh3M4Stutiu0X2nB/LyjZ6lcIbVTpdQU/jWVPzLADM8+ZGBRdtC7wrF/O7bR99iu26VL86iU4SAH4b' .
        'Po5d6AQhstMSvGyI4wS5FJBKSRwnzF8byx/u+PjzzMF1mfryQ1K/jnCahqp1xEopjFLoNEFJSRJHzF799gWHqa+/QKcSUXBI609f' .
        'Al5W4teQSiHDOipNUKnMI13RvnOXAIEKQixvGWya98SC560MFwPiqEG86JM8q79Q06lvhnOndy5/B6GPCUOMUu3BQgg8z0M3GmBZ' .
        'iGJn3v2VmsqnfzNx7FDueODuj8ROCFpjtG5TCmOYv32bJ09msP0ISydMfnAUgF8/O45RAA6WTPjlvXcB+Gn7FuRf/zAnNX6x3ARe' .
        'PSdmqL+P/YHkwMGDOGWDZTlQcNBRhPEComgB/YeHfq2InF1kLlXUOkpMbio1bd7aATRD/X0M1lPeSlM2vt2X1XBZjZnpLG2tmZO6' .
        'LbQVOIcP+HG2UauH3xgwBqOz9Cc3l1tC24Fz+MvUDroeGNb5if9H/1dM/wLPCYMw9fryKgAAAABJRU5ErkJggg==';

        //==========================================================
        // error.png
        //==========================================================
        $this->iBuiltinIcon[9][0] = 541;
        $this->iBuiltinIcon[9][1] =
        'iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAAaVBMVEX//////2Xy8mLl5V/Z2VvMzFi/v1WyslKlpU+ZmUyMjEh/' .
        'f0VyckJlZT9YWDxMTDjAwMDy8sLl5bnY2K/MzKW/v5yyspKlpYiYmH+MjHY/PzV/f2xycmJlZVlZWU9MTEXY2Ms/PzwyMjLFTjea' .
        'AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxIAAAsSAdLdfvwAAAAHdElNRQfTCAkUMSj9wWSOAAABLUlEQVR4' .
        '2s2U3ZKCMAxGjfzJanFAXFkUle/9H9JUKA1gKTN7Yy6YMjl+kNPK5rlZVSuxf1ZRnlZxFYAm93NnIKvR+MEHUgqBXx93wZGIUrSe' .
        'h+ctEgbpiMo3iQ4kioHCGxir/ZYUbr7AgPXs9bX0BCYM8vN/cPe8oQYzom3tVsSBMVHEoOJ5dm5F1RsIe9CtqGgRacCAkUvRtevT' .
        'e2pd6vOWF+gCuc/brcuhyARakBU9FgK5bUBWdHEH8tHpDsZnRTZQGzdLVvQ3CzyYZiTAmSIODEwzFCAdJopuvbpeZDisJ4pKEcjD' .
        'ijWPJhU1MjCo9dkYfiUVjQNTDKY6CVbR6A0niUSZjRwFanR0l9i/TyvGnFdqwStq5axMfDbyBksld/FUumvxS/Bd9VyJvQDWiiMx' .
        'iOsCHgAAAABJRU5ErkJggg==';

        //==========================================================
        // openfolder.png
        //==========================================================
        $this->iBuiltinIcon[10][0] = 2040;
        $this->iBuiltinIcon[10][1] =
        'iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAABGdBTUEAALGPC/xhBQAAAAZiS0dEANAAtwClFht71AAAAAlwSFlz' .
        'AAALEAAACxABrSO9dQAAAAd0SU1FB9AKDQ4RIXMeaLcAAAd1SURBVHicxZd7jBXVHcc/58zcvTNzH8vusqw8FsTsKiCUUh5WBZXG' .
        'GkOptmqwNWsWLKXFGlEpzZI0AWNKSy0WhDS22gJKtWlTsSRqzYIuLGB2WVvDIwQMZQMsy2OFfdzde+/OnHP6x907vJaFpjb9JZM5' .
        'c85Mfp/f9/s7Jxn4P4e41gtSyp78WGvtfdEAcqDFYUOH9HS0NhGk9tPb/ilSyp789UUB2AMuqhQy3Uzm7HGkE6W3dTNZMRI3EcWO' .
        'jf9ClLmWBT3dzW8jUsevWHCG3UpWl+IkHSxnbDh/Mcz12NevBcuWXTmf6TjnXvJ88gDmVB3pw3+nt3UzHa1NqMzBS2zqPLGFjtMN' .
        'ZNr3XdW+qyqwZcFk76HX/tHWfuQvyO4W7qhaHwL8efkMRlRUpPv7rqD0RrJ+FgAjLy1a20OIxZJEEuNCRfIApj+om4bGM3u2/sYU' .
        '9J41d8973f3Dhg1pISTV1dXXBRNJxPGFCzhou+DCQrScZOkktNaeDZjamgeZ9MgiYmVDccvHhjAzJw0NTh8/alyZMaVJicp0iTHj' .
        'JpgNv38tjWUhhGROdbUL9W5/MH5XCkjlcibi+KIop5LVHLKEu8A/f4r286doa9pGrGwYAAsfqbbH3b8MgO/Nqgy6WvdbbXHMkEFJ' .
        '4xUOMVEvaTZu3BgmvF4Yk4hz9rO/Ulr5cE9owae/rcGxohSOuiWkC2IjcIqKyPZm+OmCH7GhoZEF077EEzVVweAbJ+riEeO0Ey8y' .
        'UubqOHn0AOgMwvf59txnBrSp9dgxKmf/+kIP1NY8SFk0jh5ajmNHAWg5b2E5EexojGHjbiVRMoRMNs0LC+Yz46vTuH3enN7BI8fr' .
        'qFdo0BoVZNC9aVSQ4fNjBzEmQJiARxb+/AqYPMAVB5FsPU5v37g9OxgLhe14ZM5/ju052E6MNZvf5pmHHuLmmWOkEysxUtpGAtme' .
        'dtHTflJkezqQto3jFRnLssyf1jydxiiM7zNnye/c3ZsqLu2BN5fcMfzrv/hby1tPzmRUoihcTJ87CwQI2yLtDcIqsIjYUf51qBlf' .
        'OnScOSrdQUOMURkiXsLUzJnvbGhoBGDHH5cGyZLhOpYoNl5hqYnYEXOu5fDl9eYAHntx98n8hFHZcPHUuTSxSASAeK/CGIOxJJ0f' .
        'bOGNPU280dgkq6Y2yu8vfjCIlwwzr+/ZQ/PHO0gOLuO5qsftDQ2NbN+4OCgqG6WTxWVaq6zpF+DiSHWnicdylp3r6aZTWthIOrNp' .
        'ktHcvBu0sHX1Sm6ozB3B42d90zZA9bQp7PvgPSzXZfnqX/HS4DKKK2+x69Y/HURs26iBAN5ccsfw7774UcumF37C6f07KSt2OHji' .
        'DEUJD0tISjyPrrSPlAKvN0JP/U4O1NfjuhG2rvklN1SOpfXwftpbTqAyKRrff5fb7rs9V1R7m4wlz2ihA3HpmXflUWyOH2umpLiY' .
        'ui3v8M+6bWzfsRNbSgqkxaCkiy0simMuEWEhpcRzIhQWOIAh6tiAwS4owInFiTou5dOnMnl2NR++ujBwXEc9terD6M43nrj6LgAB' .
        'QnDPA9/irtkP8JRS7Hr/3T6YekDQ1pEiEXOwpUVJzCVlZZFS4mZtkpEo9ChAkDp/jtLMBACy6S4RiQghLyv5cgBRPnKUOX6smUGF' .
        'hSil0MYw9d77mPy1e5mnFE3batm3czvb6nYgEJztSFGU9LCRlMRdUjIH0+lnEMIwPNXD3NumoVJnrMCJaiciMUZfvQnz4QcBSvV1' .
        'vjE5GK358t0zmXDnDB79saLpo20c+aSRD+t25JTp7GZQwsEWFiVxl6hlUf/WO9z32CxmL1rOe6u/I2KuwGhzLQCB7/sYY9Bah3el' .
        'FKbvrrVm4vS7GH/7ncx+chEHGz7myCeNbPtoO0JI2jq78WIRLGkzsqs7V5SfFV5EovXACoiqqsfNpk2vo5VCWtYFBfoU0VoTBAFa' .
        'a7TRaK2p+MoURk+cxMzq+Rzbv49DDbuo27UTW9h0dedssPxuK+kIfN8XxhgDYPVXf2Fh4XKtFIl4AiklAlBKAYRKKK36wHIweTCt' .
        'NfHiEkaOn8j0+7/BmDFjaT30GbHywSxcuZkpFfFg+m1jjZ/NmnVvNfRvwd69e8WBA/uNFAIh4JVXXmHsmDHE4vEQQgjQ2lxQIm9N' .
        'nz35q3BEOZOHzaG2thaA4mRU+L29It+IV21CpbRQfeMFC35gRB/M2rVrubnyZmLxWJhECBEmz/eHyo/7lMlH3LFFujsthNFCCGOu' .
        '+WNyeUgpjSVzMKtWraKyshLPdcPEeYWCIEBdpIxSivr6eta8vI7d6+cGnhdV06pe1QP+F/QXWmuRL+jZZ58LlVmxYgUVFRV4rhtu' .
        '4TzMxXAA6XRaRAtsYUkx8I/JtSJQOlSwpmZpCLN8+fPcdNNoHMfB9/0QJgRoP295TlR7UVv8xxZcHMuWIZ9/Hn35vG3JEGZpzVJG' .
        'jx5N1IlitKahsZE1L69j69qHgx+urFX/lQL9JYdLlfnZihUhzOLFi8N3Ml1dthOxVH/f/8/CtqSJ2JaJ2JZ59J7RPsC/AViJsQS/' .
        'dBntAAAAAElFTkSuQmCC';

        //==========================================================
        // folder.png
        //==========================================================
        $this->iBuiltinIcon[11][0] = 1824;
        $this->iBuiltinIcon[11][1] =
        'iVBORw0KGgoAAAANSUhEUgAAACIAAAAiCAYAAAA6RwvCAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz' .
        'AAALEAAACxABrSO9dQAAAAd0SU1FB9ECAQgFFyd9cRUAAAadSURBVHiczdhvbBP3Hcfx9/2xfefEOA5JoCNNnIT8AdtZmYBETJsI' .
        '6+jQOlQihT1AYgytqzZpD1atfyYqlT1h0lRpT7aRJ4NQpRvZGELVuo5Ua9jEJDIETQsNQyPBsUJMWGPnj//e+e72wNg4xElMR6ed' .
        'ZNln3933dZ/f93f6yfB/sgmrHdDV1WXlPg8NDZUDScD8LFFFEZZlWYZhWMFg0Orq6sq/gDJAfFy1iiZy9OjrVnj4JzQ1rMWqfxm/' .
        '309jYyNtbW0kEgnu3bvH4cOH88c/jqSKQl4/XGkd+eVtAN46up1LH92ktqYS++ZX8Pv9NDQ0sGnTJlKpFOFwmO7u7vy5IyMjeVRd' .
        'XV1+WEOh0IrY4pDnq6wXX/sTiCJaMkFZdRNqxefoe7VtCSqXVDqdZnZ2ltraWkzTpKqqijt3JpFlG7dvj7NzZ1f++qFQyA3EClHL' .
        'Ql743nFkhxPDtJAd5eTaYSVUfX09lZWVlJWVIUnSg7sVQMBCUcu4ceMGe/bsIRQK1QAzOcyykIM9P0KyudAyCWyqG8nhwqa4SkLt' .
        '3r0bVVVxu924XC40TUOWZUQxe97CwgIdHR2LMHIxSCaVInVvFElxE0vMY1Pd2NUKJMWNTXHlUfF//4vETJCelwbpFm3MjP2dt37x' .
        'AlN+PzU1NViWRSwW4+7du3g8HjweD4qi5EFAJzAExIpCANbooxhplfB0FJvTg6xWIqsVRVF6MopkU3FXPcnkJxGU0VEAdF2noqKC' .
        'W3/8DpnqLjzep2lubsblcjE8PExHR8fboVDID9xYFpLBDpJF0jDQIncQpWlkm31FlFLtp9PfyuW/vYQj1kPSuRW/38+lj27S2Q7v' .
        '/aWXUBVUffVNtm3blivVCEwsC5Eyc5iiApEpDEAXMqQdldhSiWVQHjJagud+8Fuexck/zv+K82dfoSbSCsDe75/km+4GVPd6+l5t' .
        '4zJHcqVUYN2yEEtZQDCSJCueRAYsPY49HsFIZVG6p25JUumFafT4DKJN4amtT7Nz38sk5+5A70HMtEYyMkFiZhxzjQ/poXrLQrRU' .
        'DFGEeFpAlkQkm4pRiCpIKodKzk0T/2QMh+piPjxKZPwiSkUtu/b9mNnJEWS7E8nhAmvpM60oJDkXJxqNozxRRUxPIesispBBlsXV' .
        'UaKEFo8gzoaJhz8s2lOmrpUG+WBhJ9/60g+Z+fDXTAXfxllRjl1VkO0OFATsYhYliiK21ZKKhhHnFveUqSdKgwAEOp7F2v51vvw8' .
        'XH7/N1wd/BlTweuUV65BdtgfoLTSkipsdD3tRi0VYpommUwGwzDwdT5HYEc3giAwcvH3jLz3BlPB67jWeZBEKYsSBWwpHZtNKo4q' .
        'aHTDsJeeiGEYWJaFZVmYpommaRiGQdPnv0bb1m8gSRL/vPIOV979aR4lmAJ2p4qCgCxksNuKJ6VNpx4NYhgGpmkuQhmGQTqdxjAM' .
        'qr2d7HtxEEEQuH1tkKvvvkF44tqDnrIcKJKAPf1g+LAUElq8dIiu60sApmnm93Pfzc7OYhgGrie+wFe++ztcLhcT1wf54PzPCU9c' .
        'w7XWjWS3IdsdOAUBWZAxrRJnTQ6SG5bce2FCpmkughmGQSqVYm5uDtnj44sH38TtdhP6+Dwf//V4ttHXrkGURZJaic8RgHQ6jWma' .
        'SJKUL5RLKNfIOczDKF3XSSaTRCIRhLJWntp3nGfWrSMxc5OLf3iNP4+68T9Ub9nF76lTpxgfHycajZJKpdA0LZ9GbjYV7hcDWZaF' .
        'pmnMz88Ti8UYunSLmu1HFi2aVkxkaGjINTY2ttDb24vX6+XQoUNs3ryZ8vJyIDu1BUFYkkxhgxeiWlpaOHPmDE1NTdTX1xe98eWG' .
        'JnF/9dQZCoXUYDA4AOD1ejlw4ACtra2Ul5fniwmCkEcUJiUIAoFAgL6+Pnw+H21tbfT39z8SxCS7hHsfWH9/8dL4MKqnp4eWlhac' .
        'TmcekEvMNE2am5s5ceIEgUCA9vZ2Tp48ic/nY3j4UsmQHCYOjJHtpeBKqL1799Lc3IzT6UTXdRobGxkYGKC9vZ3W1tZ8Ko86NJ8a' .
        'tXHjRo4dO8bp06fZsmULGzZsoL+/n0AggNfr5ezZs/8VpGTU5OSkc//+/acBfD4f1dXV7Nq1i4aGBs6dO4fP5+Pq1SuPBbIiyjTN' .
        'RUnV1dUNXLhwAa/Xy44dO4jFYgBEo9FFF1r134BPuYlk16LrAYXsAlmtq6sbKDwoFAp9m+ykuP5ZQVZF3f8tCdwCov8LyHIoAANI' .
        'AXf/A1TI0XCDh7OWAAAAAElFTkSuQmCC';

        //==========================================================
        // file_important.png
        //==========================================================
        $this->iBuiltinIcon[12][0] = 1785;
        $this->iBuiltinIcon[12][1] =
        'iVBORw0KGgoAAAANSUhEUgAAACIAAAAiCAYAAAA6RwvCAAAABGdBTUEAALGPC/xhBQAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlz' .
        'AAALDwAACw8BkvkDpQAAAAd0SU1FB9ECDAcjDeD3lKsAAAZ2SURBVHicrZhPaFzHHcc/897s7lutJCsr2VHsOHWMk0MPbsBUrcnF' .
        'OFRdSo6FNhdB6SGHlpDmYtJCDyoxyKe6EBxKQkt7KKL0T6ABo0NbciqigtC6PhWKI2NFqqxdSd7V2/dmftPDvPd212t55dCBYfbN' .
        'zpvfZ77z+/1mdhUjytWrV93Hf/24eD5z9gwiMlDjOKbb7dLtdhER2u02u7u73Lp1CxEZBw4AeZwdNQqkMd9wbziFGINJUt6rRbz5' .
        '1ptUq1XK5TJBEAAUMHt7e+zu7gKwvLzMysoKwAng/uNg9CgQgFKlgg1DUJ67Vqtx6tQpZmdniaIIpRTOOZRSdDoddnZ2aLfbLC8v' .
        's7S0xJUrV7ZGwQSj1PhhfRodVdDlMrpc5vup5Z2fvMPdu3fZ29vDWjvwztjYGPV6nVqtRqVS4dKlSywtLQFsAdOH2XwsCEApg3jl' .
        'w98Rak2gvYjNZpNms0mSJDjnHgkDMDc3dySYQ0Ea8w139YUX0OUKulzyg7UmCEO+l1huvHuDra0t9vf3h1TJYSqVypFhHquIrlQI' .
        'S5qv/uIDAC7/4bcEQYAKvK+0Wq1DVQGIoog7d+4cCeaRII35hrt+8SsEOkRlUaEyR0UpFIrXHxyMVKVUKnHv3r0jwRwaNelBjBjL' .
        'Sz/7KYuLiwAsLi7y4z/9kY9e+TpkCuSqjI+Po7XuAWeKXLt2DWNMUZMkwRjDhQsXWFtbK6JpCCT3jfQgxomPtPX19YHWicM5x3c2' .
        '73Pj3Ru8/aO3mZqaolKpoHVvyuvXr/Ppnf/Q7uzz380NPtu4y/qnG+ztd1hfX2dtbQ3gIvDnRyqSxl1UoPjyz98D4PTp0wPtq39Z' .
        '4fdzLxegrVaLVqvF5OQkYRgWqpRKJZ77wvNsbW1RG5tgfKLOTH2G7Z1twqBQrgrMDvhInjfSOCY5iIv+hYWFgRZArEWsZWF941Bf' .
        'SdMUgMnJCWpjVU4cn+HUyePM1Gc4+fRUPkzBI5w1jbukcczLv/5l0XfmzJmBFuCba38r/CRXpT+CrDUoZ0jjB4RYonJAOYRobJKT' .
        'z5zgqfqxAbsFSH6mpHFM2qdGXh4VnoViD6mSJF2cTQeqDqBaKVHWmonJCWpZjhkC6anR5WsffTgwaHV1FaUUq6urA/2v3f5k4LnV' .
        'arG9tUn3oI2YBCcWHYAxMVYs1qZEZY2SFB2aYZDGfMN9d7uJiWPSeFiNo5Rclc3NTXZbO6RpF7EJVixYA9agwwDnUiqlEPdQ3imi' .
        'Jo27BGHIt/7x9yEjc3Nzh27Na7c/4TdffKl4bja3ae5MUIu0T/HOEIaOpJt4gwoSsVTK4SBIY77hFtY3ABBjBiZ90rKwvsH77/+K' .
        't37wOhO1iPpTk4SBw1mLsz6CnKQ4l3qV+kE+t9XHlNZOk+bUJLVIE1VCcIJWQmJ6qjj30NbcXLkZMt8YPig+Z3n1G5fZ39/j/vY2' .
        '9ckqZT2Ochbn0p4qNkU/dDfUADdXbh4HXgRO4zNdEU0XL1784PLly5w9e7Z4SazFOfGrEotDcOKrcoJPmrYIXf/Zop3QNd1skuGt' .
        'cUAb2MgAxvHZTgFUq1Wmp6eZnZ0F8JlTjDduDThBnDeECEoJtbGIp6enqEblzCcEZ1PECU4yVRiOGgd0gc+AB0CZvkv1sWPHOHfu' .
        'HOfPn8da41cpkkltEBEPJhYnBkTQJcdYVKGkgRxCfBsq5xXNgAa2Bn+hjTOgHEKBP8pzRUxykIH4ifLJRTJAl+UMBJzPHQ6bfe/f' .
        'cWIzPxlUpD+zugzIZtVk1d8znBAqRxgoQuVQgSJQ3h9C5QhDRYgjUILCAzlnEdsHYTKfMTEBcP7F54YUGVmc2GLlIn6ve6v0ahSt' .
        '8X25TzjJ+rIx1grKpQPWR4LkGVVsMgghvS0qjPdvm5OeceOTWA5Evo2mFzkjQfL7hZPUy5yvvF/uPFQL3+nbDmsLCEmT3sTmCTNr' .
        'rogT6yFsOix3ftw7OwQhkvSU6CuinhCk0+kAkFoBazEEICHaHHiPVmU0gnUp4EAc1mYrF0EBVpwPi34VrBkwPxKk3W5ju/e5/c+d' .
        'bGUHIAIuydTIE5zfc5Wr4lJcahHnHTP3CVGm78DrgY38N+DEibp7dmYKdAQmBh1hjEFjis+9CTWYGK21H6PxPyOI0DobYwzZF/z7' .
        '7jadTvJtYG0kCD7lfwl49ijgT1gc0AH+dZSJA/xB+Mz/GSIvFoj/B7H1mAd8CO/zAAAAAElFTkSuQmCC';

        $this->iLen = count($this->iBuiltinIcon);
    }
}
