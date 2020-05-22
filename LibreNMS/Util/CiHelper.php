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

use Symfony\Component\Process\Process;

class CiHelper
{
    private $changedFiles;
    private $changed;
    private $modules;
    private $os;

    private $completedChecks = [
        'lint' => false,
        'style' => false,
        'unit' => false,
        'web' => false,
    ];
    private $ciDefaults = [
        'quiet' => [
            'lint' => true,
            'style' => true,
            'unit' => false,
            'web' => false,
        ],
    ];
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
        'web_enable' => true,
        'web_skip' => false,
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
        $this->flags["{$check}_enable"] = $enabled;
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

            if ($this->flags['fail-fast'] && $ret !== 0 && $ret !== 250) {
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
     * @param string $item
     * @return bool|bool[]
     */
    public function getFlags($item = null)
    {
        return isset($this->flags[$item]) ? $this->flags[$item] : $this->flags;
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

        if ($this->flags['fail-fast']) {
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

        $files = ($this->flags['full']) ? './' : implode(' ', $this->changed['php']);

        $cs_cmd = "$phpcs_bin -n -p --colors --extensions=php --standard=misc/phpcs_librenms.xml $files";

        return $this->execute('style', $cs_cmd);
    }

    public function checkWeb()
    {
        $this->execute('config:clear', ['php', 'artisan', 'config:clear']);
        $this->execute('dusk:update', ['php', 'artisan', 'dusk:update', ' --detect']);
//        $config_clear = new Process(['php', 'artisan', 'config:clear']);
//        $config_clear->run();
//        if ($config_clear->getExitCode() !== 0) {
//            echo $config_clear->getOutput() . PHP_EOL;
//            echo $config_clear->getErrorOutput() . PHP_EOL;
//        }
//        ()->setTty(Process::isTtySupported())->run(); // make sure config is not cached
//        (new Process(['php', 'artisan', 'dusk:update', ' --detect']))->setTty(Process::isTtySupported())->run(); // make sure driver is correct

        putenv('APP_ENV=testing');

        // check if web server is running
        $server = new Process(['php', '-S', '127.0.0.1:8000', base_path('server.php')], public_path(), ['APP_ENV' => 'dusk.testing']);
        $server->setTimeout(3600)
            ->setIdleTimeout(3600)
            ->setTty(Process::isTtySupported())
            ->start();
        $server->waitUntil(function ($type, $output) {
            return strpos($output, 'Development Server (http://127.0.0.1:8000) started') !== false;
        });
        if ($server->isRunning()) {
            echo "Started server http://127.0.0.1:8000\n";
        }

        $dusk_cmd = "php artisan dusk";

        if ($this->flags['fail-fast']) {
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

            $files = $this->flags['full'] ? './' : implode(' ', $this->changed['php']);

            $php_lint_cmd = "$parallel_lint_bin $lint_exclude $files";

            $return += $this->execute('PHP lint', $php_lint_cmd);
        }

        if (!$this->flags['lint_skip_python']) {
            $pylint_bin = $this->checkPythonExec('pylint');

            $files = $this->flags['full']
                ? str_replace(PHP_EOL, ' ', rtrim(shell_exec("find . -name '*.py' -not -path './vendor/*' -not -path './tests/*'")))
                : implode(' ', $this->changed['python']);

            $py_lint_cmd = "$pylint_bin -E -j 0 $files";
            $return += $this->execute('Python lint', $py_lint_cmd);
        }

        if (!$this->flags['lint_skip_bash']) {
            $files = $this->flags['full']
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
     * @param string|array $command
     * @return int
     */
    private function execute(string $name, $command): int
    {
        $start = microtime(true);
        $proc = new Process($command);

        if ($this->flags['commands']) {
            echo $proc->getCommandLine() . PHP_EOL;
            return 250;
        }

        echo "Running $name check... ";
        $space = strrpos($name, ' ');
        $type = substr($name, $space ? $space + 1 : 0);

        $quiet = ($this->flags['ci'] && isset($this->ciDefaults['quiet'][$type])) ? $this->ciDefaults['quiet'][$type] : $this->flags['quiet'];
        if (!$quiet) {
            echo PHP_EOL;
            $proc->setTty(Process::isTtySupported());
        }

        $proc->run();

        $duration = sprintf('%.2fs', microtime(true) - $start);
        if ($proc->getExitCode() > 0) {
            echo "failed ($duration)\n";
            if ($quiet) {
                echo $proc->getOutput() . PHP_EOL;
                echo $proc->getErrorOutput() . PHP_EOL;
            }
        } else {
            echo "success ($duration)\n";
        }

        return $proc->getExitCode();
    }


    private function checkEnv()
    {
        $this->flags['unit_skip'] = (bool)getenv('SKIP_UNIT_CHECK');
        $this->flags['lint_skip'] = (bool)getenv('SKIP_LINT_CHECK');
        $this->flags['web_skip'] = (bool)getenv('SKIP_WEB_CHECK');
        $this->flags['style_skip'] = (bool)getenv('SKIP_STYLE_CHECK');
    }

    private function detectChangedFiles()
    {
        $changed_files = getenv('FILES')
            ? rtrim(getenv('FILES'))
            : exec("git diff --diff-filter=d --name-only master | tr '\n' ' '|sed 's/,*$//g'");
        $categorizor = new FileCategorizer($this->changedFiles = $changed_files ? explode(' ', $changed_files) : []);
        $this->changed = $categorizor->categorize();
    }

    private function parseChangedFiles()
    {
        if (empty($this->changedFiles) || $this->flags['full']) {
            // nothing to do
            return;
        }

        $hasOs = !empty($this->changed['os']);
        $onlyOs = empty(array_diff($this->changed['php'], $this->changed['os-files']));
        $php = $hasOs && empty($this->changed['php']);

        $this->setFlags([
            'style_enable' => !empty($this->changed['php']),
            'lint_enable' => !empty($this->changed['php']) || !empty($this->changed['python']) || !empty($this->changed['bash']),
            'lint_skip_php' => empty($this->changed['php']),
            'lint_skip_python' => empty($this->changed['python']),
            'lint_skip_bash' => empty($this->changed['bash']),
            'unit_enable' => $hasOs || $php || !empty($this->changed['docs']) || !empty($this->changed['svg']),
            'unit_os' => !empty($this->os) || ($hasOs && $onlyOs),
            'unit_docs' => !empty($this->changed['docs']) && !$php,
            'unit_svg' => !empty($this->changed['svg']) && !$php,
            'unit_modules' => !empty($this->modules),
            'web_enable' => $php || !empty($this->changed['resources']),
            'docs_changed' => !empty($this->changed['docs']),
            'full' => !empty($this->changed['full-checks']),
        ]);
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
