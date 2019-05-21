#!/usr/bin/env php
<?php

$filename = basename(__FILE__);
$install_dir = realpath(__DIR__ . '/..');
chdir($install_dir);

require $install_dir . '/vendor/autoload.php';

if (getenv('FILES')) {
    $changed_files = rtrim(getenv('FILES'));
} else {
    $changed_files = exec("git diff --diff-filter=d --name-only master | tr '\n' ' '|sed 's/,*$//g'");
}

$changed_files = $changed_files ? explode(' ', $changed_files) : [];

$map = [
    'docs'   => 0,
    'python' => 0,
    'bash'   => 0,
    'php'    => 0,
    'os-php' => 0,
    'os'     => [],
];

foreach ($changed_files as $file) {
    if (starts_with($file, 'doc/')) {
        $map['docs']++;
    }
    if (ends_with($file, '.py')) {
        $map['python']++;
    }
    if (ends_with($file, '.sh')) {
        $map['bash']++;
    }

    if ($file == 'composer.lock') {
        $map['php']++; // cause full tests to run
    }

    // check if os owned file or generic php file
    if (!empty($os_name = os_from_file($file))) {
        $map['os'][] = $os_name;
        if (ends_with($file, '.php')) {
            $map['os-php']++;
        }
    } elseif (ends_with($file, '.php')) {
        $map['php']++;
    }
}

$map['os'] = array_unique($map['os']);

$short_opts = 'lsufqcho:m:';
$long_opts = array(
    'lint',
    'style',
    'unit',
    'os:',
    'module:',
    'fail-fast',
    'quiet',
    'snmpsim',
    'db',
    'commands',
    'help',
);
$options = getopt($short_opts, $long_opts);

if (check_opt($options, 'h', 'help')) {
    echo "LibreNMS Code Tests Script
Running $filename without options runs all checks.
  -l, --lint      Run php lint checks to test for valid syntax
  -s, --style     Run phpcs check to check for PSR-2 compliance
  -u, --unit      Run phpunit tests
  -o, --os        Specific OS to run tests on. Implies --unit, --db, --snmpsim
  -m, --module    Specific Module to run tests on. Implies --unit, --db, --snmpsim
  -f, --fail-fast Quit when any failure is encountered
  -q, --quiet     Hide output unless there is an error
      --db        Run unit tests that require a database
      --snmpsim   Use snmpsim for unit tests
  -c, --commands  Print commands only, no checks
  -h, --help      Show this help text.\n";
    exit();
}

// set up some variables
$passthru = !check_opt($options, 'q', 'quiet');
$command_only = check_opt($options, 'c', 'commands');
$fail_fast = check_opt($options, 'f', 'fail-fast');
$return = 0;
$completed_tests = array(
    'lint' => false,
    'style' => false,
    'unit' => false,
);

if ($os = check_opt($options, 'os', 'o')) {
    // enable unit tests, snmpsim, and db
    $options['u'] = false;
    $options['snmpsim'] = false;
    $options['db'] = false;
}

if ($module = check_opt($options, 'm', 'module')) {
    putenv("TEST_MODULES=$module");
    // enable unit tests, snmpsim, and db
    $options['u'] = false;
    $options['snmpsim'] = false;
    $options['db'] = false;
}

$all = !check_opt($options, 'l', 'lint', 's', 'style', 'u', 'unit');
if ($all) {
    // no test specified, run all tests in this order
    $options += array('u' => false, 's' => false, 'l' => false);
}

if (check_opt($options, 'snmpsim')) {
    putenv('SNMPSIM=1');
}

if (check_opt($options, 'db')) {
    putenv('DBTEST=1');
}

// No php files, skip the php checks.
if (!empty($changed_files) && $map['php'] === 0 && $map['os-php'] === 0) {
    putenv('SKIP_LINT_CHECK=1');
    putenv('SKIP_STYLE_CHECK=1');
}

// If we have no php files and no OS' found then also skip unit checks.
if (!empty($changed_files) && $map['php'] === 0 && empty($map['os']) && !$os) {
    putenv('SKIP_UNIT_CHECK=1');
}

// If we have more than 4 (arbitrary number) of OS' then blank them out
// Unit tests may take longer to run in a loop so fall back to all.
if (count($map['os']) > 4) {
    unset($map['os']);
}

// run tests in the order they were specified

foreach (array_keys($options) as $opt) {
    $ret = 0;
    if ($opt == 'l' || $opt == 'lint') {
        $ret = run_check('lint', $passthru, $command_only);
    } elseif ($opt == 's' || $opt == 'style') {
        $ret = run_check('style', $passthru, $command_only);
    } elseif ($opt == 'u' || $opt == 'unit') {
        if (!empty($map['os']) && $map['php'] === 0) {
            $os = $map['os'];
        }

        if (!empty($os)) {
            echo 'Only checking os: ' . implode(', ', (array)$os) . PHP_EOL;
        }

        $ret = run_check('unit', $passthru, $command_only, compact('fail_fast', 'os', 'module'));
    }

    if ($fail_fast && $ret !== 0 && $ret !== 250) {
        exit($ret);
    } else {
        $return += $ret;
    }
}

