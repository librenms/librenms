<?php
/*************************************************************************************
 * cobol.php
 * ----------
 * Author: BenBE (BenBE@omorphia.org)
 * Copyright: (c) 2007-2008 BenBE (http://www.omorphia.de/)
 * Release Version: 1.0.8.12
 * Date Started: 2007/07/02
 *
 * COBOL language file for GeSHi.
 *
 * Most of the compiler directives, reserved words and intrinsic functions are
 * from the 2009 COBOL Draft Standard, Micro Focus, and GNU Cobol. The lists of
 * these were found in the draft standard (Sections 8.9, 8.10, 8.11 and 8.12),
 * Micro Focus' COBOL Language Reference and the GNU Cobol FAQ.
 *
 * CHANGES
 * -------
 * 2013/11/17 (1.0.8.12)
 *  -  Changed compiler directives to be handled like comments.
 *  -  Fixed bug where keywords in identifiers were highlighted.
 * 2013/08/19 (1.0.8.12)
 *  -  Added more intrinsic functions, reserved words, and compiler directives
 *     from the (upcoming) standard.
 * 2013/07/07 (1.0.8.12)
 *  -  Added more reserved words, compiler directives and intrinsic functions.
 *  -  Added modern comment syntax and corrected the other one.
 *  -  Set OOLANG to true and added an object splitter.
 *  -  Added extra symbols.
 *  -  Fixed bug where scope terminators were only the statement in
 *     end-statement was highlighted.
 *
 * TODO (updated 2013/11/17)
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
    'LANG_NAME' => 'COBOL',
    'COMMENT_SINGLE' => array(
        1 => '*>', // COBOL 2002 inline comment
        2 => '>>'  // COBOL compiler directive indicator
        ),
    'COMMENT_MULTI' => array(),
    'COMMENT_REGEXP' => array(
        1 => '/^......(\*.*?$)/m', // Fixed-form comment
        2 => '/\$SET.*/i'          // MF compiler directive indicator
        ),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"', "'"),
    'ESCAPE_CHAR' => '',
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC |
        GESHI_NUMBER_FLT_NONSCI |
        GESHI_NUMBER_FLT_SCI_SHORT |
        GESHI_NUMBER_FLT_SCI_ZERO,
    'KEYWORDS' => array(
        // Statements containing spaces. These are separate to other statements
        // so that they are highlighted correctly.
        1 => array(
            'DELETE FILE', 'GO TO', 'NEXT SENTENCE', 'XML GENERATE',
            'XML PARSE'
            ),

        2 => array( // Other Reserved Words
            '3-D', 'ABSENT', 'ABSTRACT', 'ACCESS', 'ACQUIRE',
            'ACTION', 'ACTIVE-CLASS', 'ACTIVE-X', 'ACTUAL', 'ADDRESS',
            'ADDRESS-ARRAY', 'ADDRESS-OFFSET', 'ADJUSTABLE-COLUMNS',
            'ADVANCING', 'AFP-5A', 'AFTER', 'ALIGNED', 'ALIGNMENT', 'ALL',
            'ALLOW', 'ALLOWING', 'ALPHABET', 'ALPHABETIC',
            'ALPHABETIC-LOWER', 'ALPHABETIC-UPPER', 'ALPHANUMERIC',
            'ALPHANUMERIC-EDITED', 'ALSO', 'ALTERNATE', 'AND', 'ANY',
            'ANYCASE',
            'APPLY', 'ARE', 'AREA', 'AREAS', 'ARGUMENT-NUMBER',
            'ARGUMENT-VALUE',
            'ARITHMETIC', 'AS', 'ASCENDING',
            'ASSEMBLY-ATTRIBUTES', 'ASSIGN', 'AT', 'ATTRIBUTE', 'AUTHOR',
            'AUTO', 'AUTO-DECIMAL', 'AUTO-HYPHEN-SKIP', 'AUTO-MINIMIZE',
            'AUTO-RESIZE', 'AUTO-SKIP', 'AUTO-SPIN', 'AUTOMATIC',
            'AUTOTERMINATE', 'AWAY-FROM-ZERO',
            'AX-EVENT-LIST', 'B-AND', 'B-EXOR', 'B-LEFT',
            'B-NOT', 'B-OR', 'B-RIGHT', 'B-XOR', 'BACKGROUND-COLOR',
            'BACKGROUND-COLOUR', 'BACKGROUND-HIGH', 'BACKGROUND-LOW',
            'BACKGROUND-STANDARD', 'BACKWARD', 'BAR', 'BASED', 'BASIS', 'BEEP',
            'BEFORE', 'BEGINNING', 'BELL', 'BINARY', 'BINARY-CHAR',
            'BINARY-DOUBLE', 'BINARY-LONG', 'BINARY-SHORT', 'BIND', 'BIT',
            'BITMAP', 'BITMAP-END', 'BITMAP-HANDLE', 'BITMAP-NUMBER',
            'BITMAP-RAW-HEIGHT', 'BITMAP-RAW-WIDTH', 'BITMAP-SCALE',
            'BITMAP-START', 'BITMAP-TIMER', 'BITMAP-TRAILING', 'BITMAP-WIDTH',
            'BLANK', 'BLINK', 'BLINKING', 'BLOB', 'BLOB-FILE', 'BLOB-LOCATOR',
            'BLOCK', 'BOLD', 'BOOLEAN', 'BOTTOM', 'BOX', 'BOXED', 'BROWSING',
            'BUSY', 'BUTTONS', 'BY', 'C01', 'C02', 'C03', 'C04',
            'C05',
            'C06', 'C07', 'C08', 'C09', 'C10', 'C11', 'C12', 'CALENDAR-FONT',
            'CALLED', 'CANCEL-BUTTON', 'CAPACITY', 'CATCH', 'CBL',
            'CBL-CTR', 'CCOL', 'CD', 'CELL', 'CELL-COLOR', 'CELL-DATA',
            'CELL-FONT', 'CELL-PROTECTION', 'CELLS', 'CENTER', 'CENTERED',
            'CENTERED-HEADINGS', 'CENTURY-DATE', 'CENTURY-DAY', 'CF', 'CH',
            'CHAINING', 'CHANGED', 'CHAR-VARYING',
            'CHARACTER',
            'CHARACTERS', 'CHART', 'CHECK-BOX', 'CHECKING', 'CLASS',
            'CLASS-ATTRIBUTES', 'CLASS-CONTROL', 'CLASS-ID', 'CLASS-OBJECT',
            'CLASSIFICATION',
            'CLEAR-SELECTION', 'CLINE', 'CLINES', 'CLOB', 'CLOB-FILE',
            'CLOB-LOCATOR', 'CLOCK-UNITS', 'COBOL', 'CODE', 'CODE-SET',
            'COERCION', 'COL', 'COLLATING', 'COLORS', 'COLOUR',
            'COLOURS', 'COLS', 'COLUMN', 'COLUMN-COLOR', 'COLUMN-DIVIDERS',
            'COLUMN-FONT', 'COLUMN-HEADINGS', 'COLUMN-PROTECTION', 'COLUMNS',
            'COM-REG', 'COMBO-BOX', 'COMMA', 'COMMITMENT', 'COMMON',
            'COMMUNICATION', 'COMP', 'COMP-0', 'COMP-1', 'COMP-2', 'COMP-3',
            'COMP-4', 'COMP-5', 'COMP-6', 'COMP-X', 'COMPRESSION',
            'COMPUTATIONAL', 'COMPUTATIONAL-0', 'COMPUTATIONAL-1',
            'COMPUTATIONAL-2', 'COMPUTATIONAL-3', 'COMPUTATIONAL-4',
            'COMPUTATIONAL-5', 'COMPUTATIONAL-6', 'COMPUTATIONAL-X',
            'CONDITION-VALUE', 'CONFIGURATION', 'CONSOLE', 'CONSTANT',
            'CONSTRAIN', 'CONSTRAINTS', 'CONTAINS', 'CONTENT',
            'CONTROL', 'CONTROL-AREA', 'CONTROLS', 'CONTROLS-UNCROPPED',
            'CONVERSION', 'CONVERT', 'CONVERTING', 'COPY-SELECTION',
            'CORE-INDEX', 'CORR', 'CORRESPONDING', 'COUNT',
            'CREATING', 'CRT', 'CRT-UNDER', 'CSIZE', 'CSP', 'CURRENCY',
            'CURSOR', 'CURSOR-COL', 'CURSOR-COLOR',
            'CURSOR-FRAME-WIDTH', 'CURSOR-ROW', 'CURSOR-X', 'CURSOR-Y',
            'CUSTOM-ATTRIBUTE', 'CUSTOM-PRINT-TEMPLATE', 'CYCLE', 'CYL-INDEX',
            'CYL-OVERFLOW', 'DASHED', 'DATA', 'DATA-COLUMNS',
            'DATA-POINTER', 'DATA-TYPES', 'DATABASE-KEY', 'DATABASE-KEY-LONG',
            'DATE', 'DATE-COMPILED', 'DATE-ENTRY', 'DATE-RECORD',
            'DATE-WRITTEN', 'DAY', 'DAY-OF-WEEK', 'DBCLOB', 'DBCLOB-FILE',
            'DBCLOB-LOCATOR', 'DBCS', 'DE', 'DEBUG', 'DEBUG-CONTENTS',
            'DEBUG-ITEM', 'DEBUG-LINE', 'DEBUG-NAME', 'DEBUG-SUB-1',
            'DEBUG-SUB-2', 'DEBUG-SUB-3', 'DEBUGGING', 'DECIMAL',
            'DECIMAL-POINT', 'DECLARATIVES', 'DEFAULT',
            'DEFAULT-BUTTON', 'DEFAULT-FONT', 'DEFINITION',
            'DELEGATE-ID', 'DELIMITED', 'DELIMITER', 'DEPENDING',
            'DESCENDING', 'DESTINATION', 'DESTROY', 'DETAIL', 'DICTIONARY',
            'DISABLE', 'DISC', 'DISJOINING', 'DISK', 'DISP',
            'DISPLAY-1', 'DISPLAY-COLUMNS', 'DISPLAY-FORMAT', 'DISPLAY-ST',
            'DIVIDER-COLOR', 'DIVIDERS', 'DIVISION', 'DOT-DASH',
            'DOTTED', 'DOWN', 'DRAG-COLOR', 'DRAW', 'DROP', 'DROP-DOWN',
            'DROP-LIST', 'DUPLICATES', 'DYNAMIC', 'EBCDIC', 'EC', 'ECHO', 'EGCS',
            'EGI', 'EJECT', 'ELEMENTARY', 'ELSE', 'EMI', 'EMPTY-CHECK',
            'ENABLE', 'ENABLED', 'END', 'END-ACCEPT', 'END-ADD', 'END-CALL',
            'END-CHAIN', 'END-COLOR', 'END-COMPUTE', 'END-DELEGATE',
            'END-DELETE', 'END-DISPLAY', 'END-DIVIDE', 'END-EVALUATE',
            'END-IF', 'END-INVOKE', 'END-MODIFY', 'END-MOVE', 'END-MULTIPLY',
            'END-OF-PAGE', 'END-PERFORM', 'END-READ', 'END-RECEIVE',
            'END-RETURN', 'END-REWRITE', 'END-SEARCH', 'END-START',
            'END-STRING', 'END-SUBTRACT', 'END-SYNC', 'END-TRY',
            'END-UNSTRING', 'END-WAIT', 'END-WRITE', 'END-XML', 'ENDING',
            'ENGRAVED', 'ENSURE-VISIBLE', 'ENTRY-CONVENTION',
            'ENTRY-FIELD',
            'ENTRY-REASON', 'ENUM', 'ENUM-ID', 'ENVIRONMENT',
            'ENVIRONMENT-NAME', 'ENVIRONMENT-VALUE', 'EOL', 'EOP',
            'EOS', 'EQUAL', 'EQUALS', 'ERASE', 'ERROR', 'ESCAPE',
            'ESCAPE-BUTTON', 'ESI', 'EVENT', 'EVENT-LIST',
            'EVENT-POINTER', 'EVERY', 'EXCEEDS', 'EXCEPTION',
            'EXCEPTION-OBJECT', 'EXCEPTION-VALUE', 'EXCESS-3',
            'EXCLUDE-EVENT-LIST', 'EXCLUSIVE',
            'EXPAND', 'EXPANDS', 'EXTEND', 'EXTENDED',
            'EXTENDED-SEARCH', 'EXTENSION', 'EXTERNAL', 'EXTERNAL-FORM',
            'EXTERNALLY-DESCRIBED-KEY', 'FACTORY', 'FALSE', 'FD',
            'FH--FCD', 'FH--KEYDEF', 'FILE', 'FILE-CONTROL', 'FILE-ID',
            'FILE-LIMIT', 'FILE-LIMITS', 'FILE-NAME', 'FILE-POS', 'FILL-COLOR',
            'FILL-COLOR2', 'FILL-PERCENT', 'FILLER', 'FINAL', 'FINALLY',
            'FINISH-REASON', 'FIRST', 'FIXED', 'FIXED-FONT', 'FIXED-WIDTH',
            'FLAT', 'FLAT-BUTTONS', 'FLOAT-BINARY-7', 'FLOAT-BINARY-16',
            'FLOAT-BINARY-34', 'FLOAT-DECIMAL-16', 'FLOAT-DECIMAL-34',
            'FLOAT-EXTENDED', 'FLOAT-LONG',
            'FLOAT-SHORT', 'FLOATING', 'FONT', 'FOOTING', 'FOR',
            'FOREGROUND-COLOR', 'FOREGROUND-COLOUR', 'FOREVER', 'FORMAT',
            'FRAME', 'FRAMED', 'FROM', 'FULL', 'FULL-HEIGHT',
            'FUNCTION', 'FUNCTION-ID', 'FUNCTION-POINTER', 'GENERATE',
            'GET', 'GETTER', 'GIVING', 'GLOBAL', 'GO-BACK', 'GO-FORWARD',
            'GO-HOME', 'GO-SEARCH', 'GRAPHICAL', 'GREATER', 'GRID',
            'GRIP', 'GROUP', 'GROUP-USAGE', 'GROUP-VALUE', 'HANDLE',
            'HAS-CHILDREN', 'HEADING', 'HEADING-COLOR', 'HEADING-DIVIDER-COLOR',
            'HEADING-FONT', 'HEAVY', 'HEIGHT', 'HEIGHT-IN-CELLS', 'HELP-ID',
            'HIDDEN-DATA', 'HIGH', 'HIGH-COLOR', 'HIGH-VALUE', 'HIGH-VALUES',
            'HIGHLIGHT', 'HORIZONTAL', 'HOT-TRACK', 'HSCROLL', 'HSCROLL-POS',
            'I-O', 'I-O-CONTROL', 'ICON', 'ID', 'IDENTIFICATION',
            'IDENTIFIED', 'IFINITY', 'IGNORE', 'IGNORING', 'IMPLEMENTS', 'IN',
            'INDEPENDENT', 'INDEX', 'INDEXED', 'INDEXER', 'INDEXER-ID', 'INDIC',
            'INDICATE', 'INDICATOR', 'INDICATORS', 'INDIRECT',
            'INHERITING', 'INHERITS',
            'INITIAL', 'INITIALIZED', 'INPUT',
            'INPUT-OUTPUT', 'INQUIRE', 'INSERT', 'INSERT-ROWS',
            'INSERTION-INDEX', 'INSTALLATION', 'INSTANCE',
            'INTERFACE', 'INTERFACE-ID', 'INTERMEDIATE',
            'INTERNAL', 'INTO', 'INTRINSIC',
            'INVALID', 'INVOKED', 'IS', 'ITEM', 'ITEM-BOLD',
            'ITEM-ID', 'ITEM-TEXT', 'ITEM-TO-ADD', 'ITEM-TO-DELETE',
            'ITEM-TO-EMPTY', 'ITEM-VALUE', 'ITERATOR', 'ITERATOR-ID', 'J',
            'JOINED', 'JOINING', 'JUST', 'JUSTIFIED', 'KANJI',
            'KEPT', 'KEY', 'KEY-YY', 'KEYBOARD', 'LABEL', 'LABEL-OFFSET',
            'LARGE-FONT', 'LAST', 'LAST-ROW', 'LAYOUT-DATA', 'LAYOUT-MANAGER',
            'LC_ALL', 'LC_COLLATE', 'LC_CTYPE', 'LC_CURRENCY', 'LC_MESSAGES',
            'LC_MONETARY', 'LC_NUMERIC', 'LC_TIME', 'LEADING', 'LEADING-SHIFT',
            'LEAVE', 'LEFT', 'LEFT-JUSTIFY', 'LEFT-TEXT', 'LEFTLINE',
            'LENGTH-CHECK', 'LESS', 'LIMIT', 'LIMITS', 'LIN', 'LINAGE',
            'LINAGE-COUNTER', 'LINE', 'LINE-COUNTER', 'LINES', 'LINES-AT-ROOT',
            'LINK', 'LINKAGE', 'LIST', 'LIST-BOX', 'LM-RESIZE', 'LOCAL-STORAGE',
            'LOCALE', 'LOCK', 'LOCKING', 'LONG-DATE', 'LONG-VARBINARY',
            'LONG-VARCHAR', 'LOW', 'LOW-COLOR', 'LOW-VALUE', 'LOW-VALUES',
            'LOWER', 'LOWERED', 'LOWLIGHT', 'MANUAL', 'MASS-UPDATE',
            'MASTER-INDEX', 'MAX-HEIGHT', 'MAX-LINES', 'MAX-PROGRESS',
            'MAX-SIZE', 'MAX-TEXT', 'MAX-VAL', 'MAX-WIDTH', 'MDI-CHILD',
            'MDI-FRAME', 'MEDIUM-FONT', 'MEMORY', 'MENU', 'MESSAGE',
            'MESSAGES', 'METACLASS', 'METHOD', 'METHOD-ID', 'MIN-HEIGHT',
            'MIN-LINES', 'MIN-SIZE', 'MIN-VAL', 'MIN-WIDTH', 'MODAL', 'MODE',
            'MODELESS', 'MODIFIED', 'MODULES', 'MONITOR-POINTER',
            'MORE-LABELS', 'MULTILINE',
            'MUTEX-POINTER', 'NAME', 'NAMED', 'NATIONAL',
            'NATIONAL-EDITED', 'NATIVE', 'NAVIGATE-URL', 'NCHAR',
            'NEAREST-AWAY-FROM-ZERO', 'NEAREST-EVEN', 'NEAREST-TOWARD-ZERO',
            'NEGATIVE', 'NEGATIVE-INFINITY',
            'NESTED', 'NET-EVENT-LIST', 'NEW', 'NEWABLE', 'NEXT ', 'NEXT-ITEM',
            'NO', 'NO-AUTO-DEFAULT', 'NO-AUTOSEL', 'NO-BOX', 'NO-CELL-DRAG',
            'NO-CLOSE', 'NO-DIVIDERS', 'NO-ECHO', 'NO-F4', 'NO-FOCUS',
            'NO-GROUP-TAB', 'NO-KEY-LETTER', 'NO-SEARCH', 'NO-TAB', 'NO-UPDOWN',
            'NOMINAL', 'NONE', 'NORMAL', 'NOT', 'NOT-A-NUMBER', 'NOTIFY',
            'NOTIFY-CHANGE', 'NOTIFY-DBLCLICK', 'NOTIFY-SELCHANGE',
            'NSTD-REELS', 'NULL', 'NULLS', 'NUM-COL-HEADINGS',
            'NUM-ROW-HEADINGS', 'NUM-ROWS', 'NUMBER', 'NUMBERS', 'NUMERIC',
            'NUMERIC-EDITED', 'NUMERIC-FILL', 'O-FILL', 'OBJECT',
            'OBJECT-COMPUTER', 'OBJECT-ID', 'OBJECT-REFERENCE',
            'OBJECT-STORAGE', 'OCCURS', 'OF', 'OFF', 'OK-BUTTON', 'OMITTED',
            'ONLY', 'OOSTACKPTR', 'OPERATOR', 'OPERATOR-ID',
            'OPTIONAL', 'OPTIONS', 'OR', 'ORDER', 'ORGANIZATION', 'OTHER',
            'OTHERWISE', 'OUTPUT', 'OVERFLOW', 'OVERLAP-LEFT', 'OVERLAP-TOP',
            'OVERLAPPED', 'OVERLINE', 'OVERRIDE', 'PACKED-DECIMAL',
            'PADDING', 'PAGE', 'PAGE-COUNTER', 'PAGE-SETUP', 'PAGE-SIZE',
            'PAGED', 'PANEL-INDEX', 'PANEL-STYLE', 'PANEL-TEXT', 'PANEL-WIDTHS',
            'PARAGRAPH', 'PARAMS', 'PARENT', 'PARSE', 'PARTIAL', 'PASSWORD',
            'PERMANENT', 'PF', 'PH', 'PIC', 'PICTURE', 'PIXEL',
            'PIXELS', 'PLACEMENT', 'PLUS', 'POINTER', 'POP-UP', 'POSITION',
            'POSITION-SHIFT', 'POSITIONING', 'POSITIVE', 'POSITIVE-INFINITY',
            'PREFIXED', 'PREFIXING', 'PRESENT',
            'PREVIOUS', 'PRINT', 'PRINT-CONTROL', 'PRINT-NO-PROMPT',
            'PRINT-PREVIEW', 'PRINT-SWITCH', 'PRINTER', 'PRINTER-1', 'PRINTING',
            'PRIOR', 'PRIORITY', 'PRIVATE', 'PROCEDURE', 'PROCEDURE-POINTER',
            'PROCEDURES', 'PROCEED', 'PROCESS', 'PROCESSING', 'PROGRAM',
            'PROGRAM-ID', 'PROGRAM-POINTER', 'PROGRESS', 'PROHIBITED',
            'PROMPT', 'PROPERTIES',
            'PROPERTY', 'PROPERTY-ID', 'PROPERTY-VALUE', 'PROTECTED',
            'PROTOTYPE', 'PUBLIC', 'PURGE', 'PUSH-BUTTON', 'QUERY-INDEX',
            'QUEUE', 'QUOTE', 'QUOTES', 'RADIO-BUTTON', 'RAISED',
            'RAISING', 'RD', 'READ-ONLY', 'READING',
            'READY', 'RECORD', 'RECORD-DATA', 'RECORD-OVERFLOW',
            'RECORD-TO-ADD', 'RECORD-TO-DELETE', 'RECORDING', 'RECORDS',
            'RECURSIVE', 'REDEFINE', 'REDEFINES', 'REDEFINITION', 'REEL',
            'REFERENCE', 'REFERENCES', 'REFRESH', 'REGION-COLOR', 'RELATION',
            'RELATIVE', 'RELOAD', 'REMAINDER', 'REMARKS', 'REMOVAL',
            'RENAMES', 'REORG-CRITERIA', 'REPEATED', 'REPLACE', 'REPLACING',
            'REPORT', 'REPORTING', 'REPORTS', 'REPOSITORY', 'REQUIRED',
            'REPRESENTS-NOT-A-NUMBER',
            'REREAD', 'RERUN', 'RESERVE', 'RESET-GRID', 'RESET-LIST',
            'RESET-TABS', 'RESIZABLE', 'RESTRICTED', 'RESULT-SET-LOCATOR',
            'RETRY', 'RETURN-CODE', 'RETURNING',
            'REVERSE-VIDEO', 'REVERSED', 'REWIND', 'RF', 'RH',
            'RIGHT', 'RIGHT-ALIGN', 'RIGHT-JUSTIFY', 'RIMMED',
            'ROLLING', 'ROUNDED', 'ROUNDING', 'ROW-COLOR', 'ROW-COLOR-PATTERN',
            'ROW-DIVIDERS', 'ROW-FONT', 'ROW-HEADINGS', 'ROW-PROTECTION',
            'ROWID', 'RUN', 'S01', 'S02', 'S03', 'S04', 'S05', 'SAME',
            'SAVE-AS', 'SAVE-AS-NO-PROMPT', 'SCREEN', 'SCROLL', 'SCROLL-BAR',
            'SD', 'SEARCH-OPTIONS', 'SEARCH-TEXT', 'SECONDS',
            'SECTION', 'SECURE', 'SECURITY', 'SEEK', 'SEGMENT', 'SEGMENT-LIMIT',
            'SELECT-ALL', 'SELECTION-INDEX', 'SELECTION-TEXT',
            'SELECTIVE', 'SELF', 'SELF-ACT', 'SELFCLASS', 'SEMAPHORE-POINTER',
            'SEND', 'SENTENCE', 'SEPARATE', 'SEPARATION', 'SEQUENCE',
            'SEQUENTIAL', 'SETTER', 'SHADING', 'SHADOW',
            'SHARING', 'SHIFT-IN', 'SHIFT-OUT', 'SHORT-DATE', 'SHOW-LINES',
            'SHOW-NONE', 'SHOW-SEL-ALWAYS', 'SIGNED', 'SIGNED-INT',
            'SIGNED-LONG', 'SIGNED-SHORT', 'SIZE', 'SKIP1',
            'SKIP2', 'SKIP3', 'SMALL-FONT', 'SORT-CONTROL',
            'SORT-CORE-SIZE', 'SORT-FILE-SIZE', 'SORT-MERGE', 'SORT-MESSAGE',
            'SORT-MODE-SIZE', 'SORT-OPTION', 'SORT-ORDER', 'SORT-RETURN',
            'SORT-TAPE', 'SORT-TAPES', 'SOURCE', 'SOURCE-COMPUTER', 'SOURCES',
            'SPACE', 'SPACE-FILL', 'SPACES', 'SPECIAL-NAMES', 'SPINNER', 'SQL',
            'SQUARE', 'STANDARD', 'STANDARD-1', 'STANDARD-2', 'STANDARD-3',
            'STANDARD-BINARY', 'STANDARD-DECIMAL',
            'START-X', 'START-Y', 'STARTING', 'STATEMENT', 'STATIC',
            'STATIC-LIST',
            'STATUS', 'STATUS-BAR', 'STATUS-TEXT', 'STEP',
            'STOP-BROWSER', 'STRONG', 'STYLE', 'SUB-QUEUE-1',
            'SUB-QUEUE-2', 'SUB-QUEUE-3', 'SUBFILE', 'SUBWINDOW',
            'SUFFIXING', 'SUPER', 'SYMBOL', 'SYMBOLIC',
            'SYNCHRONIZED', 'SYSIN', 'SYSIPT', 'SYSLST', 'SYSOUT',
            'SYSPCH', 'SYSPUNCH', 'SYSTEM', 'SYSTEM-DEFAULT', 'SYSTEM-INFO',
            'TAB', 'TAB-CONTROL', 'TAB-TO-ADD', 'TAB-TO-DELETE', 'TABLE',
            'TALLY', 'TALLYING', 'TAPE', 'TAPES', 'TEMPORARY', 'TERMINAL',
            'TERMINAL-INFO', 'TERMINATION-VALUE', 'TEST', 'TEXT',
            'THAN', 'THEN', 'THREAD', 'THREAD-LOCAL', 'THREAD-LOCAL-STORAGE',
            'THREAD-POINTER', 'THROUGH', 'THRU', 'THUMB-POSITION',
            'TILED-HEADINGS', 'TIME', 'TIME-OF-DAY', 'TIME-OUT', 'TIME-RECORD',
            'TIMEOUT', 'TIMES', 'TIMESTAMP', 'TIMESTAMP-OFFSET',
            'TIMESTAMP-OFFSET-RECORD', 'TIMESTAMP-RECORD', 'TITLE', 'TITLE-BAR',
            'TITLE-POSITION', 'TO', 'TOOL-BAR', 'TOP', 'TOTALED', 'TOTALING',
            'TOWARD-GREATER', 'TOWARD-LESSER',
            'TRACE', 'TRACK-AREA', 'TRACK-LIMIT', 'TRACK-THUMB', 'TRACKS',
            'TRADITIONAL-FONT', 'TRAILING', 'TRAILING-SHIFT', 'TRAILING-SIGN',
            'TRANSACTION', 'TRANSPARENT', 'TRANSPARENT-COLOR',
            'TREE-VIEW', 'TRUE', 'TRUNCATION', 'TYPE', 'TYPEDEF', 'UCS-4',
            'UNDERLINE', 'UNDERLINED', 'UNEQUAL', 'UNFRAMED', 'UNIT', 'UNITS',
            'UNIVERSAL', 'UNSIGNED', 'UNSIGNED-INT', 'UNSIGNED-LONG',
            'UNSIGNED-SHORT',
            'UNSORTED', 'UP', 'UPDATE', 'UNTIL', 'UPON', 'UPPER',
            'UPSI-0', 'UPSI-1', 'UPSI-2', 'UPSI-3', 'UPSI-4', 'UPSI-5',
            'UPSI-6', 'UPSI-7', 'USAGE', 'USE-ALT', 'USE-RETURN',
            'USE-TAB', 'USER', 'USER-COLORS', 'USER-DEFAULT', 'USER-GRAY',
            'USER-WHITE', 'USING', 'UTF-16', 'UTF-8', 'VALID',
            'VAL-STATUS', 'VALIDATE-STATUS',
            'VALUE', 'VALUE-FORMAT', 'VALUES', 'VALUETYPE', 'VALUETYPE-ID',
            'VARBINARY', 'VARIABLE', 'VARIANT', 'VARYING', 'VERTICAL',
            'VERY-HEAVY', 'VIRTUAL-WIDTH', 'VISIBLE', 'VPADDING', 'VSCROLL',
            'VSCROLL-BAR', 'VSCROLL-POS', 'VTOP', 'WEB-BROWSER', 'WHEN',
            'WHERE', 'WIDTH', 'WIDTH-IN-CELLS', 'WINDOW',
            'WITH', 'WORDS', 'WORKING-STORAGE', 'WRAP', 'WRITE-ONLY',
            'WRITE-VERIFY', 'WRITING', ' XML', 'XML ', 'XML-CODE', 'XML-EVENT',
            'XML-NTEXT', 'XML-TEXT', 'YIELDING', 'YYYYDDD', 'YYYYMMDD', 'ZERO',
            'ZERO-FILL', 'ZEROES', 'ZEROS'
            ),
        3 => array( // Statement Keywords containing no spaces.
            'ACCEPT', 'ADD', 'ALTER', 'ALLOCATE', 'ATTACH', 'CALL', 'CANCEL',
            'CHAIN', 'CREATE',
            'CLOSE', 'COLOR', 'COMPUTE', 'COMMIT', 'CONTINUE',
            'COPY', 'DECLARE', 'DELEGATE', 'DELETE', 'DETACH', 'DISPLAY',
            'DIVIDE',
            'ENTER', 'ENTRY', 'EVALUATE', 'EXAMINE',
            'EXEC', 'EXECUTE', 'EXHIBIT', 'EXIT', 'FREE', 'GOBACK',
            'IF',  'INITIALIZE', 'INITIATE', 'INSPECT', 'INVOKE', 'MERGE',
            'MODIFY', 'MOVE', 'MULTIPLY', 'NOTE', 'ON', 'OPEN',
            'PERFORM', 'RAISE', 'READ', 'RECEIVE', 'RELEASE', 'RETURN',
            'RESET', 'RESUME',
            'REWRITE', 'ROLLBACK', 'SEARCH', 'SELECT', 'SERVICE', 'SET', 'SORT',
            'START', 'STOP', 'STRING', 'SUBTRACT', 'SYNC',
            'SUPPRESS', 'TERMINATE',
            'TRANSFORM', 'TRY', 'UNLOCKFILE', 'UNLOCK', 'UNSTRING', 'USE',
            'VALIDATE', 'WAIT', 'WRITE'
            ),
        4 => array( // Intrinsic functions
            'ABS', 'ACOS', 'ANNUITY', 'ASIN', 'ATAN', 'BOOLEAN-OF-INTEGER',
            'BYTE-LENGTH', 'CHAR', 'CHAR-NATIONAL',
            'COS', 'COMBINED-DATETIME', 'CONCATENATE', 'CURRENT-DATE',
            'DATE-OF-INTEGER', 'DATE-TO-YYYYMMDD', 'DAY-TO-YYYYDDD',
            'DAY-OF-INTEGER', 'DISPLAY-OF', 'E', 'EXCEPTION-FILE',
            'EXCEPTION-FILE-N', 'EXCEPTION-LOCATION',
            'EXCEPTION-LOCATION-N', 'EXCEPTION-STATEMENT', 'EXCEPTION-STATUS',
            'EXP', 'EXP10', 'FACTORIAL', 'FORMATTED-CURRENT-DATE',
            'FORMATTED-DATE', 'FORMATTED-DATETIME', 'FORMATTED-TIME',
            'FRACTION-PART', 'HIGHEST-ALGEBRAIC', 'INTEGER',
            'INTEGER-OF-BOOLEAN', 'INTEGER-OF-DATE', 'INTEGER-OF-DAY',
            'INTEGER-OF-FORMATTED-DATE', 'INTEGER-PART', 'LENGTH',
            'LOCALE-COMPARE',
            'LOCALE-DATE', 'LOCALE-TIME', 'LOCALE-TIME-FROM-SECONDS',
            'LOCALE-TIME-FROM-SECS', 'LOG',
            'LOG10', 'LOWER-CASE', 'LOWEST-ALGEBRAIC',
            'MAX', 'MEAN', 'MEDIAN', 'MIDRANGE',
            'MIN', 'MOD', 'NATIONAL-OF', 'NUMVAL', 'NUMVAL-C', 'NUMVAL-F',
            'ORD', 'ORD-MAX', 'ORD-MIN',
            'PI', 'PRESENT-VALUE', 'RANDOM', 'RANGE', 'REM', 'REVERSE',
            'SECONDS-FROM-FORMATTED-TIME', 'SIGN', 'SIN', 'SQRT',
            'SECONDS-PAST-MIDNIGHT', 'STANDARD-DEVIATION', 'STANDARD-COMPARE',
            'STORED-CHAR-LENGTH',
            'SUBSTITUTE', 'SUBSTITUE-CASE', 'SUM', 'TAN', 'TEST-DATE-YYYYMMDD',
            'TEST-DAY-YYYYDDD', 'TEST-FORMATTED-TIME', 'TEST-NUMVAL',
            'TEST-NUMVAL-C', 'TEST-NUMVAL-F',
            'TRIM', 'UPPER-CASE', 'VARIANCE', 'YEAR-TO-YYYY', 'WHEN-COMPILED'
            ),
        ),
    'SYMBOLS' => array(
        //  Arithmetic and comparison operators must be surrounded by spaces.
        ' + ', ' - ', ' * ', ' / ', ' ** ', ' ^ ',
        '.', ',',
        ' = ', ' < ', ' > ', ' >= ', ' <= ', ' <> ',
        '(', ')', '[', ']'
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000000; font-weight: bold;',
            2 => 'color: #008000; font-weight: bold;',
            3 => 'color: #000000; font-weight: bold;',
            4 => 'color: #9d7700;',
            ),
        'COMMENTS' => array(
            1 => 'color: #a0a0a0; font-style: italic;',
            2 => 'color: #000080; font-weight: bold;',
            ),
        'ESCAPE_CHAR' => array(
            ),
        'BRACKETS' => array(
            0 => 'color: #339933;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #993399;'
            ),
        'METHODS' => array(
            1 => 'color: #800080;'
            ),
        'SYMBOLS' => array(
            0 => 'color: #000066;'
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
    'TAB_WIDTH' => 4,
    'PARSER_CONTROL' => array(
        'KEYWORDS' => array(
            'DISALLOWED_BEFORE' => '(?<![a-zA-Z0-9-\$_\|\#|^&])',
        ),
    ),
);
