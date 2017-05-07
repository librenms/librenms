<?php

require_once 'common.php';

$menu = array(
	'output' => 'Output Examples',
	'notify' => 'cli\Notify Examples',
	'progress' => 'cli\Progress Examples',
	'table' => 'cli\Table Example',
	'colors' => 'cli\Colors example',
	'quit' => 'Quit',
);

while (true) {
	$choice = \cli\menu($menu, null, 'Choose an example');
	\cli\line();

	if ($choice == 'quit') {
		break;
	}

	include "${choice}.php";
	\cli\line();
}
