<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Facades\LibrenmsConfig;
use Illuminate\Foundation\Console\ConfigClearCommand;
use Symfony\Component\Console\Input\ArrayInput;

class ClearConfigCommand extends LnmsCommand
{
    protected $name = 'config:clear';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(ConfigClearCommand $configClearCommand)
    {
        $configClearCommand->setLaravel($this->laravel);
        $configClearCommand->run(new ArrayInput([]), $this->output);

        LibrenmsConfig::invalidateCache();
    }
}
