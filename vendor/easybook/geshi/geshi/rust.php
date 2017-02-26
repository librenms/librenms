<?php
/*************************************************************************************
 * rust.php
 * --------
 * Author: Edward Hart (edward.dan.hart@gmail.com)
 * Copyright: (c) 2013 Edward Hart
 * Release Version: 1.0.8.12
 * Date Started: 2013/10/20
 *
 * Rust language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2013/10/20
 *   -  First Release
 *
 * TODO (updated 2013/10/20)
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

$language_data = array(
    'LANG_NAME' => 'Rust',

    'COMMENT_SINGLE' => array('//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(),

    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array("'", '"'),
    'ESCAPE_CHAR' => '',
    'ESCAPE_REGEXP' => array(
        //Simple Single Char Escapes
        1 => "#\\\\[\\\\nrt\'\"?\n]#i",
        //Hexadecimal Char Specs
        2 => "#\\\\x[\da-fA-F]{2}#",
        //Hexadecimal Char Specs
        3 => "#\\\\u[\da-fA-F]{4}#",
        //Hexadecimal Char Specs
        4 => "#\\\\U[\da-fA-F]{8}#",
        //Octal Char Specs
        5 => "#\\\\[0-7]{1,3}#"
        ),
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_INT_CSTYLE | GESHI_NUMBER_BIN_PREFIX_0B |
        GESHI_NUMBER_HEX_PREFIX | GESHI_NUMBER_FLT_NONSCI |
        GESHI_NUMBER_FLT_NONSCI_F | GESHI_NUMBER_FLT_SCI_SHORT | GESHI_NUMBER_FLT_SCI_ZERO,

    'KEYWORDS' => array(
        // Keywords
        1 => array(
            'alt', 'as', 'assert', 'break', 'const', 'continue', 'copy', 'do',
            'else', 'enum', 'extern', 'fn', 'for', 'if',
            'impl', 'in', 'let', 'log', 'loop', 'match', 'mod', 'mut', 'of',
            'priv', 'pub', 'ref', 'return', 'self', 'static', 'struct', 'super',
            'to', 'trait', 'type', 'unsafe', 'use', 'with', 'while'
            ),
        // Boolean values
        2 => array( 'true', 'false' ),
        // Structs and built-in types
        3 => array(
            'u8', 'i8',
            'u16', 'i16',
            'u32', 'i32',
            'u64', 'i64',
            'f32', 'f64',
            'int', 'uint',
            'float',
            'bool',
            'str', 'char',
            'Argument', 'AsyncWatcher', 'BorrowRecord', 'BufReader',
            'BufWriter', 'BufferedReader', 'BufferedStream', 'BufferedWriter',
            'ByRef', 'ByteIterator', 'CFile', 'CString', 'CStringIterator',
            'Cell', 'Chain', 'Chan', 'ChanOne', 'CharIterator',
            'CharOffsetIterator', 'CharRange', 'CharSplitIterator',
            'CharSplitNIterator', 'ChunkIter', 'Condition', 'ConnectRequest',
            'Coroutine', 'Counter', 'CrateMap', 'Cycle', 'DeflateWriter',
            'Display', 'ElementSwaps', 'Enumerate', 'Exp', 'Exp1', 'FileDesc',
            'FileReader', 'FileStat', 'FileStream', 'FileWriter', 'Filter',
            'FilterMap', 'FlatMap', 'FormatSpec', 'Formatter', 'FsRequest',
            'Fuse', 'GarbageCollector', 'GetAddrInfoRequest', 'Handle',
            'HashMap', 'HashMapIterator', 'HashMapMoveIterator',
            'HashMapMutIterator', 'HashSet', 'HashSetIterator',
            'HashSetMoveIterator', 'Hint', 'IdleWatcher', 'InflateReader',
            'Info', 'Inspect', 'Invert', 'IoError', 'Isaac64Rng', 'IsaacRng',
            'LineBufferedWriter', 'Listener', 'LocalHeap', 'LocalStorage',
            'Loop', 'Map', 'MatchesIndexIterator', 'MemReader', 'MemWriter',
            'MemoryMap', 'ModEntry', 'MoveIterator', 'MovePtrAdaptor',
            'MoveRevIterator', 'NoOpRunnable', 'NonCopyable', 'Normal',
            'OSRng', 'OptionIterator', 'Parser', 'Path', 'Peekable',
            'Permutations', 'Pipe', 'PipeStream', 'PluralArm', 'Port',
            'PortOne', 'Process', 'ProcessConfig', 'ProcessOptions',
            'ProcessOutput', 'RC', 'RSplitIterator', 'RandSample', 'Range',
            'RangeInclusive', 'RangeStep', 'RangeStepInclusive', 'Rc', 'RcMut',
            'ReaderRng', 'Repeat', 'ReprVisitor', 'RequestData',
            'ReseedWithDefault', 'ReseedingRng', 'Scan', 'SchedOpts',
            'SelectArm', 'SharedChan', 'SharedPort', 'SignalWatcher',
            'SipState', 'Skip', 'SkipWhile', 'SocketAddr', 'SplitIterator',
            'StackPool', 'StackSegment', 'StandardNormal', 'StdErrLogger',
            'StdIn', 'StdOut', 'StdReader', 'StdRng', 'StdWriter',
            'StrSplitIterator', 'StreamWatcher', 'TTY', 'Take', 'TakeWhile',
            'Task', 'TaskBuilder', 'TaskOpts', 'TcpAcceptor', 'TcpListener',
            'TcpStream', 'TcpWatcher', 'Timer', 'TimerWatcher', 'TrieMap',
            'TrieMapIterator', 'TrieSet', 'TrieSetIterator', 'Tube',
            'UdpSendRequest', 'UdpSocket', 'UdpStream', 'UdpWatcher', 'Unfold',
            'UnixAcceptor', 'UnixListener', 'UnixStream', 'Unwinder',
            'UvAddrInfo', 'UvError', 'UvEventLoop', 'UvFileStream',
            'UvIoFactory', 'UvPausibleIdleCallback', 'UvPipeStream',
            'UvProcess', 'UvRemoteCallback', 'UvSignal', 'UvTTY',
            'UvTcpAcceptor', 'UvTcpListener', 'UvTcpStream', 'UvTimer',
            'UvUdpSocket', 'UvUnboundPipe', 'UvUnixAcceptor', 'UvUnixListener',
            'VecIterator', 'VecMutIterator', 'Weighted', 'WeightedChoice',
            'WindowIter', 'WriteRequest', 'XorShiftRng', 'Zip', 'addrinfo',
            'uv_buf_t', 'uv_err_data', 'uv_process_options_t', 'uv_stat_t',
            'uv_stdio_container_t', 'uv_timespec_t'
            ),
        // Enums
        4 => array(
            'Alignment', 'Count', 'Either', 'ExponentFormat', 'FPCategory',
            'FileAccess', 'FileMode', 'Flag', 'IoErrorKind', 'IpAddr',
            'KeyValue', 'MapError', 'MapOption', 'MemoryMapKind', 'Method',
            'NullByteResolution', 'Option', 'Ordering', 'PathPrefix', 'Piece',
            'PluralKeyword', 'Position', 'Protocol', 'Result', 'SchedHome',
            'SchedMode', 'SeekStyle', 'SendStr', 'SignFormat',
            'SignificantDigits', 'Signum', 'SocketType', 'StdioContainer',
            'TaskResult', 'TaskType', 'UvSocketAddr', 'Void', 'uv_handle_type',
            'uv_membership', 'uv_req_type'
            )
        ),
    'SYMBOLS' => array(
        '(', ')', '{', '}', '[', ']',
        '+', '-', '*', '/', '%',
        '&', '|', '^', '!', '<', '>', '~', '@',
        ':',
        ';', ',',
        '='
        ),

    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => true,
        2 => true,
        3 => true,
        4 => true
        ),

    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #708;',
            2 => 'color: #219;',
            3 => 'color: #05a;',
            4 => 'color: #800;'
            ),
        'COMMENTS' => array(
            0 => 'color: #a50; font-style: italic;',
            'MULTI' => 'color: #a50; font-style: italic;'
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
            0 => 'color: #a11;'
            ),
        'NUMBERS' => array(
            0 => 'color: #0000dd;',
            GESHI_NUMBER_BIN_PREFIX_0B => 'color: #208080;',
            GESHI_NUMBER_OCT_PREFIX => 'color: #208080;',
            GESHI_NUMBER_HEX_PREFIX => 'color: #208080;',
            GESHI_NUMBER_FLT_SCI_SHORT => 'color:#800080;',
            GESHI_NUMBER_FLT_SCI_ZERO => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI_F => 'color:#800080;',
            GESHI_NUMBER_FLT_NONSCI => 'color:#800080;'
            ),
        'BRACKETS' => array(''),
        'METHODS' => array(
            1 => 'color: #164;'
            ),
        'SYMBOLS' => array(
            0 => ''
            ),
        'REGEXPS' => array(
            ),
        'SCRIPT' => array(
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => ''
        ),
    'OOLANG' => true,
    'OBJECT_SPLITTERS' => array(
        1 => '::'
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
