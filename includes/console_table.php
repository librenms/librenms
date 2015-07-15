<?php
/**
 * Utility for printing tables from commandline scripts.
 *
 * PHP versions 4 and 5
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * o Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * o Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * o The names of the authors may not be used to endorse or promote products
 *   derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Console
 * @package   Console_Table
 * @author    Richard Heyes <richard@phpguru.org>
 * @author    Jan Schneider <jan@horde.org>
 * @copyright 2002-2005 Richard Heyes
 * @copyright 2006-2008 Jan Schneider
 * @license   http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Console_Table
 */

define('CONSOLE_TABLE_HORIZONTAL_RULE', 1);
define('CONSOLE_TABLE_ALIGN_LEFT', -1);
define('CONSOLE_TABLE_ALIGN_CENTER', 0);
define('CONSOLE_TABLE_ALIGN_RIGHT', 1);
define('CONSOLE_TABLE_BORDER_ASCII', -1);

/**
 * The main class.
 *
 * @category Console
 * @package  Console_Table
 * @author   Jan Schneider <jan@horde.org>
 * @license  http://www.debian.org/misc/bsd.license  BSD License (3 Clause)
 * @link     http://pear.php.net/package/Console_Table
 */
class Console_Table
{
    /**
     * The table headers.
     *
     * @var array
     */
    var $_headers = array();

    /**
     * The data of the table.
     *
     * @var array
     */
    var $_data = array();

    /**
     * The maximum number of columns in a row.
     *
     * @var integer
     */
    var $_max_cols = 0;

    /**
     * The maximum number of rows in the table.
     *
     * @var integer
     */
    var $_max_rows = 0;

    /**
     * Lengths of the columns, calculated when rows are added to the table.
     *
     * @var array
     */
    var $_cell_lengths = array();

    /**
     * Heights of the rows.
     *
     * @var array
     */
    var $_row_heights = array();

    /**
     * How many spaces to use to pad the table.
     *
     * @var integer
     */
    var $_padding = 1;

    /**
     * Column filters.
     *
     * @var array
     */
    var $_filters = array();

    /**
     * Columns to calculate totals for.
     *
     * @var array
     */
    var $_calculateTotals;

    /**
     * Alignment of the columns.
     *
     * @var array
     */
    var $_col_align = array();

    /**
     * Default alignment of columns.
     *
     * @var integer
     */
    var $_defaultAlign;

    /**
     * Character set of the data.
     *
     * @var string
     */
    var $_charset = 'utf-8';

    /**
     * Border character.
     *
     * @var string
     */
    var $_border = CONSOLE_TABLE_BORDER_ASCII;

    /**
     * Whether the data has ANSI colors.
     *
     * @var boolean
     */
    var $_ansiColor = false;

    /**
     * Constructor.
     *
     * @param integer $align   Default alignment. One of
     *                         CONSOLE_TABLE_ALIGN_LEFT,
     *                         CONSOLE_TABLE_ALIGN_CENTER or
     *                         CONSOLE_TABLE_ALIGN_RIGHT.
     * @param string  $border  The character used for table borders or
     *                         CONSOLE_TABLE_BORDER_ASCII.
     * @param integer $padding How many spaces to use to pad the table.
     * @param string  $charset A charset supported by the mbstring PHP
     *                         extension.
     * @param boolean $color   Whether the data contains ansi color codes.
     */
    function Console_Table($align = CONSOLE_TABLE_ALIGN_LEFT,
                           $border = CONSOLE_TABLE_BORDER_ASCII, $padding = 1,
                           $charset = null, $color = false)
    {
        $this->_defaultAlign = $align;
        $this->_border       = $border;
        $this->_padding      = $padding;
        $this->_ansiColor    = $color;
        if ($this->_ansiColor) {
            include_once 'Console/Color.php';
        }
        if (!empty($charset)) {
            $this->setCharset($charset);
        }
    }

