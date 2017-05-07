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

abstract class Memoize {
	protected $_memoCache = array();

	public function __get($name) {
		if (isset($this->_memoCache[$name])) {
			return $this->_memoCache[$name];
		}

		// Hide probable private methods
		if (0 == strncmp($name, '_', 1)) {
			return ($this->_memoCache[$name] = null);
		}

		if (!method_exists($this, $name)) {
			return ($this->_memoCache[$name] = null);
		}

		$method = array($this, $name);
		($this->_memoCache[$name] = call_user_func($method));
		return $this->_memoCache[$name];
	}

	protected function _unmemo($name) {
		if ($name === true) {
			$this->_memoCache = array();
		} else {
			unset($this->_memoCache[$name]);
		}
	}
}
