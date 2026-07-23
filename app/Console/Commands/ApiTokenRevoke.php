<?php

namespace App\Console\Commands;

use App\Console\LnmsCommand;
use App\Models\User;
use Symfony\Component\Console\Input\InputArgument;

class ApiTokenRevoke extends LnmsCommand
{
    protected $name = 'api:token:revoke';

    public function __construct()
    {
        parent::__construct();

        $this->addArgument('username', InputArgument::REQUIRED);
        $this->addArgument('token-id', InputArgument::REQUIRED);
    }

    public function handle(): int
    {
        $username = $this->argument('username');
        $user = User::where('username', $username)->first();

        if (! $user) {
            $this->error(trans('commands.api:token:revoke.user-not-found', ['username' => $username]));

            return 1;
        }

        $tokenId = $this->argument('token-id');
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (! $token) {
            $this->error(trans('commands.api:token:revoke.token-not-found', ['id' => $tokenId, 'username' => $user->username]));

            return 1;
        }

        $token->delete();
        $this->info(trans('commands.api:token:revoke.revoked', ['name' => $token->name, 'id' => $tokenId]));

        return 0;
    }
}
