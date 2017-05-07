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

use cli\arguments\Argument;
use cli\arguments\HelpScreen;
use cli\arguments\InvalidArguments;
use cli\arguments\Lexer;

/**
 * Parses command line arguments.
 */
class Arguments implements \ArrayAccess {
	protected $_flags = array();
	protected $_options = array();
	protected $_strict = false;
	protected $_input = array();
	protected $_invalid = array();
	protected $_parsed;
	protected $_lexer;

	/**
	 * Initializes the argument parser. If you wish to change the default behaviour
	 * you may pass an array of options as the first argument. Valid options are
	 * `'help'` and `'strict'`, each a boolean.
	 *
	 * `'help'` is `true` by default, `'strict'` is false by default.
	 *
	 * @param  array  $options  An array of options for this parser.
	 */
	public function __construct($options = array()) {
		$options += array(
			'strict' => false,
			'input'  => array_slice($_SERVER['argv'], 1)
		);

		$this->_input = $options['input'];
		$this->setStrict($options['strict']);

		if (isset($options['flags'])) {
			$this->addFlags($options['flags']);
		}
		if (isset($options['options'])) {
			$this->addOptions($options['options']);
		}
	}

	/**
	 * Get the list of arguments found by the defined definitions.
	 *
	 * @return array
	 */
	public function getArguments() {
		if (!isset($this->_parsed)) {
			$this->parse();
		}
		return $this->_parsed;
	}

	public function getHelpScreen() {
		return new HelpScreen($this);
	}

	/**
	 * Encodes the parsed arguments as JSON.
	 *
	 * @return string
	 */
	public function asJSON() {
		return json_encode($this->_parsed);
	}

	/**
	 * Returns true if a given argument was parsed.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 * @return bool
	 */
	public function offsetExists($offset) {
		if ($offset instanceOf Argument) {
			$offset = $offset->key;
		}

		return array_key_exists($offset, $this->_parsed);
	}

	/**
	 * Get the parsed argument's value.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 * @return mixed
	 */
	public function offsetGet($offset) {
		if ($offset instanceOf Argument) {
			$offset = $offset->key;
		}

		if (isset($this->_parsed[$offset])) {
			return $this->_parsed[$offset];
		}
	}

	/**
	 * Sets the value of a parsed argument.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 * @param mixed  $value   The value to set
	 */
	public function offsetSet($offset, $value) {
		if ($offset instanceOf Argument) {
			$offset = $offset->key;
		}

		$this->_parsed[$offset] = $value;
	}

	/**
	 * Unset a parsed argument.
	 *
	 * @param mixed  $offset  An Argument object or the name of the argument.
	 */
	public function offsetUnset($offset) {
		if ($offset instanceOf Argument) {
			$offset = $offset->key;
		}

		unset($this->_parsed[$offset]);
	}

	/**
	 * Adds a flag (boolean argument) to the argument list.
	 *
	 * @param mixed  $flag  A string representing the flag, or an array of strings.
	 * @param array  $settings  An array of settings for this flag.
	 * @setting string  description  A description to be shown in --help.
	 * @setting bool    default  The default value for this flag.
	 * @setting bool    stackable  Whether the flag is repeatable to increase the value.
	 * @setting array   aliases  Other ways to trigger this flag.
	 * @return $this
	 */
	public function addFlag($flag, $settings = array()) {
		if (is_string($settings)) {
			$settings = array('description' => $settings);
		}
		if (is_array($flag)) {
			$settings['aliases'] = $flag;
			$flag = array_shift($settings['aliases']);
		}
		if (isset($this->_flags[$flag])) {
			$this->_warn('flag already exists: ' . $flag);
			return $this;
		}

		$settings += array(
			'default'     => false,
			'stackable'   => false,
			'description' => null,
			'aliases'     => array()
		);

		$this->_flags[$flag] = $settings;
		return $this;
	}

