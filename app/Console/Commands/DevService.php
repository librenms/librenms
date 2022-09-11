<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use Illuminate\Support\Str;
use LibreNMS\Services\CheckParameter;
use LibreNMS\Util\Clean;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DevService extends LnmsCommand
{
    protected $developer = true;
    protected $name = 'dev:service';

    public function __construct()
    {
        parent::__construct();
        $this->addArgument('check', InputArgument::OPTIONAL, __('commands.dev:check.arguments.check', ['checks' => '[unit, lint, style, dusk]']), 'all');
        $this->addOption('test-lines', 't', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $check = Clean::fileName($this->argument('check'));
        $parser = new \LibreNMS\Services\HelpParser();
        $help = $parser->fetchHelp($check);
        $output = $parser->parse($check);

        // print lines to add to test file
        if ($this->option('test-lines')) {
            $this->line("            [\n                '$check',\n                <<<'EOF'");
            $this->line($help);
            $this->line("EOF,\n                [");
            $output->each([$this, 'printTestCode']);
            $this->line("                ],\n            ]");

            return 0;
        }

        // print out help text and parsed parameters
        $this->line($help);
        $this->line(str_repeat('─', 80));
        $this->newLine();
        $output->each(function (\LibreNMS\Services\CheckParameter $param) {
            printf(
                "\e[36m%s \e[92m%s\e[0m%s\e[0m: %s\n\t%s %s %s \e[0mI:%s \e[0mX:%s\e[0m\n",
                $param->short,
                $param->param,
                $param->value ? "=\e[95m$param->value" : '',
                $param->description,
                $param->required ? "\e[32mR+" : "\e[31mR-",
                $param->default ? "\e[32mD+" : "\e[31mD-",
                $param->uses_target ? "\e[32mT+" : "\e[31mT-",
                "\e[35m" . implode(',', $param->inclusive_group ?? []),
                "\e[34m" . implode(',', $param->exclusive_group ?? [])
            );
        });

        return 0;
    }

    public function printTestCode(CheckParameter $param): void
    {
        $long  = $this->quoteText($param->param);
        $short = $this->quoteText($param->short);
        $value = $this->quoteText($param->value);
        $descr = $this->quoteText($param->description);
        $code =  "new CheckParameter($long, $short, $value, $descr)";
        if ($param->required || $param->default || $param->inclusive_group || $param->exclusive_group) {
            $code = "($code)";
        }
        if($param->required) {
            $code .= '->setRequired()';
        }
        if ($param->default) {
            $code .= '->setHasDefault()';
        }
        if ($param->uses_target) {
            $code .= '->usesTarget()';
        }
        if ($param->inclusive_group) {
            $code .= '->setInclusiveGroup(' . str_replace('"', '\'', json_encode($param->inclusive_group)) . ')';
        }
        if ($param->exclusive_group) {
            $code .= '->setExclusiveGroup(' . str_replace('"', '\'', json_encode($param->exclusive_group)) . ')';
        }

        $this->line('                    ' . $code . ',');
    }


    private function quoteText(string $text): string
    {
        if (Str::contains($text, ["\n"])) {
            $text = addcslashes($text, '"\\');
            $text = str_replace("\n", '\n', $text);

            return '"' . $text . '"';
        }

        $text = addcslashes($text, "'\\");
        return "'" . $text . "'";
    }
}
