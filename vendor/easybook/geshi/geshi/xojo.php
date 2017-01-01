<?php
/*************************************************************************************
 * xojo.php
 * --------
 * Author: Dr Garry Pettet (contact@garrypettet.com)
 * Copyright: (c) 2014 Dr Garry Pettet (http://garrypettet.com)
 * Release Version: 1.0.0
 * Date Started: 2014/10/19
 *
 * Xojo language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2014/10/19 (1.0.8.12)
 *  -  First Release
 *
 * TODO (updated 2014/10/19)
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
    'LANG_NAME' => 'Xojo',
    'COMMENT_SINGLE' => array(1 => "'", 2 => '//', 3 => 'rem'),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
	'NUMBERS' => array(
	        1 => GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_INT_CSTYLE, // integers
	        2 => GESHI_NUMBER_FLT_NONSCI // floating point numbers
	        ),    
    'KEYWORDS' => array(
        //Keywords
        1 => array(
	        'AddHandler', 'AddressOf', 'Aggregates', 'And', 'Array', 'As', 'Assigns', 'Attributes', 
	        'Break', 'ByRef', 'ByVal', 'Call', 'Case', 'Catch', 'Class', 'Const', 'Continue',
	        'CType', 'Declare', 'Delegate', 'Dim', 'Do', 'DownTo', 'Each', 'Else', 'Elseif', 'End', 
	        'Enum', 'Event', 'Exception', 'Exit', 'Extends', 'False', 'Finally', 'For', 
	        'Function', 'Global', 'GoTo', 'Handles', 'If', 'Implements', 'In', 'Inherits', 
	        'Inline68K', 'Interface', 'Is', 'IsA', 'Lib', 'Loop', 'Me', 'Mod', 'Module', 
	        'Namespace', 'New', 'Next', 'Nil', 'Not', 'Object', 'Of', 'Optional', 'Or', 
	        'ParamArray', 'Private', 'Property', 'Protected', 'Public', 'Raise', 
	        'RaiseEvent', 'Rect', 'Redim', 'RemoveHandler', 'Return', 'Select', 'Self', 'Shared', 
	        'Soft', 'Static', 'Step', 'Sub', 'Super', 'Then', 'To', 'True', 'Try',
	        'Until', 'Using', 'Wend', 'While', 'With', 'WeakAddressOf', 'Xor'
            ),
        //Data Types
        2 => array(
            'Boolean', 'CFStringRef', 'CString', 'Currency', 'Double', 'Int8', 'Int16', 'Int32',
            'Int64', 'Integer', 'OSType', 'PString', 'Ptr', 'Short', 'Single', 'String', 
            'Structure', 'UInt8', 'UInt16', 'UInt32', 'UInt64', 'UShort', 'WindowPtr', 
            'WString', 'XMLNodeType'
            ),
        //Compiler Directives
        3 => array(
            '#Bad', '#Else', '#Endif', '#If', '#Pragma', '#Tag'
            ),
        ),
    'SYMBOLS' => array(
        '+', '-', '*', '=', '/', '>', '<', '^', '(', ')', '.'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #0000FF;',  // keywords
            2 => 'color: #0000FF;',  // primitive data types
            3 => 'color: #0000FF;',  // compiler commands
            ),
        'COMMENTS' => array(
            1 => 'color: #7F0000;',
            'MULTI' => 'color: #7F0000;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #008080;'
            ),
        'BRACKETS' => array(
            0 => 'color: #000000;'
            ),
        'STRINGS' => array(
            0 => 'color: #6500FE;'
            ),
        'NUMBERS' => array(
            1 => 'color: #326598;', // integers
            2 => 'color: #006532;', // floating point numbers
            ),
        'METHODS' => array(
            1 => 'color: #000000;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #000000;'
            ),
        'REGEXPS' => array(
	        1 => 'color: #326598;', // &h hex numbers
	        2 => 'color: #326598;', // &b hex numbers
	        3 => 'color: #326598;', // &o hex numbers
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => 'http://docs.xojo.com/index.php/{FNAMEU}',
        2 => 'http://docs.xojo.com/index.php/{FNAMEU}',
        3 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 =>'.'
        ),
    'REGEXPS' => array(
		1 => array( // &h numbers
		    // search for &h, then any number of letters a-f or numbers 0-9
		    GESHI_SEARCH => '(&amp;h[0-9a-fA-F]*\b)',
		    GESHI_REPLACE => '\\1',
		    GESHI_MODIFIERS => '',
		    GESHI_BEFORE => '',
		    GESHI_AFTER => ''
		    ),
		2 => array( // &b numbers
		    // search for &b, then any number of 0-1 digits
		    GESHI_SEARCH => '(&amp;b[0-1]*\b)',
		    GESHI_REPLACE => '\\1',
		    GESHI_MODIFIERS => '',
		    GESHI_BEFORE => '',
		    GESHI_AFTER => ''
		    ),
		3 => array( // &o octal numbers
		    // search for &o, then any number of 0-7 digits
		    GESHI_SEARCH => '(&amp;o[0-7]*\b)',
		    GESHI_REPLACE => '\\1',
		    GESHI_MODIFIERS => '',
		    GESHI_BEFORE => '',
		    GESHI_AFTER => ''
		    )
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);

