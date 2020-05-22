<?php
/**
 * DevCheckCommand.php
 *
 * -Description-
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

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Illuminate\Support\Arr;
use LibreNMS\Util\CiHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevCheckCommand extends LnmsCommand
{
    protected $developer = true;
    protected $name = 'dev:check';

    /** @var CiHelper */
    protected $helper;

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('check', InputArgument::OPTIONAL, __('commands.dev:check.arguments.check', ['checks' => '[unit, lint, style, dusk]']), 'all');
        $this->addOption('os', 'o', InputArgument::IS_ARRAY | InputArgument::REQUIRED);
        $this->addOption('module', 'm', InputArgument::IS_ARRAY | InputArgument::REQUIRED);
        $this->addOption('fail-fast', 'f', InputOption::VALUE_NONE);
        $this->addOption('quiet', 'q', InputOption::VALUE_NONE);
        $this->addOption('db', null, InputOption::VALUE_NONE);
        $this->addOption('snmpsim', null, InputOption::VALUE_NONE);
        $this->addOption('full', null, InputOption::VALUE_NONE);
        $this->addOption('commands', 'c', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->helper = new CiHelper();
        $this->parseInput();

        $result = $this->helper->run();

        if (getenv('EXECUTE_BUILD_DOCS') && $this->helper->getFlags('docs_changed')) {
            exec('bash scripts/deploy-docs.sh');
        }

        if ($result == 0 && $this->helper->allChecksComplete()) {
            $this->line("\033[32mTests ok, submit away :)\033[0m");
        }

        return $result;
    }

    private function parseInput()
    {
        $this->helper->setFlags(Arr::only($this->options(), ['quiet', 'commands', 'fail-fast', 'full']));

        $check = $this->argument('check');
        $all = $check == 'all' || $check == 'ci';
        $this->helper->enable('style', $all || $check === 'style');
        $this->helper->enable('lint', $all || $check === 'lint');
        $this->helper->enable('unit', $all || $check === 'unit');
        $this->helper->enable('web', $all || $check === 'web');

        if ($os = $this->option('os')) {
            $this->helper->setFlags(['style_enable' => false, 'lint_enable' => false, 'unit_enable' => true, 'web_enable' => false]);
            $this->helper->setOS(explode(',', $os));
            CiHelper::enableSnmpsim();
            CiHelper::enableDb();
        }

        if ($modules = $this->option('module')) {
            $this->helper->setFlags(['style_enable' => false, 'lint_enable' => false, 'unit_enable' => true, 'web_enable' => false]);
            $this->helper->setModules($modules);
            CiHelper::enableSnmpsim();
            CiHelper::enableDb();
        }

        if ($check == 'ci') {
            $this->helper->setFlags(['ci' => true]);
            CiHelper::duskHeadless();
            CiHelper::enableSnmpsim();
            CiHelper::enableDb();
        }

        if ($this->option('snmpsim')) {
            CiHelper::enableSnmpsim();
        }

        if ($this->option('db')) {
            CiHelper::enableDb();
        }
    }
}
