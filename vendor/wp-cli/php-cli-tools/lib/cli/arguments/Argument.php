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

namespace cli\arguments;

use cli\Memoize;

/**
 * Represents an Argument or a value and provides several helpers related to parsing an argument list.
 */
class Argument extends Memoize {
	/**
	 * The canonical name of this argument, used for aliasing.
	 *
	 * @param string
	 */
	public $key;

	private $_argument;
	private $_raw;

	/**
	 * @param string  $argument  The raw argument, leading dashes included.
	 */
	public function __construct($argument) {
		$this->_raw = $argument;
		$this->key =& $this->_argument;

		if ($this->isLong) {
			$this->_argument = substr($this->_raw, 2);
		} else if ($this->isShort) {
			$this->_argument = substr($this->_raw, 1);
		} else {
			$this->_argument = $this->_raw;
		}
	}

	/**
	 * Returns the raw input as a string.
	 *
	 * @return string
	 */
	public function __toString() {
		return (string)$this->_raw;
	}

	/**
	 * Returns the formatted argument string.
	 *
	 * @return string
	 */
	public function value() {
		return $this->_argument;
	}

	/**
	 * Returns the raw input.
	 *
	 * @return mixed
	 */
	public function raw() {
		return $this->_raw;
	}

	/**
	 * Returns true if the string matches the pattern for long arguments.
	 *
	 * @return bool
	 */
	public function isLong() {
		return (0 == strncmp($this->_raw, '--', 2));
	}

	/**
	 * Returns true if the string matches the pattern for short arguments.
	 *
	 * @return bool
	 */
	public function isShort() {
		return !$this->isLong && (0 == strncmp($this->_raw, '-', 1));
	}

	/**
	 * Returns true if the string matches the pattern for arguments.
	 *
	 * @return bool
	 */
	public function isArgument() {
		return $this->isShort() || $this->isLong();
	}

	/**
	 * Returns true if the string matches the pattern for values.
	 *
	 * @return bool
	 */
	public function isValue() {
		return !$this->isArgument;
	}

	/**
	 * Returns true if the argument is short but contains several characters. Each
	 * character is considered a separate argument.
	 *
	 * @return bool
	 */
	public function canExplode() {
		return $this->isShort && strlen($this->_argument) > 1;
	}

	/**
	 * Returns all but the first character of the argument, removing them from the
	 * objects representation at the same time.
	 *
	 * @return array
	 */
	public function exploded() {
		$exploded = array();

		for ($i = strlen($this->_argument); $i > 0; $i--) {
			array_push($exploded, $this->_argument[$i - 1]);
		}

		$this->_argument = array_pop($exploded);
		$this->_raw      = '-' . $this->_argument;
		return $exploded;
	}
}
