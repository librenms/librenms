<?php
/*************************************************************************************
 * teraterm.php
 * --------
 * Author: Boris Maisuradze (boris at logmett.com)
 * Copyright: (c) 2008 Boris Maisuradze (http://logmett.com)
 * Release Version: 1.0.8.3
 * Date Started: 2008/09/26
 *
 * Tera Term Macro language file for GeSHi.
 *
 *
 * This version of ttl.php was created for Tera Term 4.60 and LogMeTT 2.9.4.
 * Newer versions of these application can contain additional Macro commands
 * and/or keywords that are not listed here. The latest release of ttl.php
 * can be downloaded from Download section of LogMeTT.com
 *
 * CHANGES
 * -------
 * 2008/09/26 (1.0.8)
 *   -  First Release for Tera Term 4.60 and below.
 *
 * TODO (updated 2008/09/26)
 * -------------------------
 * *
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
    'LANG_NAME' => 'Tera Term Macro',
    'COMMENT_SINGLE' => array(1 => ';'),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        /* Commands */
        1 => array(
            'Beep',
            'BplusRecv',
            'BplusSend',
            'Break',            // (version 4.53 or later)
            'Call',
            'CallMenu',         // (version 4.56 or later)
            'ChangeDir',
            'ClearScreen',
            'Clipb2Var',        //(version 4.46 or later)
            'ClosesBox',
            'CloseTT',
            'Code2Str',
            'Connect',
            'CRC32',            // (version 4.60 or later)
            'CRC32File',        // (version 4.60 or later)
            'CygConnect',       // (version 4.57 or later)
            'DelPassword',
            'Disconnect',
            'Do',               // (version 4.56 or later)
            'Else',
            'EnableKeyb',
            'End',
            'EndIf',
            'EndUntil',         // (version 4.56 or later)
            'EndWhile',
            'Exec',
            'ExecCmnd',
            'Exit',
            'FileClose',
            'FileConcat',
            'FileCopy',
            'FileCreate',
            'FileDelete',
            'FileMarkPtr',
            'FilenameBox',      //(version 4.54 or later)
            'FileOpen',
            'FileRead',
            'FileReadln',       // (version 4.48 or later)
            'FileRename',
            'FileSearch',
            'FileSeek',
            'FileSeekBack',
            'FileStrSeek',
            'FileStrSeek2',
            'FileWrite',
            'FileWriteln',
            'FindOperations',
            'FlushRecv',
            'ForNext',
            'GetDate',
            'GetDir',           //(version 4.46 or later)
            'GetEnv',
            'GetPassword',
            'GetTime',
            'GetTitle',
            'GetVer',           //(version 4.58 or later)
            'GoTo',
            'If',
            'IfDefined',        // (version 4.46 or later)
            'IfThenElseIf',
            'Include',
            'InputBox',
            'Int2Str',
            'KmtFinish',
            'KmtGet',
            'KmtRecv',
            'KmtSend',
            'LoadKeyMap',
            'LogClose',
            'LogOpen',
            'LogPause',
            'LogStart',
            'LogWrite',
            'Loop',             // (version 4.56 or later)
            'MakePath',
            'MessageBox',
            'MPause',           // (version 4.27 or later)
            'PasswordBox',
            'Pause',
            'QuickvanRecv',
            'QuickvanSend',
            'Random',           //(version 4.27 or later)
            'Recvln',
            'RestoreSetup',
            'Return',
            'RotateLeft',       //(version 4.54 or later)
            'RotateRight',      //(version 4.54 or later)
            'ScpRecv',          // (version 4.57 or later)
            'ScpSend',          // (version 4.57 or later)
            'Send',
            'SendBreak',
            'SendFile',
            'SendKcode',
            'Sendln',
            'SetBaud',          // (version 4.58 or later)
            'SetDate',
            'SetDir',
            'SetDlgPos',
            'SetDTR',           // (version 4.59 or later)
            'SetRTS',           // (version 4.59 or later)
            'SetEnv',           // (version 4.54 or later)
            'SetEcho',
            'SetExitCode',
            'SetSync',
            'SetTime',
            'SetTitle',
            'Show',
            'ShowTT',
            'Sprintf',          // (version 4.52 or later)
            'StatusBox',
            'Str2Code',
            'Str2Int',
            'StrCompare',
            'StrConcat',
            'StrCopy',
            'StrLen',
            'StrMatch',         // (version 4.59 or later)
            'StrScan',
            'Testlink',
            'Then',
            'ToLower',          //(version 4.53 or later)
            'ToUpper',          //(version 4.53 or later)
            'Unlink',
            'Until',            // (version 4.56 or later)
            'Var2Clipb',        //(version 4.46 or later)
            'Wait',
            'WaitEvent',
            'Waitln',
            'WaitRecv',
            'WaitRegex',        // (version 4.21 or later)
            'While',
            'XmodemRecv',
            'XmodemSend',
            'YesNoBox',
            'ZmodemRecv',
            'ZmodemSend'
            ),
        /* System Variables */
        2 => array(
            'groupmatchstr1',
            'groupmatchstr2',
            'groupmatchstr3',
            'groupmatchstr4',
            'groupmatchstr5',
            'groupmatchstr6',
            'groupmatchstr7',
            'groupmatchstr8',
            'groupmatchstr9',
            'inputstr',
            'matchstr',
            'param2',
            'param3',
            'param4',
            'param5',
            'param6',
            'param7',
            'param8',
            'param9',
            'result',
            'timeout'
            ),
        /* LogMeTT Key Words */
        3 => array(
            '$[1]',
            '$[2]',
            '$[3]',
            '$[4]',
            '$[5]',
            '$[6]',
            '$[7]',
            '$[8]',
            '$connection$',
            '$email$',
            '$logdir$',
            '$logfilename$',
            '$logit$',
            '$mobile$',
            '$name$',
            '$pager$',
            '$parent$',
            '$phone$',
            '$snippet$',
            '$ttdir$',
            '$user$',
            '$windir$',
            ),
        /* Keyword Symbols */
        4 => array(
            'and',
            'not',
            'or',
            'xor'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '[', ']',
        '~', '!', '+', '-', '*', '/', '%', '>>', '<<', '<<<', '>>>', '&', '^', '|',
        '<>', '<=', '>=', '=', '==', '<>', '!=', '&&', '||'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000080; font-weight: bold!important;',
            2 => 'color: #808000; font-weight: bold;',  // System Variables
            3 => 'color: #ff0000; font-weight: bold;',  // LogMeTT Key Words
            4 => 'color: #ff00ff; font-weight: bold;'   // Keyword Symbols
            ),
        'COMMENTS' => array(
            1 => 'color: #008000; font-style: italic;',
            ),
        'ESCAPE_CHAR' => array(),
        'BRACKETS' => array(
            0 => 'color: #ff00ff; font-weight: bold;'
        ),
        'STRINGS' => array(
            0 => 'color: #800080;'
            ),
        'NUMBERS' => array(
            0 => 'color: #008080;'
            ),
        'SCRIPT' => array(
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #ff00ff; font-weight: bold;'
            ),
        'REGEXPS' => array(
            0 => 'color: #0000ff; font-weight: bold;'
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        0 => array (
            GESHI_SEARCH => '(\:[_a-zA-Z][_a-zA-Z0-9]+)',
            GESHI_REPLACE => '\\1',
            GESHI_MODIFIERS => '',
            GESHI_BEFORE => '',
            GESHI_AFTER => ''
            )
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array(),
    'TAB_WIDTH' => 4
);

?>
