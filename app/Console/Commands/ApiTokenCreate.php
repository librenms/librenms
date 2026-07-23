<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ApiTokenCreate extends LnmsCommand
{
    protected $name = 'api:token:create';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('username', InputArgument::REQUIRED);
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, '', 'api-token');
    }

    public function handle(): int
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error(trans('commands.api:token:create.user-not-found', ['username' => $username]));

            return 1;
        }

        $token = $user->createToken($this->option('name'));

        $this->info(trans('commands.api:token:create.created'));
        $this->newLine();
        $this->line($token->plainTextToken);
        $this->newLine();
        $this->warn(trans('commands.api:token:create.save-warning'));

        return 0;
    }
}
