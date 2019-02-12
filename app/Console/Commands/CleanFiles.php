<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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

        $this->setDescription(__('Clean modified and untracked files, helpful to remove files after testing PR or just clean up'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm(__('Are you sure you want to delete all modified and untracked files?'))) {

        }

        return 0;
    }
}
