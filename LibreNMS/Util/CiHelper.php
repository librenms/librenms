<?php
/**
 * CiHelper.php
 *
 * Code for CI operation
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class CiHelper
{
    private $options;
    private $changedFiles;
    private $changed = [
        'docs' => [],
        'python' => [],
        'bash' => [],
        'php' => [],
        'os-php' => [],
        'os' => [],
        'resources' => [],
        'svg' => [],
    ];
    private $quiet = false;
    private $commandOnly = false;
    private $failFast = false;
    private $inCi = false;
    private $completedTests = [
        'lint' => false,
        'style' => false,
        'unit' => false,
        'dusk' => false,
    ];
    private $ciDefaults = [
        'quiet' => [
            'lint' => true,
            'style' => true,
            'unit' => false,
            'dusk' => false,
        ],
    ];
    private $fullChecks = false;
    private $modules;
    private $os;
    private $flags = [
        'style' => [
            'skip' => false,
        ],
        'lint' => [
            'skip' => false,
            'skip_php' => false,
            'skip_python' => false,
            'skip_bash' => false,
        ],
        'unit' => [
            'skip' => false,
            'os' => false,
            'docs' => false,
            'svg' => false,
            'modules' => false,
        ],
        'dusk' => [
            'skip' => false,
        ],
        'docs' => [
            'changed' => false,
        ]
    ];

    public function __construct()
    {
        $this->parseOptions();
        $this->detectChangedFiles();
        $this->parseChangedFiles();
        $this->checkEnv();
    }

    public function run()
    {
        // run tests in the order they were specified

        $return = 0;
        foreach (array_keys($this->options) as $opt) {
            $ret = 0;
            if ($opt == 'l' || $opt == 'lint') {
                $ret = $this->runCheck('lint');
            } elseif ($opt == 's' || $opt == 'style') {
                $ret = $this->runCheck('style');
            } elseif ($opt == 'u' || $opt == 'unit') {
                $ret = $this->runCheck('unit');
            } elseif ($opt == 'd' || $opt == 'dusk') {
                $ret = $this->runCheck('dusk');
            }

            if ($this->failFast && $ret !== 0 && $ret !== 250) {
                exit($ret);
            } else {
                $return += $ret;
            }
        }

        return $return;
    }

    /**
     * Confirm that all possible checks have been completed
     *
     * @return bool
     */
    public function allChecksComplete()
    {
        return array_reduce($this->completedTests, function ($result, $check) {
            return $result && $check;
        }, false);
    }

    /**
     * Fetch flags
     * if no parameters are specified, all are fetch or all for type if only type is specified
     * @param string $type
     * @return bool|bool[]|bool[][]
     */
    public function getFlags($type = null, $item = null)
    {
        if (isset($this->flags[$type][$item])) {
            return $this->flags[$type][$item];
        }

        return isset($this->flags[$type]) ? $this->flags[$type] : $this->flags;
    }

    /**
     * Runs phpunit
     *
     * @return int the return value from phpunit (0 = success)
     */
    public function checkUnit()
    {
        $phpunit_bin = $this->checkPhpExec('phpunit');

        $phpunit_cmd = "$phpunit_bin --colors=always";

        if ($this->failFast) {
            $phpunit_cmd .= ' --stop-on-error --stop-on-failure';
        }

        // exclusive tests
        if ($this->flags['unit']['os']) {
            echo 'Only checking os: ' . implode(', ', $this->changed['os']) . PHP_EOL;
            $filter = implode('.*|', $this->os ?: $this->changed['os']);
            // include tests that don't have data providers and only data sets that match
            $phpunit_cmd .= " --group os --filter '/::test[A-Za-z]+$|::test[A-Za-z]+ with data set \"$filter.*\"$/'";
        } elseif ($this->flags['unit']['docs']) {
            $phpunit_cmd .= " --group docs";
        } elseif ($this->flags['unit']['svg']) {
            $phpunit_cmd .= ' tests/SVGTest.php';
        } elseif ($this->flags['unit']['modules']) {
            $phpunit_cmd .= ' tests/OSModulesTest.php';
        }

        return $this->execute('unit', $phpunit_cmd);
    }

    /**
     * Runs phpcs --standard=PSR2 against the code base
     *
     * @return int the return value from phpcs (0 = success)
     */
    public function checkStyle()
    {
        $phpcs_bin = $this->checkPhpExec('phpcs');

        $files = ($this->fullChecks) ? './' : implode(' ', $this->changed['php']);

        $cs_cmd = "$phpcs_bin -n -p --colors --extensions=php --standard=misc/phpcs_librenms.xml $files";

        return $this->execute('style', $cs_cmd);
    }

    public function checkDusk()
    {
        exec('php artisan config:clear'); // make sure config is not cached
        exec('php artisan dusk:update --detect');  // make sure driver is correct

        putenv('APP_ENV=testing');

        // check if web server is running
        $server = new Process("php -S 127.0.0.1:8000 ../server.php", 'html', ['APP_ENV' => 'dusk.testing']);
        $server->setTimeout(3600);
        $server->setIdleTimeout(3600);
        $server->start();
        $server->waitUntil(function ($type, $output) {
            return strpos($output, 'Development Server (http://127.0.0.1:8000) started') !== false;
        });
        if ($server->isRunning()) {
            echo "Started server http://127.0.0.1:8000\n";
        }

        $dusk_cmd = "php artisan dusk";

        if ($this->failFast) {
            $dusk_cmd .= ' --stop-on-error --stop-on-failure';
        }

        return $this->execute('dusk', $dusk_cmd);
    }

    /**
     * Runs php -l and tests for any syntax errors
     *
     * @return int the return value from running php -l (0 = success)
     */
    public function checkLint()
    {
        $return = 0;
        if (!$this->flags['lint']['skip_php']) {
            $parallel_lint_bin = $this->checkPhpExec('parallel-lint');

            // matches a substring of the relative path, leading / is treated as absolute path
            $lint_excludes = ['vendor/'];
            $lint_exclude = $this->buildPhpLintExcludes('--exclude ', $lint_excludes);

            $files = $this->fullChecks ? './' : implode(' ', $this->changed['php']);

            $php_lint_cmd = "$parallel_lint_bin $lint_exclude $files";

            $return += $this->execute('PHP lint', $php_lint_cmd);
        }

        if (!$this->flags['lint']['skip_python']) {
            $pylint_bin = $this->checkPythonExec('pylint');

            $files = $this->fullChecks
                ? str_replace(PHP_EOL, ' ', rtrim(shell_exec("find . -name '*.py' -not -path './vendor/*' -not -path './tests/*'")))
                : implode(' ', $this->changed['python']);

            $py_lint_cmd = "$pylint_bin -E -j 0 $files";
            $return += $this->execute('Python lint', $py_lint_cmd);
        }

        if (!$this->flags['lint']['skip_bash']) {
            $files = $this->fullChecks
                ? explode(PHP_EOL, rtrim(shell_exec("find . -name '*.sh' -not -path './node_modules/*' -not -path './vendor/*'")))
                : $this->changed['bash'];

            $bash_cmd = implode(' && ', array_map(function ($file) {
                return "bash -n $file";
            }, $files));
            $return += $this->execute('Bash lint', $bash_cmd);
        }

        return $return;
    }

    /**
     * Run the specified check and return the return value.
     * Make sure it isn't skipped by SKIP_TYPE_CHECK env variable and hasn't been run already
     *
     * @param string $type type of check lint, style, or unit
     * @return int the return value from the check (0 = success)
     */
    private function runCheck($type)
    {
        if ($method = $this->canCheck($type)) {
            $this->completedTests[$type] = true;
            return $this->$method();
        }

        echo ucfirst($type) . " check skipped.\n";
        return 0;
    }

    /**
     * @param string $type
     * @return false|string the method name to run
     */
    private function canCheck($type)
    {
        if ($this->flags[$type]['skip'] || $this->completedTests[$type]) {
            return false;
        }

        $method = 'check' . ucfirst($type);
        if (method_exists($this, $method)) {
            return $method;
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $command
     * @return int
     */
    private function execute(string $name, string $command): int
    {
        if ($this->commandOnly) {
            echo $command . PHP_EOL;
            return 250;
        }

        echo "Running $name check... ";
        $space = strrpos($name, ' ');
        $type = substr($name, $space ? $space + 1 : 0);

        $quiet = $this->inCi ? $this->ciDefaults['quiet'][$type] : $this->quiet;
        if (!$quiet) {
            echo PHP_EOL;
            passthru($command, $return);
            return $return;
        }

        exec($command, $output, $return);

        if ($return > 0) {
            echo "failed\n";
            print(implode(PHP_EOL, $output) . PHP_EOL);
        } else {
            echo "success\n";
        }

        return $return;
    }

    /**
     * Extract os name from path and validate it exists.
     *
     * @param $php_file
     * @return null|string
     */
    private function osFromPhp($php_file)
    {
        $os = basename($php_file, '.inc.php');

        if (file_exists("includes/definitions/$os.yaml")) {
            return $os;
        }

        return null;
    }

    private function osFromFile($file)
    {
        if (Str::startsWith($file, 'includes/definitions/')) {
            return basename($file, '.yaml');
        } elseif (Str::startsWith($file, ['includes/polling', 'includes/discovery'])) {
            return $this->osFromPhp($file);
        } elseif (Str::startsWith($file, 'LibreNMS/OS/')) {
            if (preg_match('#LibreNMS/OS/[^/]+.php#', $file)) {
                return $this->osFromClass(basename($file, '.php'));
            }
        } elseif (Str::startsWith($file, ['tests/snmpsim/', 'tests/data/'])) {
            [$os,] = explode('_', basename(basename($file, '.json'), '.snmprec'), 2);
            return $os;
        }

        return null;
    }

    /**
     * convert class name to os name
     *
     * @param string $class
     * @return string|null
     */
    private function osFromClass($class)
    {
        preg_match_all("/[A-Z][a-z0-9]*/", $class, $segments);
        $osname = implode('-', array_map('strtolower', $segments[0]));
        $osname = preg_replace(
            ['/^zero-/', '/^one-/', '/^two-/', '/^three-/', '/^four-/', '/^five-/', '/^six-/', '/^seven-/', '/^eight-/', '/^nine-/',],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $osname);

        if ($os = $this->osFromPhp($osname)) {
            return $os;
        }
        return $this->osFromPhp(str_replace('-', '_', $osname));
    }

    private function detectChangedFiles()
    {
        $changed_files = getenv('FILES')
            ? rtrim(getenv('FILES'))
            : exec("git diff --diff-filter=d --name-only master | tr '\n' ' '|sed 's/,*$//g'");
        $this->changedFiles = $changed_files ? explode(' ', $changed_files) : [];

        $debug = getenv('CIHELPER_DEBUG');
        foreach ($this->changedFiles as $file) {
            if ($debug && $file == 'LibreNMS/Util/CiHelper.php') {
                continue;
            }
            if (Str::endsWith($file, '.php')) {
                $this->changed['php'][] = $file;
            } elseif (Str::startsWith($file, 'doc/') || $file == 'mkdocs.yml') {
                $this->changed['docs'][] = $file;
            } elseif (Str::endsWith($file, '.py')) {
                $this->changed['python'][] = $file;
            } elseif (Str::endsWith($file, '.sh')) {
                $this->changed['bash'][] = $file;
            } elseif (Str::endsWith($file, '.svg')) {
                $this->changed['svg'][] = $file;
            } elseif (Str::startsWith($file, 'resources/')) {
                $this->changed['resources'][] = $file;
            }

            // cause full tests to run
            if ($file == 'composer.lock' || $file == '.travis.yml') {
                $this->fullChecks = true;
            }

            // check if os owned file or generic php file
            if (!empty($os_name = $this->osFromFile($file))) {
                $this->changed['os'][] = $os_name;
                if (Str::endsWith($file, '.php')) {
                    $this->changed['os-php'][] = $file;
                }
            }
        }

        $this->changed['os'] = array_unique($this->changed['os']);

        // If we have more than 4 (arbitrary number) of OS' then blank them out
        // Unit tests may take longer to run in a loop so fall back to all.
        if (count($this->changed['os']) > 4) {
            $this->changed['os'] = [];
        }
    }

    private function parseOptions(): void
    {
        $short_opts = 'ldsufqcho:m:';
        $long_opts = [
            'ci',
            'commands',
            'db',
            'dusk',
            'fail-fast',
            'full',
            'help',
            'lint',
            'module:',
            'os:',
            'quiet',
            'snmpsim',
            'style',
            'unit',
        ];
        $this->options = getopt($short_opts, $long_opts);

        $filename = basename($_SERVER["SCRIPT_FILENAME"]);
        if ($this->checkOpt('h', 'help')) {
            echo "LibreNMS Code Tests Script
Running $filename without options runs all checks.
  -l, --lint      Run php lint checks to test for valid syntax
  -s, --style     Run phpcs check to check for PSR-2 compliance
  -u, --unit      Run phpunit tests
  -d, --dusk      Run Laravel Dusk tests
  -o, --os        Specific OS to run tests on. Implies --unit, --db, --snmpsim
  -m, --module    Specific Module to run tests on. Implies --unit, --db, --snmpsim
  -f, --fail-fast Quit when any failure is encountered
  -q, --quiet     Hide output unless there is an error
      --db        Run unit tests that require a database
      --snmpsim   Use snmpsim for unit tests
      --full      Run full checks ignoring changed file filtering
      --ci        Use preset config for running continuous integration
  -c, --commands  Print commands only, no checks
  -h, --help      Show this help text.\n";
            exit();
        }

        $this->quiet = $this->checkOpt('q', 'quiet');
        $this->commandOnly = $this->checkOpt('c', 'commands');
        $this->failFast = $this->checkOpt('f', 'fail-fast', 'ci');
        $this->inCi = $this->checkOpt('ci');
        $this->fullChecks = $this->checkOpt('full');

        if ($this->inCi) {
            putenv('CHROME_HEADLESS=1');
        }

        if ($os = $this->checkOpt('os', 'o')) {
            $this->os = explode(',', $os);
            // enable unit tests, snmpsim, and db
            $this->options['u'] = false;
            $this->options['snmpsim'] = false;
            $this->options['db'] = false;
        }

        if ($modules = $this->checkOpt('m', 'module')) {
            $this->modules = $modules;
            putenv("TEST_MODULES=$modules"); // set to pass to unit test
            // enable unit tests, snmpsim, and db
            $this->options['u'] = false;
            $this->options['snmpsim'] = false;
            $this->options['db'] = false;
        }

        if (!$this->checkOpt('l', 'lint', 's', 'style', 'u', 'unit', 'd', 'dusk')) {
            // no test specified, run all tests in this order
            $this->options += ['u' => false, 's' => false, 'l' => false, 'd' => false];
        }

        if ($this->checkOpt('snmpsim', 'ci')) {
            putenv('SNMPSIM=1');
        }

        if ($this->checkOpt('db', 'ci')) {
            putenv('DBTEST=1');
        }
    }

    private function checkEnv()
    {
        $this->flags['unit']['skip'] = getenv('SKIP_UNIT_CHECK') ? true : $this->flags['unit']['skip'];
        $this->flags['lint']['skip'] = getenv('SKIP_LINT_CHECK') ? true : $this->flags['lint']['skip'];
        $this->flags['dusk']['skip'] = getenv('SKIP_DUSK_CHECK') ? true : $this->flags['dusk']['skip'];
        $this->flags['style']['skip'] = getenv('SKIP_STYLE_CHECK') ? true : $this->flags['style']['skip'];
    }

    private function parseChangedFiles()
    {
        if (empty($this->changedFiles) || $this->fullChecks) {
            // nothing to do
            return;
        }

        $hasOs = !empty($this->changed['os']);
        $onlyOs = empty(array_diff($this->changed['php'], $this->changed['os-php']));
        $noPhp = !$hasOs && empty($this->changed['php']);

        $this->flags = [
            'style' => [
                'skip' => empty($this->changed['php'])
            ],
            'lint' => [
                'skip' => empty($this->changed['php']) && empty($this->changed['python']) && empty($this->changed['bash']),
                'skip_php' => empty($this->changed['php']),
                'skip_python' => empty($this->changed['python']),
                'skip_bash' => empty($this->changed['bash']),
            ],
            'unit' => [
                'skip' => !$hasOs && $noPhp && empty($this->changed['docs']) && empty($this->changed['svg']),
                'os' => !empty($this->os) || ($hasOs && $onlyOs),
                'docs' => !empty($this->changed['docs']) && $noPhp,
                'svg' => !empty($this->changed['svg']) && $noPhp,
                'modules' => !empty($this->modules),
            ],
            'dusk' => [
                'skip' => !$noPhp && empty($this->changed['resources']),
            ],
            'docs' => [
                'changed' => !empty($this->changed['docs']),
            ]
        ];
    }

    /**
     *  Check if the given options array contains any of the $opts specified
     *
     * @param string ...$opts options to check for
     * @return bool If one of the specified options is set
     */
    private function checkOpt(...$opts)
    {
        foreach ($opts as $option) {
            if (isset($this->options[$option])) {
                if ($this->options[$option] === false) {
                    // no data, return that option is enabled
                    return true;
                }
                return $this->options[$option];
            }
        }

        return false;
    }

    /**
     * Check for a PHP executable and return the path to it
     * If it does not exist, run composer.
     * If composer isn't installed, print error and exit.
     *
     * @param string $exec the name of the executable to check
     * @return string path to the executable
     */
    private function checkPhpExec($exec)
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

    /**
     * Check for a Python executable and return the path to it
     * If it does not exist, run pip3.
     * If pip3 isn't installed, print error and exit.
     *
     * @param string $exec the name of the executable to check
     * @return string path to the executable
     */
    private function checkPythonExec($exec)
    {
        $home = getenv('HOME');
        $path = "$home/.local/bin/$exec";

        if (is_executable($path)) {
            return $path;
        }

        // check system
        $system_path = rtrim(exec("which pylint 2>/dev/null"));
        if (is_executable($system_path)) {
            return $system_path;
        }

        echo "Running pip3 install to install developer dependencies.\n";
        passthru("pip3 install $exec"); // probably wrong in other cases...

        if (is_executable($path)) {
            return $path;
        }

        echo "\nRunning installing deps with pip3 failed.\n You should try running 'pip3 install -r requirements.txt' by hand\n";
        echo "You can find more info at http://docs.librenms.org/Developing/Validating-Code/\n";
        exit(1);
    }

    /**
     * Build a list of exclude arguments from an array
     *
     * @param string $exclude_string such as "--exclude"
     * @param array $excludes array of directories to exclude
     * @return string resulting string
     */
    private function buildPhpLintExcludes($exclude_string, $excludes)
    {
        $result = '';
        foreach ($excludes as $exclude) {
            $result .= $exclude_string . $exclude . ' ';
        }

        return $result;
    }
}
