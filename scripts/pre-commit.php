#!/usr/bin/env php
<?php

$filename = basename(__FILE__);
$install_dir = realpath(__DIR__.'/..');
chdir($install_dir);

$short_opts = 'lsuph';
$long_opts = array(
    'lint',
    'style',
    'unit',
    'passthru',
    'help',
);
$parameters = array(
    'p',
    'passthru',
);

$options = getopt($short_opts, $long_opts);

if (check_opt($options, 'h', 'help')) {
    echo "LibreNMS Code Tests Script
Running $filename without options runs all checks.
  -l, --lint  Run php lint checks to test for valid syntax.
  -s, --style Run phpcs check to check for PSR-2 compliance.
  -u, --unit  Run phpunit tests.
  -h, --help  Show this help text.\n";
    exit();
}

// set up some variables
$passthru = check_opt($options, 'p', 'passthru');
$commands = array_diff($options, $parameters);
$all = empty($commands);
$ret = 0;


// run tests
if ($all || check_opt($commands, 'l', 'lint')) {
    $ret += check_lint();
}

if ($all || check_opt($commands, 's', 'style')) {
    $ret += check_style($passthru);
}

if ($all || check_opt($commands, 'u', 'unit')) {
    $ret += check_unit($passthru);
}


// output Tests ok, if no arguments passed
if ($all && $ret === 0) {
    echo "\033[32mTests ok, submit away :)\033[0m \n";
}
exit($ret); //return the combined/single return value of tests


/**
 *  Check if the given options array contains any of the $opts specified
 *
 * @param array $options the array from getopt()
 * @param string $opts,... options to check for
 * @return bool If one of the specified options is set
 */
function check_opt($options)
{
    $args = func_get_args();
    array_shift($args);

    $intersect = array_intersect(array_keys($options), $args);
    return !empty($intersect);
}


/**
 * Runs php -l and tests for any syntax errors
 *
 * @return int the return value from running php -l (0 = success)
 */
function check_lint()
{
    echo "Running lint check... \n";

    if (version_compare(PHP_VERSION, '5.6') >= 0) {
        $lint_exclude = 'vendor';
    } else {
        $lint_exclude = 'vendor|lib/influxdb-php';
    }
    $lint_cmd = 'find . -regextype posix-extended -regex "\./(';
    $lint_cmd .= $lint_exclude;
    $lint_cmd .= ')" -prune -o -name "*.php" -print0 | xargs -0 -n1 -P8 php -l ';
    $lint_cmd .= '| grep -v "^No syntax errors detected"; test $? -eq 1';

    exec($lint_cmd, $lint_output, $lint_ret);

    if ($lint_ret > 0) {
        print(implode(PHP_EOL, $lint_output) . PHP_EOL);
    } else {
        echo "success\n";
    }

    return $lint_ret;
}

/**
 * Runs phpcs --standard=PSR2 against the code base
 *
 * @param bool $passthru display the output as comes in
 * @return int the return value from phpcs (0 = success)
 */
function check_style($passthru = false)
{
    echo 'Checking PSR-2 style...'.($passthru ? "\n" : ' ');


    $cs_exclude = '--ignore=html/lib/* --ignore=html/plugins/';
    $cs_cmd = "./vendor/bin/phpcs -n -p --colors --extensions=php --standard=PSR2 $cs_exclude html includes";

    $cs_output = '';
    if ($passthru) {
        passthru($cs_cmd, $cs_ret);
    } else {
        exec($cs_cmd, $cs_output, $cs_ret);
    }

    if (!$passthru) {
        if ($cs_ret > 0) {
            echo "failed\n";
            print(implode(PHP_EOL, $cs_output) . PHP_EOL);
        } else {
            echo "success\n";
        }
    }

    return $cs_ret;
}

/**
 * Runs phpunit
 *
 * @param bool $passthru display the output as comes in
 * @return int the return value from phpunit (0 = success)
 */
function check_unit($passthru = false)
{
    echo 'Running unit tests...'.($passthru ? "\n" : ' ');
    $phpunit_cmd = './vendor/bin/phpunit --colors=always';

    $phpunit_output = '';
    if ($passthru) {
        passthru($phpunit_cmd, $phpunit_ret);
    } else {
        exec($phpunit_cmd, $phpunit_output, $phpunit_ret);
    }

    if (!$passthru) {
        if ($phpunit_ret > 0) {
            echo "failed\n";
            print(implode(PHP_EOL, $phpunit_output) . PHP_EOL);
        } else {
            echo "success\n";
        }
    }

    return $phpunit_ret;
}
