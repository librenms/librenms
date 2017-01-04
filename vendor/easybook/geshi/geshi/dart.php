<?php
/*************************************************************************************
 * dart.php
 * --------
 * Author: Edward Hart (edward.dan.hart@gmail.com)
 * Copyright: (c) 2013 Edward Hart
 * Release Version: 1.0.8.12
 * Date Started: 2013/10/25
 *
 * Dart language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2013/10/25
 *   -  First Release
 *
 * TODO (updated 2013/10/25)
 * -------------------------
 *   -  Highlight standard library types.
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

$language_data = array(
    'LANG_NAME' => 'Dart',

    'COMMENT_SINGLE' => array('//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(),

    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'ESCAPE_REGEXP' => array(
        //Simple Single Char Escapes
        1 => "#\\\\[\\\\nrfbtv\'\"?\n]#i",
        //Hexadecimal Char Specs
        2 => "#\\\\x[\da-fA-F]{2}#",
        //Hexadecimal Char Specs
        3 => "#\\\\u[\da-fA-F]{4}#",
        4 => "#\\\\u\\{[\da-fA-F]*\\}#"
        ),
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_INT_CSTYLE |
        GESHI_NUMBER_HEX_PREFIX | GESHI_NUMBER_FLT_NONSCI |
        GESHI_NUMBER_FLT_NONSCI_F | GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,

    'KEYWORDS' => array(
        1 => array(
            'abstract', 'as', 'assert', 'break', 'case', 'catch', 'class',
            'const', 'continue', 'default', 'do', 'dynamic', 'else', 'export',
            'extends', 'external', 'factory', 'false', 'final', 'finally',
            'for', 'get', 'if', 'implements', 'import', 'in', 'is', 'library',
            'new', 'null', 'operator', 'part', 'return', 'set', 'static',
            'super', 'switch', 'this', 'throw', 'true', 'try', 'typedef', 'var',
            'while', 'with'
            ),
        2 => array(
            'double', 'bool', 'int', 'num', 'void'
            ),
        ),

    'SYMBOLS' => array(
        0 => array('(', ')', '{', '}', '[', ']'),
        1 => array('+', '-', '*', '/', '%', '~'),
        2 => array('&', '|', '^'),
        3 => array('=', '!', '<', '>'),
        4 => array('?', ':'),
        5 => array('..'),
        6 => array(';', ',')
        ),

    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        ),

    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'font-weight: bold;',
            2 => 'color: #445588; font-weight: bold;'
            ),
        'COMMENTS' => array(
            0 => 'color: #999988; font-style: italic;',
            'MULTI' => 'color: #999988; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;',
            1 => 'color: #000099; font-weight: bold;',
            2 => 'color: #660099; font-weight: bold;',
            3 => 'color: #660099; font-weight: bold;',
            4 => 'color: #660099; font-weight: bold;',
            5 => 'color: #006699; font-weight: bold;',
            'HARD' => ''
            ),
        'STRINGS' => array(
            0 => 'color: #d14;'
            ),
        'NUMBERS' => array(
            0 => 'color: #009999;',
            GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
            GESHI_NUMBER_FLT_SCI_SHORT => 'color:#800080;',
            GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI_F => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI => 'color:#800080;'
            ),
        'BRACKETS' => array(''),
        'METHODS' => array(
            1 => 'color: #006633;'
            ),
        'SYMBOLS' => array(
            0 => 'font-weight: bold;',
            1 => 'font-weight: bold;',
            2 => 'font-weight: bold;',
            3 => 'font-weight: bold;',
            4 => 'font-weight: bold;',
            5 => 'font-weight: bold;',
            6 => 'font-weight: bold;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '.'
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 4
);