	/**
	 * Add multiple flags at once. The input array should be keyed with the
	 * primary flag character, and the values should be the settings array
	 * used by {addFlag}.
	 *
	 * @param array  $flags  An array of flags to add
	 * @return $this
	 */
	public function addFlags($flags) {
		foreach ($flags as $flag => $settings) {
			if (is_numeric($flag)) {
				$this->_warn('No flag character given');
				continue;
			}

			$this->addFlag($flag, $settings);
		}

		return $this;
	}

	/**
	 * Adds an option (string argument) to the argument list.
	 *
	 * @param mixed  $option  A string representing the option, or an array of strings.
	 * @param array  $settings  An array of settings for this option.
	 * @setting string  description  A description to be shown in --help.
	 * @setting bool    default  The default value for this option.
	 * @setting array   aliases  Other ways to trigger this option.
	 * @return $this
	 */
	public function addOption($option, $settings = array()) {
		if (is_string($settings)) {
			$settings = array('description' => $settings);
		}
		if (is_array($option)) {
			$settings['aliases'] = $option;
			$option = array_shift($settings['aliases']);
		}
		if (isset($this->_options[$option])) {
			$this->_warn('option already exists: ' . $option);
			return $this;
		}

		$settings += array(
			'default'     => null,
			'description' => null,
			'aliases'     => array()
		);

		$this->_options[$option] = $settings;
		return $this;
	}

	/**
	 * Add multiple options at once. The input array should be keyed with the
	 * primary option string, and the values should be the settings array
	 * used by {addOption}.
	 *
	 * @param array  $options  An array of options to add
	 * @return $this
	 */
	public function addOptions($options) {
		foreach ($options as $option => $settings) {
			if (is_numeric($option)) {
				$this->_warn('No option string given');
				continue;
			}

			$this->addOption($option, $settings);
		}

		return $this;
	}

	/**
	 * Enable or disable strict mode. If strict mode is active any invalid
	 * arguments found by the parser will throw `cli\arguments\InvalidArguments`.
	 *
	 * Even if strict is disabled, invalid arguments are logged and can be
	 * retrieved with `cli\Arguments::getInvalidArguments()`.
	 *
	 * @param bool  $strict  True to enable, false to disable.
	 * @return $this
	 */
	public function setStrict($strict) {
		$this->_strict = (bool)$strict;
		return $this;
	}

	/**
	 * Get the list of invalid arguments the parser found.
	 *
	 * @return array
	 */
	public function getInvalidArguments() {
		return $this->_invalid;
	}

	/**
	 * Get a flag by primary matcher or any defined aliases.
	 *
	 * @param mixed  $flag  Either a string representing the flag or an
	 *                      cli\arguments\Argument object.
	 * @return array
	 */
	public function getFlag($flag) {
		if ($flag instanceOf Argument) {
			$obj  = $flag;
			$flag = $flag->value;
		}

		if (isset($this->_flags[$flag])) {
			return $this->_flags[$flag];
		}

		foreach ($this->_flags as $master => $settings) {
			if (in_array($flag, (array)$settings['aliases'])) {
				if (isset($obj)) {
					$obj->key = $master;
				}

				$cache[$flag] =& $settings;
				return $settings;
			}
		}
	}

	public function getFlags() {
		return $this->_flags;
	}

	public function hasFlags() {
		return !empty($this->_flags);
	}

	/**
	 * Returns true if the given argument is defined as a flag.
	 *
	 * @param mixed  $argument  Either a string representing the flag or an
	 *                          cli\arguments\Argument object.
	 * @return bool
	 */
	public function isFlag($argument) {
		return (null !== $this->getFlag($argument));
	}

	/**
	 * Returns true if the given flag is stackable.
	 *
	 * @param mixed  $flag  Either a string representing the flag or an
	 *                      cli\arguments\Argument object.
	 * @return bool
	 */
	public function isStackable($flag) {
		$settings = $this->getFlag($flag);

		return isset($settings) && (true === $settings['stackable']);
	}

