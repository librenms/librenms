<?php

namespace App\Console\Commands;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Console\DumpCommand;
use LibreNMS\DB\Schema;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Yaml\Yaml;

class SchemaDumpCommand extends DumpCommand
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->signature .= '{--snapshots : Dump snapshots to reduce initial migration time}';
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ConnectionResolverInterface $connections, Dispatcher $dispatcher): int
    {
        $database = $this->option('database');

        if ($this->option('snapshots')) {
            $databases = $database ? [$database] : ['mysql', 'testing'];
            foreach ($databases as $database) {
                $this->line("Database: $database");
                $this->input->setOption('database', $database);
                $this->input->setOption('verbose', 3);
                parent::handle($connections, $dispatcher);
            }

            return 0;
        }

        $stdout = new StreamOutput(fopen('php://stdout', 'w'));
        $parameters = ['--force' => true, '--ansi' => true];
        if ($database) {
            $parameters['--database'] = $database;
        }

        \Artisan::call('migrate', $parameters, $stdout);

        $file = $this->option('path') ?: resource_path('definitions/schema/db_schema.yaml');
        $yaml = Yaml::dump(Schema::dump($database), 3, 2);

        if (file_put_contents($file, $yaml)) {
            $this->info(basename($file) . ' updated!');

            return 0;
        }

        $this->error('Failed to write file ' . $file);

        return 1;
    }
}
