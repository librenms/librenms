<?php

class MIBUpI18N {

	const DEFAULT_LOCALE = 'en_US';
	const GETTEXT_DOMAIN = 'mibuploader';

	public static function setup() {
		self::setLocale(self::getLocale());
		bindtextdomain(self::GETTEXT_DOMAIN, self::localesDirectory());
		textdomain(self::GETTEXT_DOMAIN);
	}

	public static function localesDirectory() {
		return dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'locales';
	}

	public static function getLocale() {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$sUALang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			$aUALangs = explode(',', $sUALang);

			foreach($aUALangs as $sUALangTag) {
				$aTags = explode(';', $sUALangTag);

				foreach($aTags as $sTag) {
					$sLocale = str_replace('-', '_', $sTag);

					if (self::localeExists($sLocale)) {
						return $sLocale;
					}
				}
			}
		}

		return self::DEFAULT_LOCALE;
	}

	public static function setLocale($sLocale) {
		putenv('LC_ALL=' . $sLocale);
		setlocale(LC_ALL, $sLocale);
	}

	public static function localeExists($sLocale) {
		if (is_dir(self::localesDirectory() . DIRECTORY_SEPARATOR . $sLocale)) {
			return true;
		}
		return false;
	}

}