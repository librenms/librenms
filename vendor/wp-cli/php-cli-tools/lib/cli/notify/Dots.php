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

namespace cli\notify;

use cli\Notify;
use cli\Streams;

/**
 * A Notifer that displays a string of periods.
 */
class Dots extends Notify {
	protected $_dots;
	protected $_format = '{:msg}{:dots}  ({:elapsed}, {:speed}/s)';
	protected $_iteration;

	/**
	 * Instatiates a Notification object.
	 *
	 * @param string  $msg       The text to display next to the Notifier.
	 * @param int     $dots      The number of dots to iterate through.
	 * @param int     $interval  The interval in milliseconds between updates.
	 * @throws \InvalidArgumentException
	 */
	public function __construct($msg, $dots = 3, $interval = 100) {
		parent::__construct($msg, $interval);
		$this->_dots = (int)$dots;

		if ($this->_dots <= 0) {
			throw new \InvalidArgumentException('Dot count out of range, must be positive.');
		}
	}

	/**
	 * Prints the correct number of dots to `STDOUT` with the time elapsed and
	 * tick speed.
	 *
	 * @param boolean  $finish  `true` if this was called from
	 *                          `cli\Notify::finish()`, `false` otherwise.
	 * @see cli\out_padded()
	 * @see cli\Notify::formatTime()
	 * @see cli\Notify::speed()
	 */
	public function display($finish = false) {
		$repeat = $this->_dots;
		if (!$finish) {
			$repeat = $this->_iteration++ % $repeat;
		}

		$msg = $this->_message;
		$dots = str_pad(str_repeat('.', $repeat), $this->_dots);
		$speed = number_format(round($this->speed()));
		$elapsed = $this->formatTime($this->elapsed());

		Streams::out_padded($this->_format, compact('msg', 'dots', 'speed', 'elapsed'));
	}
}
