<?php

require dirname( dirname( __FILE__ ) ) . '/lib/cli/cli.php';

function cli_autoload( $className ) {
	$className = ltrim($className, '\\');
	$fileName  = '';
	$namespace = '';
	if ($lastNsPos = strrpos($className, '\\')) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}
	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

	if ( 'cli' !== substr( $fileName, 0, 3 ) ) {
		return;
	}

	require dirname( dirname( __FILE__ ) ) . '/lib/' . $fileName;
}

spl_autoload_register( 'cli_autoload' );