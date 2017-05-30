<?php
/**
 * PHP Command Line Tools
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @author    James Logsdon <dwarf@girsbrain.org>
 * @copyright 2010 James Logsdom (http://girsbrain.org)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace cli;

use cli\Shell;
use cli\Streams;
use cli\table\Ascii;
use cli\table\Renderer;
use cli\table\Tabular;

/**
 * The `Table` class is used to display data in a tabular format.
 */
class Table {
	protected $_renderer;
	protected $_headers = array();
	protected $_footers = array();
	protected $_width = array();
	protected $_rows = array();
	protected $_use_index = false;

	/**
	 * Initializes the `Table` class.
	 *
	 * There are 3 ways to instantiate this class:
	 *
	 *  1. Pass an array of strings as the first parameter for the column headers
	 *     and a 2-dimensional array as the second parameter for the data rows.
	 *  2. Pass an array of hash tables (string indexes instead of numerical)
	 *     where each hash table is a row and the indexes of the *first* hash
	 *     table are used as the header values.
	 *  3. Pass nothing and use `setHeaders()` and `addRow()` or `setRows()`.
	 *
	 * @param array  $headers  Headers used in this table. Optional.
	 * @param array  $rows     The rows of data for this table. Optional.
	 * @param array  $footers  Footers used in this table. Optional.
	 */
	public function __construct(array $headers = null, array $rows = null, array $footers = null) {
		if (!empty($headers)) {
			// If all the rows is given in $headers we use the keys from the
			// first row for the header values
			if ($rows === null) {
				$rows = $headers;
				$keys = array_keys(array_shift($headers));
				$headers = array();

				foreach ($keys as $header) {
					$headers[$header] = $header;
				}
			}

			$this->setHeaders($headers);
			$this->setRows($rows);
		}

		if (!empty($footers)) {
			$this->setFooters($footers);
		}

		if (Shell::isPiped()) {
			$this->setRenderer(new Tabular());
		} else {
			$this->setRenderer(new Ascii());
		}
	}

	public function resetTable()
	{
		$this->_headers = array();
		$this->_width = array();
		$this->_rows = array();
		$this->_footers = array();
		$this->_use_index = false;
		return $this;
	}

	/**
	 * Sets the renderer used by this table.
	 *
	 * @param table\Renderer  $renderer  The renderer to use for output.
	 * @see   table\Renderer
	 * @see   table\Ascii
	 * @see   table\Tabular
	 */
	public function setRenderer(Renderer $renderer) {
		$this->_renderer = $renderer;
	}

	/**
	 * Loops through the row and sets the maximum width for each column.
	 *
	 * @param array  $row  The table row.
     * @return array $row
	 */
	protected function checkRow(array $row) {
		foreach ($row as $column => $str) {
			$width = Colors::length($str);
			if (!isset($this->_width[$column]) || $width > $this->_width[$column]) {
				$this->_width[$column] = $width;
			}
		}

		return $row;
	}

	/**
	 * Output the table to `STDOUT` using `cli\line()`.
	 *
	 * If STDOUT is a pipe or redirected to a file, should output simple
	 * tab-separated text. Otherwise, renders table with ASCII table borders
	 *
	 * @uses cli\Shell::isPiped() Determine what format to output
	 *
	 * @see cli\Table::renderRow()
	 */
	public function display() {
		foreach( $this->getDisplayLines() as $line ) {
			Streams::line( $line );
		}
	}

	/**
	 * Get the table lines to output.
	 *
	 * @see cli\Table::display()
	 * @see cli\Table::renderRow()
	 *
	 * @return array
	 */
	public function getDisplayLines() {
		$this->_renderer->setWidths($this->_width);
		$border = $this->_renderer->border();

		$out = array();
		if (isset($border)) {
			$out[] = $border;
		}
		$out[] = $this->_renderer->row($this->_headers);
		if (isset($border)) {
			$out[] = $border;
		}

		foreach ($this->_rows as $row) {
			$row = $this->_renderer->row($row);
			$row = explode( PHP_EOL, $row );
			$out = array_merge( $out, $row );
		}

		if (isset($border)) {
			$out[] = $border;
		}

		if ($this->_footers) {
			$out[] = $this->_renderer->row($this->_footers);
			if (isset($border)) {
				$out[] = $border;
			}
		}
		return $out;
	}

	/**
	 * Sort the table by a column. Must be called before `cli\Table::display()`.
	 *
	 * @param int  $column  The index of the column to sort by.
	 */
	public function sort($column) {
		if (!isset($this->_headers[$column])) {
			trigger_error('No column with index ' . $column, E_USER_NOTICE);
			return;
		}

		usort($this->_rows, function($a, $b) use ($column) {
			return strcmp($a[$column], $b[$column]);
		});
	}

	/**
	 * Set the headers of the table.
	 *
	 * @param array  $headers  An array of strings containing column header names.
	 */
	public function setHeaders(array $headers) {
		$this->_headers = $this->checkRow($headers);
	}

	/**
	 * Set the footers of the table.
	 *
	 * @param array  $footers  An array of strings containing column footers names.
	 */
	public function setFooters(array $footers) {
		$this->_footers = $this->checkRow($footers);
	}


	/**
	 * Add a row to the table.
	 *
	 * @param array  $row  The row data.
	 * @see cli\Table::checkRow()
	 */
	public function addRow(array $row) {
		$this->_rows[] = $this->checkRow($row);
	}

    /**
     * Sets if we should output the arrays index
     */
	public function useIndex()
    {
        $this->_use_index = true;
    }

	/**
	 * Clears all previous rows and adds the given rows.
	 *
	 * @param array  $rows  A 2-dimensional array of row data.
	 * @see cli\Table::addRow()
	 */
	public function setRows(array $rows) {
		$this->_rows = array();
		foreach ($rows as $id => $row) {
		    if ($this->_use_index === true) {
		        $row = array_merge(array($id), $row);
            }
			$this->addRow($row);
		}
	}

	public function countRows() {
		return count($this->_rows);
	}
}
