<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Services\Api\OpenApi\OpenApiGenerator;
use GoldSpecDigital\ObjectOrientedOAS\Exceptions\ValidationException;
use Symfony\Component\Console\Input\InputOption;

class ApiOpenApiCommand extends LnmsCommand
{
    protected $name = 'api:openapi';
    protected $description = 'Generate the OpenAPI 3 specification for the v1 API';

    public function __construct()
    {
        parent::__construct();

        $this->addOption('output', null, InputOption::VALUE_REQUIRED, 'Write the spec to PATH instead of stdout');
        $this->addOption('validate', null, InputOption::VALUE_NONE, 'Validate the spec and exit non-zero on errors');
        $this->addOption('no-pretty', null, InputOption::VALUE_NONE, 'Emit compact JSON');
    }

    public function handle(OpenApiGenerator $generator): int
    {
        $spec = $generator->generate();

        if ($this->option('validate')) {
            try {
                $spec->validate();
            } catch (ValidationException $e) {
                $this->error($e->getMessage());

                return 1;
            }
        }

        $flags = $this->option('no-pretty') ? 0 : JSON_PRETTY_PRINT;
        $json = $spec->toJson($flags);

        $output = $this->option('output');
        if ($output !== null) {
            file_put_contents($output, $json);
            $this->info("Wrote {$output}");

            return 0;
        }

        $this->line($json);

        return 0;
    }
}
