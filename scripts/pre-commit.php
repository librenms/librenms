#!/usr/bin/env php
<?php

$filename = basename(__FILE__);
$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

$short_opts = 'lsupch';
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
$passthru = check_opt($options, 'p', 'passthru');
$command_only = check_opt($options, 'c', 'commands');
$ret = 0;
$completed_tests = array(
    'lint' => false,
    'style' => false,
    'unit' => false,
);

$all = !check_opt($options, 'l', 'lint', 's', 'style', 'u', 'unit');
if ($all) {
    // no test specified, run all tests in this order
    $options += array('u' => false, 's' => false, 'l' => false);
}

// run tests in the order they were specified
foreach (array_keys($options) as $opt) {
    if ($opt == 'l' || $opt == 'lint') {
        $ret += run_check('lint', $passthru, $command_only);
    } elseif ($opt == 's' || $opt == 'style') {
        $ret += run_check('style', $passthru, $command_only);
    } elseif ($opt == 'u' || $opt == 'unit') {
        $ret += run_check('unit', $passthru, $command_only);
    }
}

// output Tests ok, if no arguments passed
if ($all && $ret === 0) {
    echo "\033[32mTests ok, submit away :)\033[0m \n";
}
exit($ret); //return the combined/single return value of tests


/**
 * Run the specified check and return the return value.
 * Make sure it isn't skipped by SKIP_TYPE_CHECK env variable and hasn't been run already
 *
 * @param string $type type of check lint, style, or unit
 * @param bool $passthru display the output as comes in
 * @param bool $command_only only display the intended command, no checks
 * @return int the return value from the check (0 = success)
 */
function run_check($type, $passthru, $command_only)
{
    global $completed_tests;
    if (getenv('SKIP_' . strtoupper($type) . '_CHECK') || $completed_tests[$type]) {
        return 0;
    }

    $function = 'check_' . $type;
    if (function_exists($function)) {
        $completed_tests[$type] = true;
        return $function($passthru, $command_only);
    }

    return 1;
}

/**
 * Runs php -l and tests for any syntax errors
 *
 * @param bool $passthru display the output as comes in
 * @param bool $command_only only display the intended command, no checks
 * @return int the return value from running php -l (0 = success)
 */
function check_lint($passthru = false, $command_only = false)
{
    $parallel_lint_bin = check_exec('parallel-lint');

    // matches a substring of the relative path, leading / is treated as absolute path
    $lint_excludes = array('vendor/');
    if (defined('HHVM_VERSION') || version_compare(PHP_VERSION, '5.6', '<')) {
        $lint_excludes[] = 'lib/influxdb-php/';
    }

    $lint_exclude = build_excludes('--exclude ', $lint_excludes);
    $lint_cmd = "$parallel_lint_bin $lint_exclude ./";

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
    $phpcs_bin = check_exec('phpcs');

    // matches a substring of the full path
    $cs_excludes = array(
        '/vendor/',
        '/lib/',
        '/html/plugins/',
        '/config.php',
    );

    $cs_exclude = build_excludes('--ignore=', $cs_excludes);
    $cs_cmd = "$phpcs_bin -n -p --colors --extensions=php --standard=PSR2 $cs_exclude ./";

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
    $phpunit_bin = check_exec('phpunit');
    $phpunit_cmd = "$phpunit_bin --colors=always";

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

/**
 * Find an executable
 *
 * @param string|array $execs executable names to find
 * @return string the path to the executable, or '' if it is not found
 * @throws Exception Could not find the Executable
 */
function find_exec($execs)
{
    foreach ((array)$execs as $exec) {
        // check vendor bin first
        $vendor_bin_dir = './vendor/bin/';
        if (is_executable($vendor_bin_dir . $exec)) {
            return $vendor_bin_dir . $exec;
        }

        // check path
        $path_exec = shell_exec("which $exec 2> /dev/null");
        if (!empty($path_exec)) {
            return trim($path_exec);
        }

        // check the cwd
        if (is_executable('./' . $exec)) {
            return './' . $exec;
        }
    }
    throw new Exception('Executable not found');
}

/**
 * Check for an executable and return the path to it
 * If it does not exist, run composer update.
 * If composer isn't installed, print error and exit.
 *
 * @param string $exec the name of the executable to check
 * @return string path to the executable
 */
function check_exec($exec)
{
    try {
        return find_exec($exec);
    } catch (Exception $e) {
        try {
            $composer_bin = find_exec(array('composer', 'composer.phar'));
            shell_exec("$composer_bin update");
            return find_exec($exec);
        } catch (Exception $ce) {
            echo "\nCould not find $exec. Please install composer.\nYou can find more info at http://docs.librenms.org/Developing/Validating-Code/\n";
            exit(1);
        }
    }
}
