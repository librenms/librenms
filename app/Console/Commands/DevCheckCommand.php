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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
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
        $this->addOption('os', 'o', InputOption::VALUE_REQUIRED);
        $this->addOption('module', 'm', InputOption::VALUE_REQUIRED);
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
        $this->helper->detectChangedFiles();
        $this->helper->checkEnvSkips();

        $result = $this->helper->run();

        if ($result == 0 && $this->helper->allChecksComplete()) {
            $this->line("\033[32mTests ok, submit away :)\033[0m");
        }

        return $result;
    }

    private function parseInput()
    {
        $check = $this->argument('check');
        if (! in_array($check, ['all', 'lint', 'style', 'unit', 'web', 'ci'])) {
            $this->error("Invalid check: $check");
            exit(1);
        }

        $this->helper->setFlags(Arr::only($this->options(), ['quiet', 'commands', 'fail-fast', 'full']));

        $this->helper->enable('style', $check == 'all' || $check === 'style');
        $this->helper->enable('lint', $check == 'all' || $check == 'ci' || $check === 'lint');
        $this->helper->enable('unit', $check == 'all' || $check == 'ci' || $check === 'unit');
        $this->helper->enable('web', $check == 'ci' || $check === 'web');

        if ($os = $this->option('os')) {
            $this->helper->setFlags(['style_enable' => false, 'lint_enable' => false, 'unit_enable' => true, 'web_enable' => false]);
            $this->helper->setOS(explode(',', $os));
        }

        if ($modules = $this->option('module')) {
            $this->helper->setFlags(['style_enable' => false, 'lint_enable' => false, 'unit_enable' => true, 'web_enable' => false]);
            $this->helper->setModules(explode(',', $modules));
        }

        if ($check == 'ci') {
            $this->helper->setFlags(['ci' => true, 'fail-fast' => true]);
            $this->helper->duskHeadless();
            $this->helper->enableSnmpsim();
            $this->helper->enableDb();
        }

        if ($this->option('snmpsim')) {
            $this->helper->enableSnmpsim();
        }

        if ($this->option('db')) {
            $this->helper->enableDb();
        }
    }
}
