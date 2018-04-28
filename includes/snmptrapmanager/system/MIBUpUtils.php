<?php

class MIBUpUtils {

	/**
	 * Concatenates a list of path elements with DIRECTORY_SEPARATOR.
	 *
	 * "bfp" stands for "build_file_path".
	 *
	 * @param Array $aPaths list of path elements to be concatenated
	 * @return string usable path
	 */
	public static function bfp($aPaths) {
		return join(DIRECTORY_SEPARATOR, $aPaths);
	}

	/**
	 * call var_dump() and retreive it's result instead of
	 * letting it echoing.
	 *
	 * @param mixed $mVar
	 * @return string
	 */
	public static function vardump($mVar) {
		ob_start();
		var_dump($mVar);
		return ob_get_clean();
	}

	public static function shellExec($sCmd, $sWorkDir = '/tmp/') {
		//logfile($sCmd);
		$descriptorspec = array(
			0 => array("pipe", "r"),
			1 => array("pipe", "w"),
			2 => array("pipe", "w")
		);

		$process = proc_open($sCmd, $descriptorspec, $pipes, $sWorkDir);

		if (!is_resource($process)) {
			throw new MIBUpException('Cannot run ' . $sCmd);
		}

		$sStdout = '';
		$sStderr = '';

		while (!feof($pipes[1]) && !feof($pipes[2])) {
			$select_read = Array($pipes[1], $pipes[2]);
			$select_write = null;
			$exceptions = null;

			stream_select($select_read, $select_write, $exceptions, 0, null);

			if (!empty($select_read)) {
				if (isset($select_read[0])) {
					$sStdout .= stream_get_contents($select_read[0]);
				} elseif(isset($select_read[1])) {
					$sStderr .= stream_get_contents($select_read[1]);
				}
			}
		}

		$return_value = proc_close($process);

		return Array($return_value, $sStdout, $sStderr);
	}


	/**
	 * Simple access to MIBUploader's configuration.
	 * @param string $sKey
	 * @param mixed $mDefault default value if $sKey isn't set
	 * @throws MIBUpException if $mDefault is null and $sKey unset, raise exception
	 * @return mixed
	 */
	public static function getConf($sKey, $mDefault = null) {
		global $config;

		if (isset($config['plugins']['mibuploader'][$sKey])) {
			return $config['plugins']['mibuploader'][$sKey];
		}

		if ($mDefault === null) {
			throw new MIBUpException('Missing mandatory MIBUploader configuration: ' . $sKey);
		}

		return $mDefault;
	}

}
