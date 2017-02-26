<?php
/**
 * verilog.php
 * -----------
 * Author: Günter Dannoritzer <dannoritzer@web.de>
 * Copyright: (C) 2008 Günter Dannoritzer
 * Release Version: 1.0.8.11
 * Date Started: 2008/05/28
 *
 * Verilog language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2008/05/29
 *   -  added regular expression to find numbers of the form 4'b001xz
 *   -  added regular expression to find values for `timescale command
 *   -  extended macro keywords
 *
 * TODO (updated 2008/05/29)
 * -------------------------
 *
 * 2013/01/08
 *   -  extended keywords to include system keywords
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
    'LANG_NAME' => 'Verilog',
    'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(1 => '/\/\/(?:\\\\\\\\|\\\\\\n|.)*$/m'),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        // keywords
        1 => array(
            'accept_on','alias',
            'always','always_comb','always_ff','always_latch','and','assert',
            'assign','assume','automatic','before','begin','bind','bins','binsof',
            'bit','break','buf','bufif0','bufif1','byte','case','casex','casez',
            'cell','chandle','checker','class','clocking','cmos','config','const',
            'constraint','context','continue','cover','covergroup','coverpoint','cross',
            'deassign','default','defparam','design','disable','dist','do','edge','else',
            'end','endcase','endchecker','endclass','endclocking','endconfig',
            'endfunction','endgenerate','endgroup','endinterface','endmodule',
            'endpackage','endprimitive','endprogram','endproperty','endspecify',
            'endsequence','endtable','endtask','enum','event','eventually','expect',
            'export','extends','extern','final','first_match','for','force','foreach',
            'forever','fork','forkjoin','function','generate','genvar','global',
            'highz0','highz1','if','iff','ifnone','ignore_bins','illegal_bins',
            'implies','import','incdir','include','initial','inout','input','inside',
            'instance','int','integer','interface','intersect','join','join_any',
            'join_none','large','let','liblist','library','local','localparam',
            'logic','longint','macromodule','matches','medium','modport','module','nand',
            'negedge','new','nexttime','nmos','nor','noshowcancelled','not','notif0',
            'notif1','null','or','output','package','packed','parameter','pmos','posedge',
            'primitive','priority','program','property','protected','pull0','pull1',
            'pulldown','pullup','pulsestyle_ondetect','pulsestyle_onevent','pure',
            'rand','randc','randcase','randsequence','rcmos','real','realtime','ref',
            'reg','reject_on','release','repeat','restrict','return','rnmos','rpmos',
            'rtran','rtranif0','rtranif1','s_always','s_eventually','s_nexttime',
            's_until','s_until_with','scalared','sequence','shortint','shortreal',
            'showcancelled','signed','small','solve','specify','specparam','static',
            'string','strong','strong0','strong1','struct','super','supply0','supply1',
            'sync_accept_on','sync_reject_on','table','tagged','task','this','throughout',
            'time','timeprecision','timeunit','tran','tranif0','tranif1','tri','tri0',
            'tri1','triand','trior','trireg','type','typedef','union','unique','unique0',
            'unsigned','until','until_with','untyped','use','uwire','var','vectored',
            'virtual','void','wait','wait_order','wand','weak','weak0','weak1','while',
            'wildcard','wire','with','within','wor','xnor','xor'
            ),
        // system tasks
        2 => array(
            '$display', '$monitor',
            '$dumpall', '$dumpfile', '$dumpflush', '$dumplimit', '$dumpoff',
            '$dumpon', '$dumpvars',
            '$fclose', '$fdisplay', '$fopen',
            '$finish', '$fmonitor', '$fstrobe', '$fwrite',
            '$fgetc', '$ungetc', '$fgets', '$fscanf', '$fread', '$ftell',
            '$fseek', '$frewind', '$ferror', '$fflush', '$feof',
            '$random',
            '$readmemb', '$readmemh', '$readmemx',
            '$signed', '$stime', '$stop',
            '$strobe', '$time', '$unsigned', '$write'
            ),
        // macros
        3 => array(
            '`default-net', '`define',
            '`celldefine', '`default_nettype', '`else', '`elsif', '`endcelldefine',
            '`endif', '`ifdef', '`ifndef', '`include', '`line', '`nounconnected_drive',
            '`resetall', '`timescale', '`unconnected_drive', '`undef'
            ),
        ),
    'SYMBOLS' => array(
        '(', ')', '{', '}', '[', ']', '=', '+', '-', '*', '/', '!', '%',
        '^', '&', '|', '~',
        '?', ':',
        '#', '<<', '<<<',
        '>', '<', '>=', '<=',
        '@', ';', ','
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #A52A2A; font-weight: bold;',
            2 => 'color: #9932CC;',
            3 => 'color: #008800;'
            ),
        'COMMENTS' => array(
            1 => 'color: #00008B; font-style: italic;',
            'MULTI' => 'color: #00008B; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => 'color: #9F79EE'
            ),
        'BRACKETS' => array(
            0 => 'color: #9F79EE;'
            ),
        'STRINGS' => array(
            0 => 'color: #FF00FF;'
            ),
        'NUMBERS' => array(
            0 => 'color: #ff0055;'
            ),
        'METHODS' => array(
            1 => 'color: #202020;',
            2 => 'color: #202020;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #5D478B;'
            ),
        'REGEXPS' => array(
            0 => 'color: #ff0055;',
            1 => 'color: #ff0055;',
            ),
        'SCRIPT' => array(
            0 => '',
            1 => '',
            2 => '',
            3 => ''
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => ''
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        1 => ''
        ),
    'REGEXPS' => array(
        // numbers
        0 => "\d'[bdh][0-9_a-fA-FxXzZ]+",
        // time -> 1, 10, or 100; s, ms, us, ns, ps, of fs
        1 => "1[0]{0,2}[munpf]?s"
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        1 => ''
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        0 => true,
        1 => true,
        2 => true,
        3 => true
        ),
    'TAB_WIDTH' => 4
);
