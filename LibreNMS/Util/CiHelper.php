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
    private $completedChecks = [
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
        'style_enable' => true,
        'style_skip' => false,
        'lint_enable' => true,
        'lint_skip' => false,
        'lint_skip_php' => false,
        'lint_skip_python' => false,
        'lint_skip_bash' => false,
        'unit_enable' => true,
        'unit_skip' => false,
        'unit_os' => false,
        'unit_docs' => false,
        'unit_svg' => false,
        'unit_modules' => false,
        'dusk_enable' => true,
        'dusk_skip' => false,
        'docs_changed' => false,
        'quiet' => false,
        'commands' => false,
        'fail-fast' => false,
        'full' => false,
        'ci' => false,
    ];

    public function __construct(array $flags = [])
    {
        $this->setFlags($flags);
        $this->detectChangedFiles();
        $this->parseChangedFiles();
        $this->checkEnv();
    }

    public function enable($check, $enabled = true)
    {
        $this->flags[$check]['enabled'] = $enabled;
    }

    public static function duskHeadless()
    {
        putenv('CHROME_HEADLESS=1');
    }

    public static function enableDb()
    {
        putenv('DBTEST=1');
    }

    public static function enableSnmpsim()
    {
        putenv('SNMPSIM=1');
    }

    public function setModules(array $modules)
    {
        putenv("TEST_MODULES=" . implode(',', $modules));
        $this->modules = $modules;
    }

    public function setOS(array $os)
    {
        $this->os = $os;
    }

    public function setFlags(array $flags)
    {
        $this->flags = array_intersect_key($flags, $this->flags) + $this->flags;
    }

    public function run()
    {
        $return = 0;
        foreach (array_keys($this->completedChecks) as $check) {
            $ret = $this->runCheck($check);

            if ($this->failFast && $ret !== 0 && $ret !== 250) {
                return $return;
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
        return array_reduce($this->completedChecks, function ($result, $check) {
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
        if ($this->flags['unit_os']) {
            echo 'Only checking os: ' . implode(', ', $this->changed['os']) . PHP_EOL;
            $filter = implode('.*|', $this->os ?: $this->changed['os']);
            // include tests that don't have data providers and only data sets that match
            $phpunit_cmd .= " --group os --filter '/::test[A-Za-z]+$|::test[A-Za-z]+ with data set \"$filter.*\"$/'";
        } elseif ($this->flags['unit_docs']) {
            $phpunit_cmd .= " --group docs";
        } elseif ($this->flags['unit_svg']) {
            $phpunit_cmd .= ' tests/SVGTest.php';
        } elseif ($this->flags['unit_modules']) {
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

    public function checkWeb()
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

        return $this->execute('web', $dusk_cmd);
    }

    /**
     * Runs php -l and tests for any syntax errors
     *
     * @return int the return value from running php -l (0 = success)
     */
    public function checkLint()
    {
        $return = 0;
        if (!$this->flags['lint_skip_php']) {
            $parallel_lint_bin = $this->checkPhpExec('parallel-lint');

            // matches a substring of the relative path, leading / is treated as absolute path
            $lint_excludes = ['vendor/'];
            $lint_exclude = $this->buildPhpLintExcludes('--exclude ', $lint_excludes);

            $files = $this->fullChecks ? './' : implode(' ', $this->changed['php']);

            $php_lint_cmd = "$parallel_lint_bin $lint_exclude $files";

            $return += $this->execute('PHP lint', $php_lint_cmd);
        }

        if (!$this->flags['lint_skip_python']) {
            $pylint_bin = $this->checkPythonExec('pylint');

            $files = $this->fullChecks
                ? str_replace(PHP_EOL, ' ', rtrim(shell_exec("find . -name '*.py' -not -path './vendor/*' -not -path './tests/*'")))
                : implode(' ', $this->changed['python']);

            $py_lint_cmd = "$pylint_bin -E -j 0 $files";
            $return += $this->execute('Python lint', $py_lint_cmd);
        }

        if (!$this->flags['lint_skip_bash']) {
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
            $this->completedChecks[$type] = true;
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
        if ($this->flags["{$type}_skip"] || $this->completedChecks[$type]) {
            return false;
        }

        $method = 'check' . ucfirst($type);
        if (method_exists($this, $method) && $this->flags["{$type}_enable"]) {
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

    private function checkEnv()
    {
        $this->flags['unit_skip'] = (bool)getenv('SKIP_UNIT_CHECK');
        $this->flags['lint_skip'] = (bool)getenv('SKIP_LINT_CHECK');
        $this->flags['dusk_skip'] = (bool)getenv('SKIP_WEB_CHECK');
        $this->flags['style_skip'] = (bool)getenv('SKIP_STYLE_CHECK');
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

        $this->setFlags([
            'style_skip' => empty($this->changed['php']),
            'lint_skip' => empty($this->changed['php']) && empty($this->changed['python']) && empty($this->changed['bash']),
            'lint_skip_php' => empty($this->changed['php']),
            'lint_skip_python' => empty($this->changed['python']),
            'lint_skip_bash' => empty($this->changed['bash']),
            'unit_skip' => !$hasOs && $noPhp && empty($this->changed['docs']) && empty($this->changed['svg']),
            'unit_os' => !empty($this->os) || ($hasOs && $onlyOs),
            'unit_docs' => !empty($this->changed['docs']) && $noPhp,
            'unit_svg' => !empty($this->changed['svg']) && $noPhp,
            'unit_modules' => !empty($this->modules),
            'dusk_skip' => !$noPhp && empty($this->changed['resources']),
            'docs_changed' => !empty($this->changed['docs']),
        ]);
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
