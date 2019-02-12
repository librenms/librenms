<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LibreNMS\Util\Git;
use Symfony\Component\Console\Input\InputOption;

class CleanFiles extends Command
{
    protected $name = 'clean:files';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDescription(__('commands.clean:files.description'));
        $this->addOption('vendor', null, InputOption::VALUE_NONE, __('commands.clean:files.options.vendor'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm(__('commands.clean:files.confirm'))) {
            Git::clean($this->option('vendor'));

            $this->info(__('commands.clean:files.done'));
        }

        return 0;
    }
}
