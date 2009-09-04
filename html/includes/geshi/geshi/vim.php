<?php

/*************************************************************************************
 * vim.php
 * ----------------
 * Author: Swaroop C H (swaroop@swaroopch.com)
 * Copyright: (c) 2008 Swaroop C H (http://www.swaroopch.com)
 * Release Version: 1.0.8.3
 * Date Started: 2008/10/19
 *
 * Vim scripting language file for GeSHi.
 *
 * Reference: http://qbnz.com/highlighter/geshi-doc.html#language-files
 * All keywords scraped from `:help expression-commands`.
 * All method names scraped from `:help function-list`.
 *
 * CHANGES
 * -------
 * 2008/10/19 (1.0.8.2)
 * - Started.
 *
 * TODO (updated 2008/10/19)
 * -------------------------
 * - Fill out list of zillion commands
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
    'LANG_NAME' => 'Vim Script',
    'COMMENT_SINGLE' => array(),
    'COMMENT_REGEXP' => array(
        1 => "/^\".*$/m"
        ),
    'COMMENT_MULTI' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '\\',
    'KEYWORDS' => array(
        1 => array(
            'brea', 'break', 'call', 'cat', 'catc',
            'catch', 'con', 'cont', 'conti',
            'contin', 'continu', 'continue', 'ec', 'echo',
            'echoe', 'echoer', 'echoerr', 'echoh',
            'echohl', 'echom', 'echoms', 'echomsg', 'echon',
            'el', 'els', 'else', 'elsei', 'elseif',
            'en', 'end', 'endi', 'endif', 'endfo',
            'endfor', 'endt', 'endtr', 'endtry', 'endw',
            'endwh', 'endwhi', 'endwhil', 'endwhile', 'exe', 'exec', 'execu',
            'execut', 'execute', 'fina', 'final', 'finall', 'finally', 'for',
            'fun', 'func', 'funct', 'functi', 'functio', 'function', 'if', 'in',
            'let', 'lockv', 'lockva', 'lockvar', 'retu', 'retur', 'return', 'th',
            'thr', 'thro', 'throw', 'try', 'unl', 'unle', 'unlet', 'unlo', 'unloc',
            'unlock', 'unlockv', 'unlockva', 'unlockvar', 'wh', 'whi', 'whil',
            'while'
            ),
        2 => array(
            'autocmd', 'com', 'comm', 'comma', 'comman', 'command', 'comc',
            'comcl', 'comcle', 'comclea', 'comclear', 'delc', 'delco',
            'delcom', 'delcomm', 'delcomma', 'delcomman', 'delcommand',
            '-nargs' # TODO There are zillions of commands to be added here from http://vimdoc.sourceforge.net/htmldoc/usr_toc.html
            ),
        3 => array(
            'abs', 'add', 'append', 'argc', 'argidx', 'argv', 'atan',
            'browse', 'browsedir', 'bufexists', 'buflisted', 'bufloaded',
            'bufname', 'bufnr', 'bufwinnr', 'byte2line', 'byteidx',
            'ceil', 'changenr', 'char2nr', 'cindent', 'clearmatches',
            'col', 'complete', 'complete_add', 'complete_check', 'confirm',
            'copy', 'cos', 'count', 'cscope_connection', 'cursor',
            'deepcopy', 'delete', 'did_filetype', 'diff_filler',
            'diff_hlID', 'empty', 'escape', 'eval', 'eventhandler',
            'executable', 'exists', 'extend', 'expand', 'feedkeys',
            'filereadable', 'filewritable', 'filter', 'finddir',
            'findfile', 'float2nr', 'floor', 'fnameescape', 'fnamemodify',
            'foldclosed', 'foldclosedend', 'foldlevel', 'foldtext',
            'foldtextresult', 'foreground', 'garbagecollect',
            'get', 'getbufline', 'getbufvar', 'getchar', 'getcharmod',
            'getcmdline', 'getcmdpos', 'getcmdtype', 'getcwd', 'getfperm',
            'getfsize', 'getfontname', 'getftime', 'getftype', 'getline',
            'getloclist', 'getmatches', 'getpid', 'getpos', 'getqflist',
            'getreg', 'getregtype', 'gettabwinvar', 'getwinposx',
            'getwinposy', 'getwinvar', 'glob', 'globpath', 'has',
            'has_key', 'haslocaldir', 'hasmapto', 'histadd', 'histdel',
            'histget', 'histnr', 'hlexists', 'hlID', 'hostname', 'iconv',
            'indent', 'index', 'input', 'inputdialog', 'inputlist',
            'inputrestore', 'inputsave', 'inputsecret', 'insert',
            'isdirectory', 'islocked', 'items', 'join', 'keys', 'len',
            'libcall', 'libcallnr', 'line', 'line2byte', 'lispindent',
            'localtime', 'log10', 'map', 'maparg', 'mapcheck', 'match',
            'matchadd', 'matcharg', 'matchdelete', 'matchend', 'matchlist',
            'matchstr', 'max', 'min', 'mkdir', 'mode', 'nextnonblank',
            'nr2char', 'pathshorten', 'pow', 'prevnonblank', 'printf',
            'pumvisible', 'range', 'readfile', 'reltime', 'reltimestr',
            'remote_expr', 'remote_foreground', 'remote_peek',
            'remote_read', 'remote_send', 'remove', 'rename', 'repeat',
            'resolve', 'reverse', 'round', 'search', 'searchdecl',
            'searchpair', 'searchpairpos', 'searchpos', 'server2client',
            'serverlist', 'setbufvar', 'setcmdpos', 'setline',
            'setloclist', 'setmatches', 'setpos', 'setqflist', 'setreg',
            'settabwinvar', 'setwinvar', 'shellescape', 'simplify', 'sin',
            'sort', 'soundfold', 'spellbadword', 'spellsuggest', 'split',
            'sqrt', 'str2float', 'str2nr', 'strftime', 'stridx', 'string',
            'strlen', 'strpart', 'strridx', 'strtrans', 'submatch',
            'substitute', 'synID', 'synIDattr', 'synIDtrans', 'synstack',
            'system', 'tabpagebuflist', 'tabpagenr', 'tabpagewinnr',
            'taglist', 'tagfiles', 'tempname', 'tolower', 'toupper', 'tr',
            'trunc', 'type', 'values', 'virtcol', 'visualmode', 'winbufnr',
            'wincol', 'winheight', 'winline', 'winnr', 'winrestcmd',
            'winrestview', 'winsaveview', 'winwidth', 'writefile'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '[', ']', '{', '}', '!', '%', '&', '*', '|', '/', '<', '>',
        '^', '-', '+', '~', '?', ':', '$', '@', '.'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true
        ),
    'STYLES' => array(
        'BRACKETS' => array(
            0 => 'color: #000000;'
            ),
        'COMMENTS' => array(
            1 => 'color: #adadad; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            0 => ''
            ),
        'KEYWORDS' => array(
            1 => 'color: #804040;',
            2 => 'color: #668080;',
            3 => 'color: #25BB4D;'
            ),
        'METHODS' => array(
            0 => 'color: #000000;',
            ),
        'NUMBERS' => array(
            0 => 'color: #000000; font-weight:bold;'
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            ),
        'STRINGS' => array(
            0 => 'color: #C5A22D;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #000000;'
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => ''
        ),
    'OOLANG' => false, //Save some time as OO identifiers aren't used
    'OBJECT_SPLITTERS' => array(),
    'REGEXPS' => array(
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(),
    'HIGHLIGHT_STRICT_BLOCK' => array()
);

?>
