<?php
/*************************************************************************************
 * vbnet.php
 * ---------
 * Author: Alan Juden (alan@judenware.org)
 * Copyright: (c) 2004 Alan Juden, Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.8.3
 * Date Started: 2004/06/04
 *
 * VB.NET language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.0)
 *  -  Initial release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
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
    'LANG_NAME' => 'vb.net',
    'COMMENT_SINGLE' => array(1 => "'"),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            '3DDKSHADOW', '3DHIGHLIGHT', '3DLIGHT', 'ABORT', 'ABORTRETRYIGNORE', 'ACTIVEBORDER',
            'ACTIVETITLEBAR', 'ALIAS', 'APPLICATIONMODAL', 'APPLICATIONWORKSPACE', 'ARCHIVE',
            'BACK', 'BINARYCOMPARE', 'BLACK', 'BLUE', 'BUTTONFACE', 'BUTTONSHADOW', 'BUTTONTEXT',
            'CANCEL', 'CDROM', 'CR', 'CRITICAL', 'CRLF', 'CYAN', 'DEFAULT', 'DEFAULTBUTTON1',
            'DEFAULTBUTTON2', 'DEFAULTBUTTON3', 'DESKTOP', 'DIRECTORY', 'EXCLAMATION', 'FALSE',
            'FIXED', 'FORAPPENDING', 'FORMFEED', 'FORREADING', 'FORWRITING', 'FROMUNICODE',
            'GRAYTEXT', 'GREEN', 'HIDDEN', 'HIDE', 'HIGHLIGHT', 'HIGHLIGHTTEXT', 'HIRAGANA',
            'IGNORE', 'INACTIVEBORDER', 'INACTIVECAPTIONTEXT', 'INACTIVETITLEBAR', 'INFOBACKGROUND',
            'INFORMATION', 'INFOTEXT', 'KATAKANALF', 'LOWERCASE', 'MAGENTA', 'MAXIMIZEDFOCUS',
            'MENUBAR', 'MENUTEXT', 'METHOD', 'MINIMIZEDFOCUS', 'MINIMIZEDNOFOCUS', 'MSGBOXRIGHT',
            'MSGBOXRTLREADING', 'MSGBOXSETFOREGROUND', 'NARROW', 'NEWLINE', 'NO', 'NORMAL',
            'NORMALFOCUS', 'NORMALNOFOCUS', 'NULLSTRING', 'OBJECTERROR', 'OK', 'OKCANCEL', 'OKONLY',
            'PROPERCASE', 'QUESTION', 'RAMDISK', 'READONLY', 'RED', 'REMOTE', 'REMOVABLE', 'RETRY',
            'RETRYCANCEL', 'SCROLLBARS', 'SYSTEMFOLDER', 'SYSTEMMODAL', 'TEMPORARYFOLDER',
            'TEXTCOMPARE', 'TITLEBARTEXT', 'TRUE', 'UNICODE', 'UNKNOWN', 'UPPERCASE', 'VERTICALTAB',
            'VOLUME', 'WHITE', 'WIDE', 'WIN16', 'WIN32', 'WINDOWBACKGROUND', 'WINDOWFRAME',
            'WINDOWSFOLDER', 'WINDOWTEXT', 'YELLOW', 'YES', 'YESNO', 'YESNOCANCEL'
            ),
        2 => array(
            'AndAlso', 'As', 'ADDHANDLER', 'ASSEMBLY', 'AUTO', 'Binary', 'ByRef', 'ByVal', 'BEGINEPILOGUE',
            'Else', 'ElseIf', 'Empty', 'Error', 'ENDPROLOGUE', 'EXTERNALSOURCE', 'ENVIRON', 'For',
            'Friend', 'GET', 'HANDLES', 'Input', 'Is', 'IsNot', 'Len', 'Lock', 'Me', 'Mid', 'MUSTINHERIT', 'MustOverride',
            'MYBASE', 'MYCLASS', 'New', 'Next', 'Nothing', 'Null', 'NOTINHERITABLE',
            'NOTOVERRIDABLE', 'OFF', 'On', 'Option', 'Optional', 'Overloads', 'OVERRIDABLE', 'Overrides', 'ParamArray',
            'Print', 'Private', 'Property', 'Public', 'Resume', 'Return', 'Seek', 'Static', 'Step',
            'String', 'SHELL', 'SENDKEYS', 'SET', 'Shared', 'Then', 'Time', 'To', 'THROW', 'WithEvents'
            ),
        3 => array(
            'COLLECTION', 'DEBUG', 'DICTIONARY', 'DRIVE', 'DRIVES', 'ERR', 'FILE', 'FILES',
            'FILESYSTEMOBJECT', 'FOLDER', 'FOLDERS', 'TEXTSTREAM'
            ),
        4 => array(
            'BOOLEAN', 'BYTE', 'DATE', 'DECIMIAL', 'DOUBLE', 'INTEGER', 'LONG', 'OBJECT',
            'SINGLE STRING'
            ),
        5 => array(
            'ADDRESSOF', 'AND', 'BITAND', 'BITNOT', 'BITOR', 'BITXOR',
            'GETTYPE', 'LIKE', 'MOD', 'NOT', 'ORXOR'
            ),
        6 => array(
            'APPACTIVATE', 'BEEP', 'CALL', 'CHDIR', 'CHDRIVE', 'CLASS', 'CASE', 'CATCH', 'CONST',
            'DECLARE', 'DELEGATE', 'DELETESETTING', 'DIM', 'DO', 'DOEVENTS', 'END', 'ENUM',
            'EVENT', 'EXIT', 'EACH', 'FUNCTION', 'FINALLY', 'IF', 'IMPORTS', 'INHERITS',
            'INTERFACE', 'IMPLEMENTS', 'KILL', 'LOOP', 'NAMESPACE', 'OPEN', 'PUT',
            'RAISEEVENT', 'RANDOMIZE', 'REDIM', 'REM', 'RESET', 'SAVESETTING', 'SELECT',
            'SETATTR', 'STOP', 'SUB', 'SYNCLOCK', 'STRUCTURE', 'SHADOWS', 'SWITCH',
            'TRY', 'WIDTH', 'WITH', 'WRITE', 'WHILE'
            ),
        7 => array(
            'ABS', 'ARRAY', 'ASC', 'ASCB', 'ASCW', 'CALLBYNAME', 'CBOOL', 'CBYTE', 'CCHAR',
            'CCHR', 'CDATE', 'CDBL', 'CDEC', 'CHOOSE', 'CHR', 'CHR$', 'CHRB', 'CHRB$', 'CHRW',
            'CINT', 'CLNG', 'CLNG8', 'CLOSE', 'COBJ', 'COMMAND', 'COMMAND$', 'CONVERSION',
            'COS', 'CREATEOBJECT', 'CSHORT', 'CSTR', 'CURDIR', 'CTYPE', 'CVDATE', 'DATEADD',
            'DATEDIFF', 'DATEPART', 'DATESERIAL', 'DATEVALUE', 'DAY', 'DDB', 'DIR', 'DIR$',
            'EOF', 'ERROR$', 'EXP', 'FILEATTR', 'FILECOPY', 'FILEDATATIME', 'FILELEN', 'FILTER',
            'FIX', 'FORMAT', 'FORMAT$', 'FORMATCURRENCY', 'FORMATDATETIME', 'FORMATNUMBER',
            'FORMATPERCENT', 'FREEFILE', 'FV', 'GETALLSETTINGS', 'GETATTRGETOBJECT', 'GETSETTING',
            'HEX', 'HEX$', 'HOUR', 'IIF', 'IMESTATUS', 'INPUT$', 'INPUTB', 'INPUTB$', 'INPUTBOX',
            'INSTR', 'INSTRB', 'INSTRREV', 'INT', 'IPMT', 'IRR', 'ISARRAY', 'ISDATE', 'ISEMPTY',
            'ISERROR', 'ISNULL', 'ISNUMERIC', 'ISOBJECT', 'JOIN', 'LBOUND', 'LCASE', 'LCASE$',
            'LEFT', 'LEFT$', 'LEFTB', 'LEFTB$', 'LENB', 'LINEINPUT', 'LOC', 'LOF', 'LOG', 'LTRIM',
            'LTRIM$', 'MID$', 'MIDB', 'MIDB$', 'MINUTE', 'MIRR', 'MKDIR', 'MONTH', 'MONTHNAME',
            'MSGBOX', 'NOW', 'NPER', 'NPV', 'OCT', 'OCT$', 'PARTITION', 'PMT', 'PPMT', 'PV',
            'RATE', 'REPLACE', 'RIGHT', 'RIGHT$', 'RIGHTB', 'RIGHTB$', 'RMDIR', 'RND', 'RTRIM',
            'RTRIM$', 'SECOND', 'SIN', 'SLN', 'SPACE', 'SPACE$', 'SPC', 'SPLIT', 'SQRT', 'STR', 'STR$',
            'STRCOMP', 'STRCONV', 'STRING$', 'STRREVERSE', 'SYD', 'TAB', 'TAN', 'TIMEOFDAY',
            'TIMER', 'TIMESERIAL', 'TIMEVALUE', 'TODAY', 'TRIM', 'TRIM$', 'TYPENAME', 'UBOUND',
            'UCASE', 'UCASE$', 'VAL', 'WEEKDAY', 'WEEKDAYNAME', 'YEAR'
            ),
        8 => array(
            'ANY', 'ATN', 'CALENDAR', 'CIRCLE', 'CURRENCY', 'DEFBOOL', 'DEFBYTE', 'DEFCUR',
            'DEFDATE', 'DEFDBL', 'DEFDEC', 'DEFINT', 'DEFLNG', 'DEFOBJ', 'DEFSNG', 'DEFSTR',
            'DEFVAR', 'EQV', 'GOSUB', 'IMP', 'INITIALIZE', 'ISMISSING', 'LET', 'LINE', 'LSET',
            'RSET', 'SGN', 'SQR', 'TERMINATE', 'VARIANT', 'VARTYPE', 'WEND'
            ),
        ),
    'SYMBOLS' => array(
        '&', '&=', '*', '*=', '+', '+=', '-', '-=', '//', '/', '/=', '=', '\\', '\\=',
        '^', '^='
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false,
        6 => false,
        7 => false,
        8 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0600FF;',        //Constants
            2 => 'color: #FF8000;',        //Keywords
            3 => 'color: #008000;',        //Data Types
            4 => 'color: #FF0000;',        //Objects
            5 => 'color: #804040;',        //Operators
            6 => 'color: #0600FF;',        //Statements
            7 => 'color: #0600FF;',        //Functions
            8 => 'color: #0600FF;'        //Deprecated
            ),
        'COMMENTS' => array(
            1 => 'color: #008080; font-style: italic;',
            'MULTI' => 'color: #008080; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #008080; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #000000;'
            ),
        'STRINGS' => array(
            0 => 'color: #808080;'
            ),
        'NUMBERS' => array(
            0 => 'color: #FF0000;'
            ),
        'METHODS' => array(
            1 => 'color: #0000FF;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #008000;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => 'http://www.google.com/search?q={FNAMEU}+site:msdn.microsoft.com',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
        8 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 =>'.'
        ),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);

?>
