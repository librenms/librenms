<?php
/*************************************************************************************
 * vhdl.php
 * --------
 * Author: Alexander 'E-Razor' Krause (admin@erazor-zone.de)
 * Copyright: (c) 2005 Alexander Krause
 * Release Version: 1.0.8.3
 * Date Started: 2005/06/15
 *
 * VHDL (VHSICADL, very high speed integrated circuit HDL) language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2008/05/23 (1.0.7.22)
 *  -  Added description of extra language features (SF#1970248)
 *  -  Optimized regexp group 0 somewhat
 * 2006/06/15 (1.0.0)
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
    'LANG_NAME' => 'VHDL',
    'COMMENT_SINGLE' => array(1 => '--'),
    'COMMENT_MULTI' => array('%' => '%'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '',
    'KEYWORDS' => array(
        /*keywords*/
        1 => array(
            'access','after','alias','all','assert','attribute','architecture','begin',
            'block','body','buffer','bus','case','component','configuration','constant',
            'disconnect','downto','else','elsif','end','entity','exit','file','for',
            'function','generate','generic','group','guarded','if','impure','in',
            'inertial','inout','is','label','library','linkage','literal','loop',
            'map','new','next','null','of','on','open','others','out','package',
            'port','postponed','procedure','process','pure','range','record','register',
            'reject','report','return','select','severity','signal','shared','subtype',
            'then','to','transport','type','unaffected','units','until','use','variable',
            'wait','when','while','with','note','warning','error','failure','and',
            'or','xor','not','nor','used','memory','segments','dff','dffe','help_id',
            'mod','info','latch','rising_edge','falling_edge'
        ),
        /*types*/
        2 => array(
            'bit','bit_vector','character','boolean','integer','real','time','string',
            'severity_level','positive','natural','signed','unsigned','line','text',
            'std_logic','std_logic_vector','std_ulogic','std_ulogic_vector','qsim_state',
            'qsim_state_vector','qsim_12state','qsim_12state_vector','qsim_strength',
            'mux_bit','mux_vector','reg_bit','reg_vector','wor_bit','wor_vector',
            'work','ieee','std_logic_signed','std_logic_1164','std_logic_arith',
            'numeric_std'

        ),
        /*operators*/
    ),
    'SYMBOLS' => array(
        '[', ']', '(', ')',
        ';',':',
        '<','>','=','<=',':=','=>','=='
    ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000080; font-weight: bold;',
            2 => 'color: #0000ff;'
            ),
        'COMMENTS' => array(
            1 => 'color: #008000; font-style: italic;',
            'MULTI' => 'color: #008000; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #000099; font-weight: bold;'
            ),
        'BRACKETS' => array(
            0 => 'color: #000066;'
            ),
        'STRINGS' => array(
            0 => 'color: #7f007f;'
            ),
        'NUMBERS' => array(
            0 => 'color: #ff0000;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #000066;'
            ),
        'REGEXPS' => array(
            0 => 'color: #ff0000;',
            1 => 'color: #ff0000;'
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => ''
    ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        //Hex numbers and scientific notation for numbers
        0 => '(\b0x[0-9a-fA-F]+|\b\d[0-9a-fA-F]+[hH])|'.
            '(\b\d+?(\.\d+?)?E[+\-]?\d+)|(\bns)|'.
            "('[0-9a-zA-Z]+(?!'))",
        //Number characters?
        1 => "\b(''\d'')"
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        )
);

?>
