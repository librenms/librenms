<?php
/*************************************************************************************
 * ezt.php
 * -----------
 * Author: Ramesh Vishveshwar (ramesh.vishveshwar@gmail.com)
 * Copyright: (c) 2012 Ramesh Vishveshwar (http://thecodeisclear.in)
 * Release Version: 1.0.8.11
 * Date Started: 2012/09/01
 *
 * Easytrieve language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2012/09/22 (1.0.0)
 *   - First Release
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
    'LANG_NAME' => 'EZT',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_UPPER,
    'COMMENT_REGEXP' => array(
        // First character of the line is an asterisk. Rest of the line is spaces/null
        0 => '/\*(\s|\D)?(\n)/',
        // Asterisk followed by any character & then a non numeric character.
        // This is to prevent expressions such as 25 * 4 from being marked as a comment
        // Note: 25*4 - 100 will mark *4 - 100 as a comment. Pls. space out expressions
        // In any case, 25*4 will result in an Easytrieve error
        1 => '/\*.([^0-9\n])+.*(\n)/'
        ),
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'CONTROL','DEFINE','DISPLAY','DO','ELSE','END-DO','END-IF',
            'END-PROC','FILE','GET','GOTO','HEADING','IF','JOB','LINE',
            'PARM','PERFORM','POINT','PRINT','PROC','PUT','READ','RECORD',
            'REPORT','RETRIEVE','SEARCH','SELECT','SEQUENCE','SORT','STOP',
            'TITLE','WRITE'
            ),
        // Procedure Keywords (Names of specific procedures)
        2 => array (
            'AFTER-BREAK','AFTER-LINE','BEFORE-BREAK','BEFORE-LINE',
            'ENDPAGE','REPORT-INPUT','TERMINATION',
            ),
        // Macro names, Parameters
        3 => array (
            'COMPILE','CONCAT','DESC','GETDATE','MASK','PUNCH',
            'VALUE','SYNTAX','NEWPAGE','SKIP','COL','TALLY',
            'WITH'
            )
        ),
    'SYMBOLS' => array(
        '(',')','=','&',',','*','>','<','%'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false
        //4 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #FF0000;',
            2 => 'color: #21A502;',
            3 => 'color: #FF00FF;'
            ),
        'COMMENTS' => array(
            0 => 'color: #0000FF; font-style: italic;',
            1 => 'color: #0000FF; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => ''
            ),
        'BRACKETS' => array(
            0 => 'color: #FF7400;'
            ),
        'STRINGS' => array(
            0 => 'color: #66CC66;'
            ),
        'NUMBERS' => array(
            0 => 'color: #736205;'
            ),
        'METHODS' => array(
            1 => '',
            2 => ''
            ),
        'SYMBOLS' => array(
            0 => 'color: #FF7400;'
            ),
        'REGEXPS' => array(
            0 => 'color: #E01B6A;'
            ),
        'SCRIPT' => array(
            0 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        // We are trying to highlight Macro names here which preceded by %
        0 => '(%)([a-zA-Z0-9])+(\s|\n)'
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
