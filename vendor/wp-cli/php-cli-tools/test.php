<?php

error_reporting(-1);
require_once __DIR__ . '/vendor/autoload.php';


$args = new cli\Arguments(array(
	'flags' => array(
		'verbose' => array(
			'description' => 'Turn on verbose mode',
			'aliases'     => array('v')
		),
		'c' => array(
			'description' => 'A counter to test stackable',
			'stackable'   => true
		)
	),
	'options' => array(
		'user' => array(
			'description' => 'Username for authentication',
			'aliases'     => array('u')
		)
	),
	'strict' => true
));

try {
    $args->parse();
} catch (cli\InvalidArguments $e) {
    echo $e->getMessage() . "\n\n";
}

print_r($args->getArguments());
