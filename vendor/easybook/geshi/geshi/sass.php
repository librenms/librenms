<?php
/*************************************************************************************
 * sass.php
 * -------
 * Author: Javier Eguiluz (javier.eguiluz@gmail.com)
 * Release Version: 1.0.8.12
 * Date Started: 2014/05/10
 *
 * SASS language file for GeSHi.
 *
 * CHANGES
 * -------
 * 2014/05/10 (1.0.0)
 *   -  First Release
 *
 *************************************************************************************
 *
 *   This file is part of GeSHi.
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
    'LANG_NAME' => 'Sass',
    'COMMENT_SINGLE' => array(1 => '//'),
    'COMMENT_MULTI' => array('/*' => '*/'),
    'COMMENT_REGEXP' => array(),
    'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
    'QUOTEMARKS' => array('"', "'"),
    'ESCAPE_CHAR' => '',
    'ESCAPE_REGEXP' => array(
        ),
    'NUMBERS' =>
        GESHI_NUMBER_INT_BASIC | GESHI_NUMBER_FLT_SCI_ZERO,
    'KEYWORDS' => array(
        // properties
        1 => array(
            'azimuth', 'background-attachment', 'background-color',
            'background-image', 'background-position', 'background-repeat',
            'background', 'border-bottom-color', 'border-radius',
            'border-top-left-radius', 'border-top-right-radius',
            'border-bottom-right-radius', 'border-bottom-left-radius',
            'border-bottom-style', 'border-bottom-width', 'border-left-color',
            'border-left-style', 'border-left-width', 'border-right',
            'border-right-color', 'border-right-style', 'border-right-width',
            'border-top-color', 'border-top-style',
            'border-top-width','border-bottom', 'border-collapse',
            'border-left', 'border-width', 'border-color', 'border-spacing',
            'border-style', 'border-top', 'border', 'box-shadow', 'caption-side', 'clear',
            'clip', 'color', 'content', 'counter-increment', 'counter-reset',
            'cue-after', 'cue-before', 'cue', 'cursor', 'direction', 'display',
            'elevation', 'empty-cells', 'float', 'font-family', 'font-size',
            'font-size-adjust', 'font-stretch', 'font-style', 'font-variant',
            'font-weight', 'font', 'line-height', 'letter-spacing',
            'list-style', 'list-style-image', 'list-style-position',
            'list-style-type', 'margin-bottom', 'margin-left', 'margin-right',
            'margin-top', 'margin', 'marker-offset', 'marks', 'max-height',
            'max-width', 'min-height', 'min-width', 'orphans', 'outline',
            'outline-color', 'outline-style', 'outline-width', 'overflow',
            'padding-bottom', 'padding-left', 'padding-right', 'padding-top',
            'padding', 'page', 'page-break-after', 'page-break-before',
            'page-break-inside', 'pause-after', 'pause-before', 'pause',
            'pitch', 'pitch-range', 'play-during', 'position', 'quotes',
            'richness', 'right', 'size', 'speak-header', 'speak-numeral',
            'speak-punctuation', 'speak', 'speech-rate', 'stress',
            'table-layout', 'text-align', 'text-decoration', 'text-indent',
            'text-shadow', 'text-transform', 'top', 'unicode-bidi',
            'vertical-align', 'visibility', 'voice-family', 'volume',
            'white-space', 'widows', 'width', 'word-spacing', 'z-index',
            'bottom', 'left', 'height',
            // media queries
            'screen', 'orientation', 'min-device-width', 'max-device-width',
            ),
        // reserved words for values
        2 => array(
            // colors
            'aqua', 'black', 'blue', 'fuchsia', 'gray', 'green', 'lime',
            'maroon', 'navy', 'olive', 'orange', 'purple', 'red', 'silver',
            'teal', 'white', 'yellow',
            // media queries
            'landscape', 'portrait', 
            // other
            'above', 'absolute', 'always', 'armenian', 'aural', 'auto',
            'avoid', 'baseline', 'behind', 'below', 'bidi-override', 'blink',
            'block', 'bold', 'bolder', 'both', 'capitalize', 'center-left',
            'center-right', 'center', 'circle', 'cjk-ideographic',
            'close-quote', 'collapse', 'condensed', 'continuous', 'crop',
            'crosshair', 'cross', 'cursive', 'dashed', 'decimal-leading-zero',
            'decimal', 'default', 'digits', 'disc', 'dotted', 'double',
            'e-resize', 'embed', 'extra-condensed', 'extra-expanded',
            'expanded', 'fantasy', 'far-left', 'far-right', 'faster', 'fast',
            'fixed',  'georgian', 'groove', 'hebrew', 'help', 'hidden',
            'hide', 'higher', 'high', 'hiragana-iroha', 'hiragana', 'icon',
            'inherit', 'inline-table', 'inline', 'inline-block', 'inset', 'inside',
            'invert', 'italic', 'justify', 'katakana-iroha', 'katakana', 'landscape',
            'larger', 'large', 'left-side', 'leftwards', 'level', 'lighter', 
            'line-through', 'list-item', 'loud', 'lower-alpha', 'lower-greek',
            'lower-roman', 'lowercase', 'ltr', 'lower', 'low', 
            'medium', 'message-box', 'middle', 'mix', 'monospace', 'n-resize',
            'narrower', 'ne-resize', 'no-close-quote',
            'no-open-quote', 'no-repeat', 'none', 'normal', 'nowrap',
            'nw-resize', 'oblique', 'once', 'open-quote', 'outset',
            'outside', 'overline', 'pointer', 'portrait', 'px',
             'relative', 'repeat-x', 'repeat-y', 'repeat', 'rgb',
            'ridge', 'right-side', 'rightwards', 's-resize', 'sans-serif',
            'scroll', 'se-resize', 'semi-condensed', 'semi-expanded',
            'separate', 'serif', 'show', 'silent',  'slow', 'slower',
            'small-caps', 'small-caption', 'smaller', 'soft', 'solid',
            'spell-out', 'square', 'static', 'status-bar', 'super',
            'sw-resize', 'table-caption', 'table-cell', 'table-column',
            'table-column-group', 'table-footer-group', 'table-header-group',
            'table-row', 'table-row-group',  'text', 'text-bottom',
            'text-top', 'thick', 'thin', 'transparent', 'ultra-condensed',
            'ultra-expanded', 'underline', 'upper-alpha', 'upper-latin',
            'upper-roman', 'uppercase', 'url', 'visible', 'w-resize', 'wait',
             'wider', 'x-fast', 'x-high', 'x-large', 'x-loud', 'x-low',
             'x-small', 'x-soft', 'xx-large', 'xx-small', 'yellow', 'yes'
            ),
        // directives
        3 => array(
            '@at-root', '@charset', '@content', '@debug', '@each', '@else', '@elseif',
            '@else if', '@extend', '@font-face', '@for', '@function', '@if',
            '@import', '@include', '@media', '@mixin', '@namespace', '@page',
            '@return', '@warn', '@while', 
            ),
        // built-in Sass functions
        4 => array(
            'rgb', 'rgba', 'red', 'green', 'blue', 'mix',
            'hsl', 'hsla', 'hue', 'saturation', 'lightness', 'adjust-hue',
            'lighten', 'darken', 'saturate', 'desaturate', 'grayscale',
            'complement', 'invert',
            'alpha', 'rgba', 'opacify', 'transparentize',
            'adjust-color', 'scale-color', 'change-color', 'ie-hex-str',
            'unquote', 'quote', 'str-length', 'str-insert', 'str-index',
            'str-slice', 'to-upper-case', 'to-lower-case',
            'percentage', 'round', 'ceil', 'floor', 'abs', 'min', 'max', 'random',
            'length', 'nth', 'join', 'append', 'zip', 'index', 'list-separator',
            'map-get', 'map-merge', 'map-remove', 'map-keys', 'map-values',
            'map-has-key', 'keywords',
            'feature-exists', 'variable-exists', 'global-variable-exists',
            'function-exists', 'mixin-exists', 'inspect', 'type-of', 'unit',
            'unitless', 'comparable', 'call',
            'if', 'unique-id',
            ),
        // reserved words
        5 => array(
            '!important', '!default', '!optional', 'true', 'false', 'with',
            'without', 'null', 'from', 'through', 'to', 'in', 'and', 'or',
            'only', 'not',
            ),
        ),
    'SYMBOLS' => array(
        '(', ')', '{', '}', ':', ';',
        '>', '+', '*', ',', '^', '=',
        '&', '~', '!', '%', '?', '...',
        ),
    'CASE_SENSITIVE' => array(
        GESHI_COMMENTS => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
        5 => false,
        ),
    'STYLES' => array(
        'KEYWORDS' => array(
            1 => 'color: #000000; font-weight: bold;',
            2 => 'color: #993333;',
            3 => 'color: #990000;',
            4 => 'color: #000000; font-weight: bold;',
            5 => 'color: #009900;',
            ),
        'COMMENTS' => array(
            1 => 'color: #006600; font-style: italic;',
            'MULTI' => 'color: #006600; font-style: italic;'
            ),
        'ESCAPE_CHAR' => array(
            ),
        'BRACKETS' => array(
            0 => 'color: #00AA00;'
            ),
        'STRINGS' => array(
            0 => 'color: #ff0000;'
            ),
        'NUMBERS' => array(
            0 => 'color: #cc66cc;'
            ),
        'METHODS' => array(
            ),
        'SYMBOLS' => array(
            0 => 'color: #00AA00;'
            ),
        'SCRIPT' => array(
            ),
        'REGEXPS' => array(
            0 => 'color: #cc00cc;',
            1 => 'color: #6666ff;',
            2 => 'color: #3333ff;',
            3 => 'color: #933;',
            4 => 'color: #ff6633;',
            5 => 'color: #0066ff;',
            )
        ),
    'URLS' => array(
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        ),
    'OOLANG' => false,
    'OBJECT_SPLITTERS' => array(
        ),
    'REGEXPS' => array(
        // Variables
        0 => "[$][a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*",
        // Hexadecimal colors
        1 => "\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})",
        // CSS Pseudo classes
        // note: & is needed for &gt; (i.e. > )
        2 => "(?<!\\\\):(?!\d)[a-zA-Z0-9\-]+\b(?:\s*(?=[\{\.#a-zA-Z,:+*&](.|\n)|<\|))",
        // Measurements
        3 => "[+\-]?(\d+|(\d*\.\d+))(em|ex|pt|px|cm|in|%)",
        // Interpolation
        4 => "(\#\{.*\})",
        // Browser prefixed properties
        5 => "(\-(moz|ms|o|webkit)\-[a-z\-]*)",
        ),
    'STRICT_MODE_APPLIES' => GESHI_NEVER,
    'SCRIPT_DELIMITERS' => array(
        ),
    'HIGHLIGHT_STRICT_BLOCK' => array(
        ),
    'TAB_WIDTH' => 2,
);
