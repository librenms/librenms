<?php
/*************************************************************************************
 * rbs.php
 * ------
 * Author: Deng Wen Gang (deng@priity.com)
 * Copyright: (c) 2013 Deng Wen Gang
 * Release Version: 1.0.8.12
 * Date Started: 2013/01/15
 *
 * RBScript language file for GeSHi.
 *
 * RBScript official website: http://docs.realsoftware.com/index.php/Rbscript
 *
 * CHANGES
 * -------
 * 2013/01/15 (1.0.0)
 *  -  First Release
 *
 * TODO
 * ----
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
    'LANG_NAME' => 'RBScript',
    'COMMENT_SINGLE' => array( 1 => '//', 2 => "'" ),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(
        3 => '/REM\s.*$/im',
        4 => '/&b[01]+/',
        5 => '/&o[0-7]+/',
        6 => '/&h[a-f0-9]+/i',
        7 => '/&c[a-f0-9]+/i',
        8 => '/&u[a-f0-9]+/i',
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'Int8', 'Int16', 'Int32', 'Int64', 'Uint8', 'Uint16', 'Uint32', 'Uint64', 'Byte', 'Integer',
            'Single', 'Double', 'Boolean', 'String', 'Color', 'Object', 'Variant'
            ),
        2 => array(
            'Private', 'Public', 'Protected',
            'Sub', 'Function', 'Delegate', 'Exception',
            ),
        3 => array(
            'IsA',
            'And', 'Or', 'Not', 'Xor',
            'If', 'Then', 'Else', 'ElseIf',
            'Select', 'Case',
            'For', 'Each', 'In', 'To', 'Step', 'Next',
            'Do', 'Loop', 'Until',
            'While', 'Wend',
            'Continue', 'Exit', 'Goto', 'End',
            ),
        4 => array(
            'Const', 'Static',
            'Dim', 'As', 'Redim',
            'Me', 'Self', 'Super', 'Extends', 'Implements',
            'ByRef', 'ByVal', 'Assigns', 'ParamArray',
            'Mod',
            'Raise',
            ),
        5 => array(
            'False', 'True', 'Nil'
            ),
        6 => array(
            'Abs',
            'Acos',
            'Asc',
            'AscB',
            'Asin',
            'Atan',
            'Atan2',
            'CDbl',
            'Ceil',
            'Chr',
            'ChrB',
            'CMY',
            'Cos',
            'CountFields',
            'CStr',
            'Exp',
            'Floor',
            'Format',
            'Hex',
            'HSV',
            'InStr',
            'InStrB',
            'Left',
            'LeftB',
            'Len',
            'LenB',
            'Log',
            'Lowercase',
            'LTrim',
            'Max',
            'Microseconds',
            'Mid',
            'MidB',
            'Min',
            'NthField',
            'Oct',
            'Pow',
            'Replace',
            'ReplaceB',
            'ReplaceAll',
            'ReplaceAllB',
            'RGB',
            'Right',
            'RightB',
            'Rnd',
            'Round',
            'RTrim',
            'Sin',
            'Sqrt',
            'Str',
            'StrComp',
            'Tan',
            'Ticks',
            'Titlecase',
            'Trim',
            'UBound',
            'Uppercase',
            'Val',
            ),
        ),
    'SYMBOLS' => array(
            '+', '-', '*', '/', '\\', '^', '<', '>', '=', '<>', '&'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false,
        6 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #F660AB; font-weight: bold;',
            2 => 'color: #E56717; font-weight: bold;',
            3 => 'color: #8D38C9; font-weight: bold;',
            4 => 'color: #151B8D; font-weight: bold;',
            5 => 'color: #00C2FF; font-weight: bold;',
            6 => 'color: #3EA99F; font-weight: bold;'
            ),
        'COMMENTS' => array(
            1 => 'color: #008000;',
            2 => 'color: #008000;',
            3 => 'color: #008000;',

            4 => 'color: #800000;',
            5 => 'color: #800000;',
            6 => 'color: #800000;',
            7 => 'color: #800000;',
            8 => 'color: #800000;',
            ),
        'BRACKETS' => array(
            ),
        'STRINGS' => array(
            0 => 'color: #800000;'
            ),
        'NUMBERS' => array(
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #800000; font-weight: bold;'
            ),
        'SCRIPT' => array(
            ),
        'REGEXPS' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'PARSER_CONTROL' => array(
        'ENABLE_FLAGS' => array(
            'BRACKETS' => GESHI_NEVER,
            'SYMBOLS' => GESHI_NEVER,
            'NUMBERS' => GESHI_NEVER
            )
        )
);
