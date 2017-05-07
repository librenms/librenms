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

namespace cli\table;

/**
 * Table renderers are used to change how a table is displayed.
 */
abstract class Renderer {
	protected $_widths = array();

	public function __construct(array $widths = array()) {
		$this->setWidths($widths);
	}

	/**
	 * Set the widths of each column in the table.
	 *
	 * @param array  $widths  The widths of the columns.
	 */
	public function setWidths(array $widths) {
		$this->_widths = $widths;
	}

	/**
	 * Render a border for the top and bottom and separating the headers from the
	 * table rows.
	 *
	 * @return string  The table border.
	 */
	public function border() {
		return null;
	}

	/**
	 * Renders a row for output.
	 *
	 * @param array  $row  The table row.
	 * @return string  The formatted table row.
	 */
	abstract public function row(array $row);
}
