#!/usr/bin/env php
<?php

$filename = basename(__FILE__);
$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$short_opts = 'lsuphc';
$long_opts = array(
    'lint',
    'style',
    'unit',
    'passthru',
    'commands',
    'help',
);
$options = getopt($short_opts, $long_opts);

if (check_opt($options, 'h', 'help')) {
    echo "LibreNMS Code Tests Script
Running $filename without options runs all checks.
  -l, --lint     Run php lint checks to test for valid syntax.
  -s, --style    Run phpcs check to check for PSR-2 compliance.
  -u, --unit     Run phpunit tests.
  -p, --passthru Display output from checks as it comes
  -c, --commands Print commands only, no checks
  -h, --help     Show this help text.\n";
    exit();
}

// set up some variables
$parameters = array(
    'p'        => false,
    'c'        => false,
    'passthru' => false,
    'commands' => false,
);
$passthru = check_opt($options, 'p', 'passthru');
$command_only = check_opt($options, 'c', 'commands');
$commands = array_diff_assoc($options, $parameters);
$all = empty($commands);
$ret = 0;

// run tests
if (($all || check_opt($commands, 'l', 'lint')) && !getenv('SKIP_LINT_CHECK')) {
    $ret += check_lint($passthru, $command_only);
}

if (($all || check_opt($commands, 's', 'style')) && !getenv('SKIP_STYLE_CHECK')) {
    $ret += check_style($passthru, $command_only);
}

if (($all || check_opt($commands, 'u', 'unit')) && !getenv('SKIP_UNIT_CHECK')) {
    $ret += check_unit($passthru, $command_only);
}


// output Tests ok, if no arguments passed
if ($all && $ret === 0) {
    echo "\033[32mTests ok, submit away :)\033[0m \n";
}
exit($ret); //return the combined/single return value of tests


/**
 * Runs php -l and tests for any syntax errors
 *
 * @param bool $passthru display the output as comes in
 * @param bool $command_only only display the intended command, no checks
 * @return int the return value from running php -l (0 = success)
 */
function check_lint($passthru = false, $command_only = false)
{
    // matches a substring of the relative path, leading / is treated as absolute path
    $lint_excludes = array('vendor/');
    if (defined('HHVM_VERSION') || version_compare(PHP_VERSION, '5.6', '<')) {
        $lint_excludes[] = 'lib/influxdb-php/';
    }

    $lint_exclude = build_excludes('--exclude ', $lint_excludes);
    $lint_cmd = "./vendor/bin/parallel-lint $lint_exclude ./";

    if ($command_only) {
        echo $lint_cmd . PHP_EOL;
        return 250;
    }

    echo 'Running lint check... ';

    if ($passthru) {
        echo PHP_EOL;
        passthru($lint_cmd, $lint_ret);
    } else {
        exec($lint_cmd, $lint_output, $lint_ret);

        if ($lint_ret > 0) {
            print(implode(PHP_EOL, $lint_output) . PHP_EOL);
        } else {
            echo "success\n";
        }
    }

    return $lint_ret;
}

/**
 * Runs phpcs --standard=PSR2 against the code base
 *
 * @param bool $passthru display the output as comes in
 * @param bool $command_only only display the intended command, no checks
 * @return int the return value from phpcs (0 = success)
 */
function check_style($passthru = false, $command_only = false)
{
    // matches a substring of the full path
    $cs_excludes = array(
        '/vendor/',
        '/lib/',
        '/html/plugins/',
    );

    $cs_exclude = build_excludes('--ignore=', $cs_excludes);
    $cs_cmd = "./vendor/bin/phpcs -n -p --colors --extensions=php --standard=PSR2 $cs_exclude ./html ./includes";

    if ($command_only) {
        echo $cs_cmd . PHP_EOL;
        return 250;
    }

    echo 'Running style check... ';

    if ($passthru) {
        echo PHP_EOL;
        passthru($cs_cmd, $cs_ret);
    } else {
        exec($cs_cmd, $cs_output, $cs_ret);

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
 * @param bool $command_only only display the intended command, no checks
 * @return int the return value from phpunit (0 = success)
 */
function check_unit($passthru = false, $command_only = false)
{
    $phpunit_cmd = './vendor/bin/phpunit --colors=always';

    if ($command_only) {
        echo $phpunit_cmd . PHP_EOL;
        return 250;
    }

    echo 'Running unit tests... ';
    if ($passthru) {
        echo PHP_EOL;
        passthru($phpunit_cmd, $phpunit_ret);
    } else {
        exec($phpunit_cmd, $phpunit_output, $phpunit_ret);

        if ($phpunit_ret > 0) {
            echo "failed\n";
            print(implode(PHP_EOL, $phpunit_output) . PHP_EOL);
        } else {
            echo "success\n";
        }
    }

    return $phpunit_ret;
}

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
 * Build a list of exclude arguments from an array
 *
 * @param string $exclude_string such as "--exclude"
 * @param array $excludes array of directories to exclude
 * @return string resulting string
 */
function build_excludes($exclude_string, $excludes)
{
    $result = '';
    foreach ($excludes as $exclude) {
        $result .= $exclude_string . $exclude . ' ';
    }

    return $result;
}
