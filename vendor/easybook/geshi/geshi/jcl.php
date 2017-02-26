<?php
/*************************************************************************************
 * jcl.php
 * -----------
 * Author: Ramesh Vishveshwar (ramesh.vishveshwar@gmail.com)
 * Copyright: (c) 2012 Ramesh Vishveshwar (http://thecodeisclear.in)
 * Release Version: 1.0.8.11
 * Date Started: 2011/09/16
 *
 * JCL (MVS), DFSORT, IDCAMS language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2011/09/16 (1.0.0)
 *   -  Internal Release (for own blog/testing)
 * 2012/09/22 (1.0.1)
 *   - Released with support for DFSORT, ICETOOL, IDCAMS
 *   - Added support for Symbolic variables in JCL
 *   - Added support for TWS OPC variables
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
    'LANG_NAME' => 'JCL',
    'COMMENT_SINGLE' => array(),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(
        // Comments identified using REGEX
        // Comments start with //* but should not be followed by % (TWS) or + (some JES3 stmts)
        3 => "\/\/\*[^%](.*?)(\n)"
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_UPPER,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        1 => array(
            'COMMAND', 'CNTL', 'DD', 'ENDCNTL', 'EXEC', 'IF', 'THEN', 'ELSE',
            'ENDIF', 'JCLLIB', 'JOB', 'OUTPUT', 'PEND',
            'PROC', 'SET', 'XMIT'
            ),
        2 => array (
            'PGM','CLASS','NOTIFY','MSGCLASS','DSN','KEYLEN','LABEL','LIKE',
            'RECFM','LRECL','DCB','DSORG','BLKSIZE','SPACE','STORCLAS',
            'DUMMY','DYNAM','AVGREC','BURST','DISP','UNIT','VOLUME',
            'MSGLEVEL','REGION'
            ),
        // Keywords set 3: DFSORT, ICETOOL
        3 => array (
            'ALTSEQ','DEBUG','END','INCLUDE','INREC','MERGE','MODS','OMIT',
            'OPTION','OUTFIL','OUTREC','RECORD','SORT','SUM',
            'COPY','COUNT','DEFAULTS','DISPLAY','MODE','OCCUR','RANGE',
            'SELECT','STATS','UNIQUE','VERIFY'
            ),
        // Keywords set 4: IDCAMS
        4 => array (
            'ALTER','BLDINDEX','CNVTCAT','DEFINE','ALIAS','ALTERNATEINDEX',
            'CLUSTER','GENERATIONDATAGROUP','GDG','NONVSAM','PAGESPACE','PATH',
            /* 'SPACE',*/'USERCATALOG','DELETE','EXAMINE','EXPORT','DISCONNECT',
            'EXPORTRA','IMPORT','CONNECT','IMPORTRA','LISTCAT','LISTCRA',
            'PRINT','REPRO','RESETCAT'//,'VERIFY'
            )
        ),
    'SYMBOLS' => array(
        '(',')','=',',','>','<'
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
            1 => 'color: #FF0000;',
            2 => 'color: #21A502;',
            3 => 'color: #FF00FF;',
            4 => 'color: #876C00;'
            ),
        'COMMENTS' => array(
            0 => 'color: #0000FF;',
            //1 => 'color: #0000FF;',
            //2 => 'color: #0000FF;',
            3 => 'color: #0000FF;'
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
            0 => 'color: #336633;'
            ),
        'METHODS' => array(
            1 => '',
            2 => ''
            ),
        'SYMBOLS' => array(
            0 => 'color: #FF7400;'
            ),
        'REGEXPS' => array(
            0 => 'color: #6B1F6B;',
            1 => 'color: #6B1F6B;',
            2 => 'color: #6B1F6B;'
            ),
        'SCRIPT' => array(
            0 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        // JCL book at IBM Bookshelf is http://publibz.boulder.ibm.com/cgi-bin/bookmgr_OS390/handheld/Connected/BOOKS/IEA2B680/CONTENTS?SHELF=&DT=20080604022956#3.1
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        // The following regular expressions solves three purposes
        // - Identify Temp Variables in JCL (e.g. &&TEMP)
        // - Symbolic variables in JCL (e.g. &SYSUID)
        // - TWS OPC Variables (e.g. %OPC)
        // Thanks to Simon for pointing me to this
        0 => '&amp;&amp;[a-zA-Z]{1,8}[0-9]{0,}',
        1 => '&amp;[a-zA-Z]{1,8}[0-9]{0,}',
        2 => '&amp;|\?|%[a-zA-Z]{1,8}[0-9]{0,}'
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);