    /**
     * Converts an array to a table.
     *
     * @param array   $headers      Headers for the table.
     * @param array   $data         A two dimensional array with the table
     *                              data.
     * @param boolean $returnObject Whether to return the Console_Table object
     *                              instead of the rendered table.
     *
     * @static
     *
     * @return Console_Table|string  A Console_Table object or the generated
     *                               table.
     */
    function fromArray($headers, $data, $returnObject = false)
    {
        if (!is_array($headers) || !is_array($data)) {
            return false;
        }

        $table = new Console_Table();
        $table->setHeaders($headers);

        foreach ($data as $row) {
            $table->addRow($row);
        }

        return $returnObject ? $table : $table->getTable();
    }

    /**
     * Adds a filter to a column.
     *
     * Filters are standard PHP callbacks which are run on the data before
     * table generation is performed. Filters are applied in the order they
     * are added. The callback function must accept a single argument, which
     * is a single table cell.
     *
     * @param integer $col       Column to apply filter to.
     * @param mixed   &$callback PHP callback to apply.
     *
     * @return void
     */
    function addFilter($col, &$callback)
    {
        $this->_filters[] = array($col, &$callback);
    }

    /**
     * Sets the charset of the provided table data.
     *
     * @param string $charset A charset supported by the mbstring PHP
     *                        extension.
     *
     * @return void
     */
    function setCharset($charset)
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'en_US');
        $this->_charset = strtolower($charset);
        setlocale(LC_CTYPE, $locale);
    }

    /**
     * Sets the alignment for the columns.
     *
     * @param integer $col_id The column number.
     * @param integer $align  Alignment to set for this column. One of
     *                        CONSOLE_TABLE_ALIGN_LEFT
     *                        CONSOLE_TABLE_ALIGN_CENTER
     *                        CONSOLE_TABLE_ALIGN_RIGHT.
     *
     * @return void
     */
    function setAlign($col_id, $align = CONSOLE_TABLE_ALIGN_LEFT)
    {
        switch ($align) {
        case CONSOLE_TABLE_ALIGN_CENTER:
            $pad = STR_PAD_BOTH;
            break;
        case CONSOLE_TABLE_ALIGN_RIGHT:
            $pad = STR_PAD_LEFT;
            break;
        default:
            $pad = STR_PAD_RIGHT;
            break;
        }
        $this->_col_align[$col_id] = $pad;
    }

    /**
     * Specifies which columns are to have totals calculated for them and
     * added as a new row at the bottom.
     *
     * @param array $cols Array of column numbers (starting with 0).
     *
     * @return void
     */
    function calculateTotalsFor($cols)
    {
        $this->_calculateTotals = $cols;
    }

    /**
     * Sets the headers for the columns.
     *
     * @param array $headers The column headers.
     *
     * @return void
     */
    function setHeaders($headers)
    {
        $this->_headers = array(array_values($headers));
        $this->_updateRowsCols($headers);
    }

    /**
     * Adds a row to the table.
     *
     * @param array   $row    The row data to add.
     * @param boolean $append Whether to append or prepend the row.
     *
     * @return void
     */
    function addRow($row, $append = true)
    {
        if ($append) {
            $this->_data[] = array_values($row);
        } else {
            array_unshift($this->_data, array_values($row));
        }

        $this->_updateRowsCols($row);
    }

    /**
     * Inserts a row after a given row number in the table.
     *
     * If $row_id is not given it will prepend the row.
     *
     * @param array   $row    The data to insert.
     * @param integer $row_id Row number to insert before.
     *
     * @return void
     */
    function insertRow($row, $row_id = 0)
    {
        array_splice($this->_data, $row_id, 0, array($row));

        $this->_updateRowsCols($row);
    }

    /**
     * Adds a column to the table.
     *
     * @param array   $col_data The data of the column.
     * @param integer $col_id   The column index to populate.
     * @param integer $row_id   If starting row is not zero, specify it here.
     *
     * @return void
     */
    function addCol($col_data, $col_id = 0, $row_id = 0)
    {
        foreach ($col_data as $col_cell) {
            $this->_data[$row_id++][$col_id] = $col_cell;
        }

        $this->_updateRowsCols();
        $this->_max_cols = max($this->_max_cols, $col_id + 1);
    }

    /**
     * Adds data to the table.
     *
     * @param array   $data   A two dimensional array with the table data.
     * @param integer $col_id Starting column number.
     * @param integer $row_id Starting row number.
     *
     * @return void
     */
    function addData($data, $col_id = 0, $row_id = 0)
    {
        foreach ($data as $row) {
            if ($row === CONSOLE_TABLE_HORIZONTAL_RULE) {
                $this->_data[$row_id] = CONSOLE_TABLE_HORIZONTAL_RULE;
                $row_id++;
                continue;
            }
            $starting_col = $col_id;
            foreach ($row as $cell) {
                $this->_data[$row_id][$starting_col++] = $cell;
            }
            $this->_updateRowsCols();
            $this->_max_cols = max($this->_max_cols, $starting_col);
            $row_id++;
        }
    }

    /**
     * Adds a horizontal seperator to the table.
     *
     * @return void
     */
    function addSeparator()
    {
        $this->_data[] = CONSOLE_TABLE_HORIZONTAL_RULE;
    }

    /**
     * Returns the generated table.
     *
     * @return string  The generated table.
     */
    function getTable()
    {
        $this->_applyFilters();
        $this->_calculateTotals();
        $this->_validateTable();

        return $this->_buildTable();
    }

    /**
     * Calculates totals for columns.
     *
     * @return void
     */
    function _calculateTotals()
    {
        if (empty($this->_calculateTotals)) {
            return;
        }

        $this->addSeparator();

        $totals = array();
        foreach ($this->_data as $row) {
            if (is_array($row)) {
                foreach ($this->_calculateTotals as $columnID) {
                    $totals[$columnID] += $row[$columnID];
                }
            }
        }

        $this->_data[] = $totals;
        $this->_updateRowsCols();
    }

    /**
     * Applies any column filters to the data.
     *
     * @return void
     */
    function _applyFilters()
    {
        if (empty($this->_filters)) {
            return;
        }

        foreach ($this->_filters as $filter) {
            $column   = $filter[0];
            $callback = $filter[1];

            foreach ($this->_data as $row_id => $row_data) {
                if ($row_data !== CONSOLE_TABLE_HORIZONTAL_RULE) {
                    $this->_data[$row_id][$column] =
                        call_user_func($callback, $row_data[$column]);
                }
            }
        }
    }

    /**
     * Ensures that column and row counts are correct.
     *
     * @return void
     */
    function _validateTable()
    {
        if (!empty($this->_headers)) {
            $this->_calculateRowHeight(-1, $this->_headers[0]);
        }

        for ($i = 0; $i < $this->_max_rows; $i++) {
            for ($j = 0; $j < $this->_max_cols; $j++) {
                if (!isset($this->_data[$i][$j]) &&
                    (!isset($this->_data[$i]) ||
                     $this->_data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE)) {
                    $this->_data[$i][$j] = '';
                }

            }
            $this->_calculateRowHeight($i, $this->_data[$i]);

            if ($this->_data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE) {
                 ksort($this->_data[$i]);
            }

        }

        $this->_splitMultilineRows();

        // Update cell lengths.
        $count_headers = count($this->_headers);
        for ($i = 0; $i < $count_headers; $i++) {
            $this->_calculateCellLengths($this->_headers[$i]);
        }
        for ($i = 0; $i < $this->_max_rows; $i++) {
            $this->_calculateCellLengths($this->_data[$i]);
        }

        ksort($this->_data);
    }

    /**
     * Splits multiline rows into many smaller one-line rows.
     *
     * @return void
     */
    function _splitMultilineRows()
    {
        ksort($this->_data);
        $sections          = array(&$this->_headers, &$this->_data);
        $max_rows          = array(count($this->_headers), $this->_max_rows);
        $row_height_offset = array(-1, 0);

        for ($s = 0; $s <= 1; $s++) {
            $inserted = 0;
            $new_data = $sections[$s];

            for ($i = 0; $i < $max_rows[$s]; $i++) {
                // Process only rows that have many lines.
                $height = $this->_row_heights[$i + $row_height_offset[$s]];
                if ($height > 1) {
                    // Split column data into one-liners.
                    $split = array();
                    for ($j = 0; $j < $this->_max_cols; $j++) {
                        $split[$j] = preg_split('/\r?\n|\r/',
                                                $sections[$s][$i][$j]);
                    }

                    $new_rows = array();
                    // Construct new 'virtual' rows - insert empty strings for
                    // columns that have less lines that the highest one.
                    for ($i2 = 0; $i2 < $height; $i2++) {
                        for ($j = 0; $j < $this->_max_cols; $j++) {
                            $new_rows[$i2][$j] = !isset($split[$j][$i2])
                                ? ''
                                : $split[$j][$i2];
                        }
                    }

                    // Replace current row with smaller rows.  $inserted is
                    // used to take account of bigger array because of already
                    // inserted rows.
                    array_splice($new_data, $i + $inserted, 1, $new_rows);
                    $inserted += count($new_rows) - 1;
                }
            }

            // Has the data been modified?
            if ($inserted > 0) {
                $sections[$s] = $new_data;
                $this->_updateRowsCols();
            }
        }
    }

    /**
     * Builds the table.
     *
     * @return string  The generated table string.
     */
    function _buildTable()
    {
        if (!count($this->_data)) {
            return '';
        }

        $rule      = $this->_border == CONSOLE_TABLE_BORDER_ASCII
            ? '|'
            : $this->_border;
        $separator = $this->_getSeparator();

        $return = array();
        $count_data = count($this->_data);
        for ($i = 0; $i < $count_data; $i++) {
            $count_data_i = count($this->_data[$i]);
            for ($j = 0; $j < $count_data_i; $j++) {
                if ($this->_data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE &&
                    $this->_strlen($this->_data[$i][$j]) <
                    $this->_cell_lengths[$j]) {
                    $this->_data[$i][$j] = $this->_strpad($this->_data[$i][$j],
                                                          $this->_cell_lengths[$j],
                                                          ' ',
                                                          $this->_col_align[$j]);
                }
            }

            if ($this->_data[$i] !== CONSOLE_TABLE_HORIZONTAL_RULE) {
                $row_begin    = $rule . str_repeat(' ', $this->_padding);
                $row_end      = str_repeat(' ', $this->_padding) . $rule;
                $implode_char = str_repeat(' ', $this->_padding) . $rule
                    . str_repeat(' ', $this->_padding);
                $return[]     = $row_begin
                    . implode($implode_char, $this->_data[$i]) . $row_end;
            } elseif (!empty($separator)) {
                $return[] = $separator;
            }

        }

        $return = implode(PHP_EOL, $return);
        if (!empty($separator)) {
            $return = $separator . PHP_EOL . $return . PHP_EOL . $separator;
        }
        $return .= PHP_EOL;

        if (!empty($this->_headers)) {
            $return = $this->_getHeaderLine() .  PHP_EOL . $return;
        }

        return $return;
    }

    /**
     * Creates a horizontal separator for header separation and table
     * start/end etc.
     *
     * @return string  The horizontal separator.
     */
    function _getSeparator()
    {
        if (!$this->_border) {
            return;
        }

        if ($this->_border == CONSOLE_TABLE_BORDER_ASCII) {
            $rule = '-';
            $sect = '+';
        } else {
            $rule = $sect = $this->_border;
        }

        $return = array();
        foreach ($this->_cell_lengths as $cl) {
            $return[] = str_repeat($rule, $cl);
        }

        $row_begin    = $sect . str_repeat($rule, $this->_padding);
        $row_end      = str_repeat($rule, $this->_padding) . $sect;
        $implode_char = str_repeat($rule, $this->_padding) . $sect
            . str_repeat($rule, $this->_padding);

        return $row_begin . implode($implode_char, $return) . $row_end;
    }

    /**
     * Returns the header line for the table.
     *
     * @return string  The header line of the table.
     */
    function _getHeaderLine()
    {
        // Make sure column count is correct
        $count_headers = count($this->_headers);
        for ($j = 0; $j < $count_headers; $j++) {
            for ($i = 0; $i < $this->_max_cols; $i++) {
                if (!isset($this->_headers[$j][$i])) {
                    $this->_headers[$j][$i] = '';
                }
            }
        }

        $count_headers = count($this->_headers);
        for ($j = 0; $j < $count_headers; $j++) {
            $count_headers_j = count($this->_headers[$j]);
            for ($i = 0; $i < $count_headers_j; $i++) {
                if ($this->_strlen($this->_headers[$j][$i]) <
                    $this->_cell_lengths[$i]) {
                    $this->_headers[$j][$i] =
                        $this->_strpad($this->_headers[$j][$i],
                                       $this->_cell_lengths[$i],
                                       ' ',
                                       $this->_col_align[$i]);
                }
            }
        }

        $rule         = $this->_border == CONSOLE_TABLE_BORDER_ASCII
            ? '|'
            : $this->_border;
        $row_begin    = $rule . str_repeat(' ', $this->_padding);
        $row_end      = str_repeat(' ', $this->_padding) . $rule;
        $implode_char = str_repeat(' ', $this->_padding) . $rule
            . str_repeat(' ', $this->_padding);

        $separator = $this->_getSeparator();
        if (!empty($separator)) {
            $return[] = $separator;
        }
        for ($j = 0; $j < count($this->_headers); $j++) {
            $return[] = $row_begin
                . implode($implode_char, $this->_headers[$j]) . $row_end;
        }

        return implode(PHP_EOL, $return);
    }

    /**
     * Updates values for maximum columns and rows.
     *
     * @param array $rowdata Data array of a single row.
     *
     * @return void
     */
    function _updateRowsCols($rowdata = null)
    {
        // Update maximum columns.
        $this->_max_cols = max($this->_max_cols, count($rowdata));

        // Update maximum rows.
        ksort($this->_data);
        $keys            = array_keys($this->_data);
        $this->_max_rows = end($keys) + 1;

        switch ($this->_defaultAlign) {
        case CONSOLE_TABLE_ALIGN_CENTER:
            $pad = STR_PAD_BOTH;
            break;
        case CONSOLE_TABLE_ALIGN_RIGHT:
            $pad = STR_PAD_LEFT;
            break;
        default:
            $pad = STR_PAD_RIGHT;
            break;
        }

        // Set default column alignments
        for ($i = count($this->_col_align); $i < $this->_max_cols; $i++) {
            $this->_col_align[$i] = $pad;
        }
    }

    /**
     * Calculates the maximum length for each column of a row.
     *
     * @param array $row The row data.
     *
     * @return void
     */
    function _calculateCellLengths($row)
    {
        $count_row = count($row);
        for ($i = 0; $i < $count_row; $i++) {
            if (!isset($this->_cell_lengths[$i])) {
                $this->_cell_lengths[$i] = 0;
            }
            $this->_cell_lengths[$i] = max($this->_cell_lengths[$i],
                                           $this->_strlen($row[$i]));
        }
    }

    /**
     * Calculates the maximum height for all columns of a row.
     *
     * @param integer $row_number The row number.
     * @param array   $row        The row data.
     *
     * @return void
     */
    function _calculateRowHeight($row_number, $row)
    {
        if (!isset($this->_row_heights[$row_number])) {
            $this->_row_heights[$row_number] = 1;
        }

        // Do not process horizontal rule rows.
        if ($row === CONSOLE_TABLE_HORIZONTAL_RULE) {
            return;
        }

        for ($i = 0, $c = count($row); $i < $c; ++$i) {
            $lines                           = preg_split('/\r?\n|\r/', $row[$i]);
            $this->_row_heights[$row_number] = max($this->_row_heights[$row_number],
                                                   count($lines));
        }
    }

    /**
     * Returns the character length of a string.
     *
     * @param string $str A multibyte or singlebyte string.
     *
     * @return integer  The string length.
     */
    function _strlen($str)
    {
        static $mbstring;

        // Strip ANSI color codes if requested.
        if ($this->_ansiColor) {
            $str = Console_Color::strip($str);
        }

        // Cache expensive function_exists() calls.
        if (!isset($mbstring)) {
            $mbstring = function_exists('mb_strwidth');
        }

        if ($mbstring) {
            return mb_strwidth($str, $this->_charset);
        }

        return strlen($str);
    }

    /**
     * Returns part of a string.
     *
     * @param string  $string The string to be converted.
     * @param integer $start  The part's start position, zero based.
     * @param integer $length The part's length.
     *
     * @return string  The string's part.
     */
    function _substr($string, $start, $length = null)
    {
        static $mbstring;

        // Cache expensive function_exists() calls.
        if (!isset($mbstring)) {
            $mbstring = function_exists('mb_substr');
        }

        if (is_null($length)) {
            $length = $this->_strlen($string);
        }
        if ($mbstring) {
            $ret = @mb_substr($string, $start, $length, $this->_charset);
            if (!empty($ret)) {
                return $ret;
            }
        }
        return substr($string, $start, $length);
    }

    /**
     * Returns a string padded to a certain length with another string.
     *
     * This method behaves exactly like str_pad but is multibyte safe.
     *
     * @param string  $input  The string to be padded.
     * @param integer $length The length of the resulting string.
     * @param string  $pad    The string to pad the input string with. Must
     *                        be in the same charset like the input string.
     * @param const   $type   The padding type. One of STR_PAD_LEFT,
     *                        STR_PAD_RIGHT, or STR_PAD_BOTH.
     *
     * @return string  The padded string.
     */
    function _strpad($input, $length, $pad = ' ', $type = STR_PAD_RIGHT)
    {
        $mb_length  = $this->_strlen($input);
        $sb_length  = strlen($input);
        $pad_length = $this->_strlen($pad);

        /* Return if we already have the length. */
        if ($mb_length >= $length) {
            return $input;
        }

        /* Shortcut for single byte strings. */
        if ($mb_length == $sb_length && $pad_length == strlen($pad)) {
            return str_pad($input, $length, $pad, $type);
        }

        switch ($type) {
        case STR_PAD_LEFT:
            $left   = $length - $mb_length;
            $output = $this->_substr(str_repeat($pad, ceil($left / $pad_length)),
                                     0, $left, $this->_charset) . $input;
            break;
        case STR_PAD_BOTH:
            $left   = floor(($length - $mb_length) / 2);
            $right  = ceil(($length - $mb_length) / 2);
            $output = $this->_substr(str_repeat($pad, ceil($left / $pad_length)),
                                     0, $left, $this->_charset) .
                $input .
                $this->_substr(str_repeat($pad, ceil($right / $pad_length)),
                               0, $right, $this->_charset);
            break;
        case STR_PAD_RIGHT:
            $right  = $length - $mb_length;
            $output = $input .
                $this->_substr(str_repeat($pad, ceil($right / $pad_length)),
                               0, $right, $this->_charset);
            break;
        }

        return $output;
    }

}