// output Tests ok, if no arguments passed
if ($all && $return === 0) {
    echo "\033[32mTests ok, submit away :)\033[0m \n";
}
exit($return); //return the combined/single return value of tests

function os_from_file($file)
{
    if (starts_with($file, 'includes/definitions/')) {
        return basename($file, '.yaml');
    } elseif (starts_with($file, ['includes/polling', 'includes/discovery'])) {
        return os_from_php($file);
    } elseif (starts_with($file, 'LibreNMS/OS/')) {
        if (preg_match('#LibreNMS/OS/[^/]+.php#', $file)) {
            // convert class name to os name
            preg_match_all("/[A-Z][a-z]*/", basename($file, '.php'), $segments);
            $osname = implode('-', array_map('strtolower', $segments[0]));
            $os = os_from_php($osname);
            if ($os) {
                return $os;
            }
            return os_from_php(str_replace('-', '_', $osname));
        }
    } elseif (starts_with($file, ['tests/snmpsim/', 'tests/data/'])) {
        list($os,) = explode('_', basename(basename($file, '.json'), '.snmprec'), 2);
        return $os;
    }

    return null;
}

/**
 * Extract os name from path and validate it exists.
 *
 * @param $php_file
 * @return null|string
 */
function os_from_php($php_file)
{
    $os = basename($php_file, '.inc.php');

    if (file_exists("includes/definitions/$os.yaml")) {
        return $os;
    }

    return null;
}


/**
 * Run the specified check and return the return value.
 * Make sure it isn't skipped by SKIP_TYPE_CHECK env variable and hasn't been run already
 *
 * @param string $type type of check lint, style, or unit
 * @param bool $passthru display the output as comes in
 * @param bool $command_only only display the intended command, no checks
 * @param array $options command specific options
 * @return int the return value from the check (0 = success)
 */
function run_check($type, $passthru, $command_only, $options = array())
{
    global $completed_tests;
    if (getenv('SKIP_' . strtoupper($type) . '_CHECK') || $completed_tests[$type]) {
        echo ucfirst($type) . " check skipped.\n";
        return 0;
    }

    $function = 'check_' . $type;
    if (function_exists($function)) {
        $completed_tests[$type] = true;
        return $function($passthru, $command_only, $options);
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

    $cs_cmd = "$phpcs_bin -n -p --colors --extensions=php --standard=misc/phpcs_librenms.xml ./";

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
 * @param array $options Supported: fail_fast, os, module
 * @return int the return value from phpunit (0 = success)
 */
function check_unit($passthru = false, $command_only = false, $options = array())
{
    echo 'Running unit tests... ';

    $phpunit_bin = check_exec('phpunit');

    $phpunit_cmd = "$phpunit_bin --colors=always";

    if ($options['fail_fast']) {
        $phpunit_cmd .= ' --stop-on-error --stop-on-failure';
    }

    if ($options['os']) {
        $filter = implode('.*|', (array)$options['os']);
        // include tests that don't have data providers and only data sets that match
        $phpunit_cmd .= " --group os --filter '/::test[A-Za-z]+$|::test[A-Za-z]+ with data set \"$filter.*\"$/'";
    }

    if ($options['module']) {
        $phpunit_cmd .= ' tests/OSModulesTest.php';
    }

    if ($command_only) {
        echo $phpunit_cmd . PHP_EOL;
        return 250;
    }

    if ($passthru) {
        echo PHP_EOL;
        passthru($phpunit_cmd, $phpunit_ret);
    } else {
        exec($phpunit_cmd, $phpunit_output, $phpunit_ret);

        if ($phpunit_ret > 0) {
            echo "failed\n";
            echo implode(PHP_EOL, $phpunit_output) . PHP_EOL;
            echo 'snmpsimd: output at /tmp/snmpsimd.log';
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
 * @param string ...$opts options to check for
 * @return bool If one of the specified options is set
 */
function check_opt($options, ...$opts)
{
    foreach ($opts as $option) {
        if (isset($options[$option])) {
            if ($options[$option] === false) {
                // no data, return that option is enabled
                return true;
            }
            return $options[$option];
        }
    }

    return false;
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
 * Check for an executable and return the path to it
 * If it does not exist, run composer update.
 * If composer isn't installed, print error and exit.
 *
 * @param string $exec the name of the executable to check
 * @return string path to the executable
 */
function check_exec($exec)
{
    $path = "vendor/bin/$exec";

    if (is_executable($path)) {
        return $path;
    }

    echo "Running composer install to install developer dependencies.\n";
    passthru("scripts/composer_wrapper.php install");

    if (is_executable($path)) {
        return $path;
    }

    echo "\nRunning installing deps with composer failed.\n You should try running './scripts/composer_wrapper.php install' by hand\n";
    echo "You can find more info at http://docs.librenms.org/Developing/Validating-Code/\n";
    exit(1);
}