	/**
	 * Get an option by primary matcher or any defined aliases.
	 *
	 * @param mixed  $option Either a string representing the option or an
	 *                       cli\arguments\Argument object.
	 * @return array
	 */
	public function getOption($option) {
		if ($option instanceOf Argument) {
			$obj = $option;
			$option = $option->value;
		}

		if (isset($this->_options[$option])) {
			return $this->_options[$option];
		}

		foreach ($this->_options as $master => $settings) {
			if (in_array($option, (array)$settings['aliases'])) {
				if (isset($obj)) {
					$obj->key = $master;
				}

				return $settings;
			}
		}
	}

	public function getOptions() {
		return $this->_options;
	}

	public function hasOptions() {
		return !empty($this->_options);
	}

	/**
	 * Returns true if the given argument is defined as an option.
	 *
	 * @param mixed  $argument  Either a string representing the option or an
	 *                          cli\arguments\Argument object.
	 * @return bool
	 */
	public function isOption($argument) {
		return (null != $this->getOption($argument));
	}

	/**
	 * Parses the argument list with the given options. The returned argument list
	 * will use either the first long name given or the first name in the list
	 * if a long name is not given.
	 *
	 * @return array
	 * @throws arguments\InvalidArguments
	 */
	public function parse() {
		$this->_invalid = array();
		$this->_parsed = array();
		$this->_lexer = new Lexer($this->_input);

		$this->_applyDefaults();

		foreach ($this->_lexer as $argument) {
			if ($this->_parseFlag($argument)) {
				continue;
			}
			if ($this->_parseOption($argument)) {
				continue;
			}

			array_push($this->_invalid, $argument->raw);
		}

		if ($this->_strict && !empty($this->_invalid)) {
			throw new InvalidArguments($this->_invalid);
		}
	}

	/**
	 * This applies the default values, if any, of all of the
	 * flags and options, so that if there is a default value
	 * it will be available.
	 */
	private function _applyDefaults() {
		foreach($this->_flags as $flag => $settings) {
			$this[$flag] = $settings['default'];
		}

		foreach($this->_options as $option => $settings) {
			// If the default is 0 we should still let it be set.
			if (!empty($settings['default']) || $settings['default'] === 0) {
				$this[$option] = $settings['default'];
			}
		}
	}

	private function _warn($message) {
		trigger_error('[' . __CLASS__ .'] ' . $message, E_USER_WARNING);
	}

	private function _parseFlag($argument) {
		if (!$this->isFlag($argument)) {
			return false;
		}

		if ($this->isStackable($argument)) {
			if (!isset($this[$argument])) {
				$this[$argument->key] = 0;
			}

			$this[$argument->key] += 1;
		} else {
			$this[$argument->key] = true;
		}

		return true;
	}

	private function _parseOption($option) {
		if (!$this->isOption($option)) {
			return false;
		}

		// Peak ahead to make sure we get a value.
		if ($this->_lexer->end() || !$this->_lexer->peek->isValue) {
			$optionSettings = $this->getOption($option->key);

			if (empty($optionSettings['default']) && $optionSettings !== 0) {
				// Oops! Got no value and no default , throw a warning and continue.
				$this->_warn('no value given for ' . $option->raw);
				$this[$option->key] = null;
			} else {
				// No value and we have a default, so we set to the default
				$this[$option->key] = $optionSettings['default'];
			}
			return true;
		}

		// Store as array and join to string after looping for values
		$values = array();

		// Loop until we find a flag in peak-ahead
		foreach ($this->_lexer as $value) {
			array_push($values, $value->raw);

			if (!$this->_lexer->end() && !$this->_lexer->peek->isValue) {
				break;
			}
		}

		$this[$option->key] = join($values, ' ');
		return true;
	}
}
