#!/usr/bin/env php
<?php

$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

require $install_dir . '/vendor/autoload.php';

$helper = new \LibreNMS\Util\CiHelper();
$return = $helper->run();

// output Tests ok, if no arguments passed
if ($helper->allChecksComplete() && $return === 0) {
    echo "\033[32mTests ok, submit away :)\033[0m \n";
}

if (!empty($helper->getChanged('docs'))) {
    exec('bash scripts/deploy-docs.sh');
}

exit($return); //return the combined/single return value of tests
