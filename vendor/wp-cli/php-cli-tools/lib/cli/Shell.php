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

/**
 * The `Shell` class is a utility class for shell related tasks such as
 * information on width.
 */
class Shell {

	/**
	 * Returns the number of columns the current shell has for display.
	 *
	 * @return int  The number of columns.
	 * @todo Test on more systems.
	 */
	static public function columns() {
		static $columns;

		if ( null === $columns ) {
			if (self::is_windows() ) {
				$output = array();
				exec('mode CON', $output);
				foreach ($output as $line) {
					if (preg_match('/Columns:( )*([0-9]+)/', $line, $matches)) {
						$columns = (int)$matches[2];
						break;
					}
				}
			} else if (!preg_match('/(^|,)(\s*)?exec(\s*)?(,|$)/', ini_get('disable_functions'))) {
				$columns = (int) exec('/usr/bin/env tput cols');
			}

			if ( !$columns ) {
				$columns = 80; // default width of cmd window on Windows OS
			}
		}

		return $columns;
	}

	/**
	 * Checks whether the output of the current script is a TTY or a pipe / redirect
	 *
	 * Returns true if STDOUT output is being redirected to a pipe or a file; false is
	 * output is being sent directly to the terminal.
	 *
	 * If an env variable SHELL_PIPE exists, returned result depends it's
	 * value. Strings like 1, 0, yes, no, that validate to booleans are accepted.
	 *
	 * To enable ASCII formatting even when shell is piped, use the
	 * ENV variable SHELL_PIPE=0
	 *
	 * @return bool
	 */
	static public function isPiped() {
		$shellPipe = getenv('SHELL_PIPE');

		if ($shellPipe !== false) {
			return filter_var($shellPipe, FILTER_VALIDATE_BOOLEAN);
		} else {
			return (function_exists('posix_isatty') && !posix_isatty(STDOUT));
		}
	}

	/**
	 * Uses `stty` to hide input/output completely.
	 * @param boolean $hidden Will hide/show the next data. Defaults to true.
	 */
	static public function hide($hidden = true) {
		system( 'stty ' . ( $hidden? '-echo' : 'echo' ) );
	}

	/**
	 * Is this shell in Windows?
	 *
	 * @return bool
	 */
	static private function is_windows() {
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

}

?>
