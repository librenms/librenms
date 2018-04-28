<?php

class MIBUpAutoload {

	private static $bRegistered = false;

	private static function mibuploader_reqin($sSubPath, $class) {
		$sFile = join(DIRECTORY_SEPARATOR, Array(
			dirname(__FILE__),
			'..',
			$sSubPath,
			$class . '.php'));

		if(is_file($sFile)) {
			require_once $sFile;
			return true;
		}

		return false;
	}

	// Ugly autoloader.
	public static function mibuploader_autoload($class) {
		if(self::mibuploader_reqin('system', $class)) { return; }
		if(self::mibuploader_reqin('controllers', $class)) { return; }
		if(self::mibuploader_reqin('models', $class)) { return; }
	}

	public static function register() {
		if (!self::$bRegistered) {
			spl_autoload_register(
				Array(get_class(), 'mibuploader_autoload')
			);
			self::$bRegistered = true;
		}
	}
}