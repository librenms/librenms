<?php
/*************************************************************************************
 * ispfpanel.php
 * -------------
 * Author: Ramesh Vishveshwar (ramesh.vishveshwar@gmail.com)
 * Copyright: (c) 2012 Ramesh Vishveshwar (http://thecodeisclear.in)
 * Release Version: 1.0.8.11
 * Date Started: 2012/09/18
 *
 * ISPF Panel Definition (MVS) language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2011/09/22 (1.0.0)
 *   -  First Release
 *
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
    'LANG_NAME' => 'ISPF Panel',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'CASE_KEYWORDS' => GESHI_CAPS_UPPER,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        // Panel Definition Statements
        1 => array(
            ')CCSID',')PANEL',')ATTR',')ABC',')ABCINIT',')ABCPROC',')BODY',')MODEL',
            ')AREA',')INIT',')REINIT',')PROC',')FIELD',')HELP',')LIST',')PNTS',')END'
            ),
        // File-Tailoring Skeletons
        2 => array (
            ')DEFAULT',')BLANK', ')CM', ')DO', ')DOT', ')ELSE', ')ENDSEL',
            ')ENDDO', ')ENDDOT', ')IF', ')IM', ')ITERATE', ')LEAVE', ')NOP', ')SEL',
            ')SET', ')TB', ')TBA'
            ),
        // Control Variables
        3 => array (
            '.ALARM','.ATTR','.ATTRCHAR','.AUTOSEL','.CSRPOS','.CSRROW','.CURSOR','.HELP',
            '.HHELP','.KANA','.MSG','.NRET','.PFKEY','.RESP','.TRAIL','.ZVARS'
            ),
        // Keywords
        4 => array (
            'WINDOW','ALARM','ATTN','BARRIER','HILITE','CAPS',
            'CKBOX','CLEAR','CMD','COLOR','COMBO','CSRGRP','CUADYN',
            'SKIP','INTENS','AREA','EXTEND',
            'DESC','ASIS','VGET','VPUT','JUST','BATSCRD','BATSCRW',
            'BDBCS','BDISPMAX','BIT','BKGRND','BREDIMAX','PAD','PADC',
            'PAS','CHINESES','CHINESET','DANISH','DATAMOD','DDLIST',
            'DEPTH','DUMP','ENGLISH','ERROR','EXIT','EXPAND','FIELD',
            'FORMAT','FRENCH','GE','GERMAN','IMAGE','IND','TYPE',
            'ITALIAN','JAPANESE','KOREAN','LCOL','LEN','LIND','LISTBOX',
            'MODE','NEST','NOJUMP','NOKANA','NUMERIC','OUTLINE','PARM',
            'PGM','PORTUGESE','RADIO','RCOL','REP','RIND','ROWS',
            'SCALE','SCROLL','SFIHDR','SGERMAN','SIND','SPANISH',
            'UPPERENG','WIDTH'
            ),
        // Parameters
        5 => array (
            'ADDPOP','ALPHA','ALPHAB','DYNAMIC','SCRL',
            'CCSID','COMMAND','DSNAME','DSNAMEF','DSNAMEFM',
            'DSNAMEPQ','DSNAMEQ','EBCDIC','ENBLDUMP','ENUM',// 'EXTEND',
            'FI','FILEID','FRAME','GUI','GUISCRD','GUISCRW','HEX',
            'HIGH','IDATE','IN','INCLUDE','INPUT','ITIME','JDATE',
            'JSTD','KEYLIST','LANG','LEFT','LIST','LISTV','LISTVX',
            'LISTX','LMSG','LOGO','LOW','MIX','NAME','NAMEF','NB',
            'NEWAPPL','NEWPOOL','NOCHECK','NOLOGO','NON','NONBLANK',
            'NULLS','NUM','OFF','ON','OPT','OUT','OUTPUT','PANEL',
            /* 'PGM',*/'PICT','PICTN','POSITION','TBDISPL','PROFILE',
            'QUERY','RANGE','REVERSE','RIGHT','SHARED','SMSG',
            'STDDATE','STDTIME','TERMSTAT','TERMTRAC','TEST',
            'TESTX','TEXT','TRACE','TRACEX','USCORE','USER',
            'USERMOD','WSCMD','WSCMDV'
            ),
        ),
    'SYMBOLS' => array(
        '(',')','=','&',',','*','#','+','&','%','_','-','@','!'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false
        ),
    'STYLES' => array(
        'BKGROUND' => 'background-color: #000000; color: #00FFFF;',
        'KEYWORDS' => array(
            1 => 'color: #FF0000;',
            2 => 'color: #21A502;',
            3 => 'color: #FF00FF;',
            4 => 'color: #876C00;',
            5 => 'color: #00FF00;'
            ),
        'COMMENTS' => array(
            0 => 'color: #002EB8; font-style: italic;',
            //1 => 'color: #002EB8; font-style: italic;',
            //2 => 'color: #002EB8; font-style: italic;',
            'MULTI' => 'color: #002EB8; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => ''
            ),
        'BRACKETS' => array(
            0 => 'color: #FF7400;'
            ),
        'STRINGS' => array(
            0 => 'color: #700000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #FF6633;'
            ),
        'METHODS' => array(
            1 => '',
            2 => ''
            ),
        'SYMBOLS' => array(
            0 => 'color: #FF7400;'
            ),
        'REGEXPS' => array(
            0 => 'color: #6B1F6B;'
            ),
        'SCRIPT' => array(
            0 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        // Variables Defined in the Panel
        0 => '&amp;[a-zA-Z]{1,8}[0-9]{0,}',
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
